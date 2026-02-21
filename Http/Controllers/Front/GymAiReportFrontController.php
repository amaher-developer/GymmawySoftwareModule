<?php

namespace Modules\Software\Http\Controllers\Front;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Software\Classes\GymAiReport;
use Modules\Software\Models\GymAiReport as GymAiReportModel;

class GymAiReportFrontController extends GymGenericFrontController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * List all saved AI reports (paginated).
     */
    public function index()
    {
        $title   = trans('sw.ai_reports');
        $records = GymAiReportModel::orderByDesc('id')->paginate(15, [
            'id', 'type', 'method', 'model_used', 'lang',
            'from_date', 'to_date',
            'email_sent', 'email_sent_to', 'email_sent_at',
            'sms_sent', 'sms_sent_to', 'sms_sent_at',
            'created_at',
        ]);

        $from = Carbon::now()->startOfMonth()->format('Y-m-d');
        $to   = Carbon::now()->format('Y-m-d');

        return view('software::Front.ai_reports_list', compact('title', 'records', 'from', 'to'));
    }

    /**
     * Generate a new AI executive report via ChatGPT, save to DB, redirect to show.
     */
    public function generate(Request $request)
    {
        $from = $request->input('from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to',   Carbon::now()->format('Y-m-d'));

        try {
            $result = (new GymAiReport())->getter($from, $to);
            return redirect()->route('sw.aiReports.show', $result['id'])
                ->with('success', trans('sw.ai_report_generated'));
        } catch (\Exception $e) {
            return redirect()->route('sw.aiReports.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show a single saved AI report with delivery options.
     */
    public function show($id)
    {
        $record = GymAiReportModel::findOrFail($id);
        $title  = trans('sw.ai_report') . ' #' . $id;

        // Load saved notify list from settings
        $aiSettings    = $this->mainSettings->integrations['ai'] ?? [];
        $notifyEmails  = $aiSettings['notify_emails'] ?? [];
        $notifyPhones  = $aiSettings['notify_phones'] ?? [];

        $lang = $this->lang;

        return view('software::Front.ai_report_show', compact(
            'title', 'record', 'notifyEmails', 'notifyPhones', 'lang'
        ));
    }

    /**
     * Send a saved AI report to the specified emails and phones.
     * Uses the saved notify lists from settings; can be overridden per-request.
     */
    public function send(Request $request, $id)
    {
        $record = GymAiReportModel::findOrFail($id);

        // Collect recipients: from settings (always) + any extra added on the send form
        $aiSettings   = $this->mainSettings->integrations['ai'] ?? [];
        $emails = array_filter(array_unique(array_merge(
            $aiSettings['notify_emails'] ?? [],
            array_filter((array) $request->input('extra_emails', []))
        )));
        $phones = array_filter(array_unique(array_merge(
            $aiSettings['notify_phones'] ?? [],
            array_filter((array) $request->input('extra_phones', []))
        )));

        if (empty($emails) && empty($phones)) {
            return redirect()->route('sw.aiReports.show', $id)
                ->with('error', trans('sw.ai_report_no_recipients'));
        }

        try {
            $gymAi   = new GymAiReport();
            $results = $gymAi->setterMulti($id, $emails, $phones);

            $emailOk = !empty(array_filter($results['emails'] ?? []));
            $smsOk   = !empty(array_filter($results['sms'] ?? []));
            $success = $emailOk || $smsOk;

            return redirect()->route('sw.aiReports.show', $id)
                ->with($success ? 'success' : 'error',
                    $success ? trans('sw.ai_report_sent') : trans('sw.ai_report_send_failed'));
        } catch (\Exception $e) {
            return redirect()->route('sw.aiReports.show', $id)
                ->with('error', $e->getMessage());
        }
    }
}
