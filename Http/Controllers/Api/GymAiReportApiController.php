<?php

namespace Modules\Software\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Software\Classes\GymAiReport;
use Modules\Software\Models\GymAiReport as GymAiReportModel;

/**
 * GymAiReportApiController
 *
 * Handles the Executive Performance AI report.
 * Lives under:  /api/ai-reports/executive/
 *
 * POST /api/ai-reports/executive/getter
 *   Collects live gym KPI data → ChatGPT → saves to sw_ai_reports → returns { id, report }
 *
 * POST /api/ai-reports/executive/setter
 *   Loads saved report by ID → sends email / SMS → updates delivery status in DB
 *
 * GET  /api/ai-reports/executive/history
 *   Returns paginated list of past reports from sw_ai_reports
 *
 * Future report controllers follow the same pattern:
 *   /api/ai-reports/sales/...
 *   /api/ai-reports/members/...
 *   /api/ai-reports/attendance/...
 */
class GymAiReportApiController extends Controller
{
    // =========================================================================
    //  GETTER — Gym data → ChatGPT → Save to DB → Return ID + report
    // =========================================================================

    /**
     * POST /api/ai-reports/executive/getter
     *
     * Body (all optional):
     *   from   Y-m-d   default: first day of current month
     *   to     Y-m-d   default: today
     *
     * Response:
     *   { success, id, report, generated_at }
     */
    public function getter(Request $request): JsonResponse
    {
        $from = $request->input('from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to',   Carbon::now()->format('Y-m-d'));

        try {
            $result = (new GymAiReport())->getter($from, $to);

            return response()->json([
                'success'      => true,
                'id'           => $result['id'],
                'report'       => $result['report'],
                'generated_at' => $result['generated_at'],
            ]);

        } catch (\Exception $e) {
            Log::error('[AiReport Getter] ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // =========================================================================
    //  SETTER — Load from DB by ID → Email + SMS → Update delivery status
    // =========================================================================

    /**
     * POST /api/ai-reports/executive/setter
     *
     * Body:
     *   report_id   int     (required)  ID returned by getter
     *   email       string  (optional)  Recipient email
     *   phone       string  (optional)  Recipient phone
     *
     * Response:
     *   { success, results: { email: bool, sms: bool }, message }
     */
    public function setter(Request $request): JsonResponse
    {
        $request->validate([
            'report_id' => 'required|integer|exists:sw_ai_reports,id',
        ]);

        $reportId = (int) $request->input('report_id');
        $email    = $request->input('email');
        $phone    = $request->input('phone');

        if (!$email && !$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Provide at least one of: email, phone.',
            ], 422);
        }

        try {
            $results = (new GymAiReport())->setter($reportId, $email, $phone);
            $success = !empty(array_filter($results));

            return response()->json([
                'success'   => $success,
                'report_id' => $reportId,
                'results'   => $results,
                'message'   => $success
                    ? 'Report delivered successfully.'
                    : 'Delivery failed. Check your email/SMS gateway settings.',
            ]);

        } catch (\Exception $e) {
            Log::error('[AiReport Setter] ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // =========================================================================
    //  HISTORY — Paginated list of past reports
    // =========================================================================

    /**
     * GET /api/ai-reports/executive/history
     *
     * Query params:
     *   per_page   int   default: 15
     *
     * Response:
     *   { success, data: [ { id, type, lang, from_date, to_date, email_sent, sms_sent, created_at } ] }
     */
    public function history(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $records = GymAiReportModel::where('type', 'executive')
            ->orderByDesc('id')
            ->paginate($perPage, [
                'id', 'type', 'method', 'model_used', 'lang',
                'from_date', 'to_date',
                'email_sent', 'email_sent_to', 'email_sent_at',
                'sms_sent', 'sms_sent_to', 'sms_sent_at',
                'created_at',
            ]);

        return response()->json(['success' => true, 'data' => $records]);
    }
}
