<?php

namespace Modules\Software\Http\Controllers\Front;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Modules\Generic\Http\Controllers\Front\PaymobFrontController;
use Modules\Generic\Http\Controllers\Front\PayTabsFrontController;
use Modules\Generic\Http\Controllers\Front\TabbyFrontController;
use Modules\Generic\Http\Controllers\Front\TamaraFrontController;
use Modules\Billing\Services\SwBillingService;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymOnlinePaymentInvoice;
use Modules\Software\Models\GymStoreOrder;
use Modules\Software\Models\GymStoreOrderProduct;
use Modules\Software\Models\GymStoreProduct;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymTrainingMember;
use Modules\Software\Models\GymTrainingMemberLog;
use Modules\Software\Models\GymTrainingAssessment;
use Modules\Software\Models\GymTrainingMedicine;
use Modules\Software\Models\GymTrainingFile;
use Modules\Software\Models\GymTrainingTrack;
use Modules\Software\Models\GymAiRecommendation;
use Modules\Software\Models\GymTrainingPlan;
use Modules\Software\Models\GymUser;
use Modules\Software\Models\GymUserLog;
use Modules\Software\Services\NotificationService;

/**
 * GymMobileSubscriptionFrontController
 *
 * Handles the mobile-app webview payment flow for gym subscriptions.
 *
 * Flow:
 *  1. Mobile app opens: GET /subscription-mobile/{id}?token=PUSH_TOKEN
 *  2. Member fills the form and selects a payment gateway (Tabby / Tamara / PayTabs).
 *  3. Form POSTs to: POST /invoice-mobile/submit
 *  4. Controller creates a pending GymOnlinePaymentInvoice and redirects to the gateway.
 *  5. Gateway redirects back to one of:
 *       GET /mobile-payment/tabby/verify
 *       GET /mobile-payment/tamara/verify
 *       GET /mobile-payment/paytabs/verify
 *  6. Controller verifies / captures the payment, creates GymMemberSubscription + GymMoneyBox,
 *     then redirects to: GET /invoice-mobile/{member_subscription_id}
 *
 * Member identification:
 *  The mobile app passes a push-notification token as ?token= query param.
 *  We look it up in sw_gym_push_tokens to find the member.
 */
class GymMobileSubscriptionFrontController extends GymGenericFrontController
{
    /** @var GymMember|null Member identified from push token (or null for guests) */
    protected $currentMember = null;

    public function __construct()
    {
        parent::__construct();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 1 — Show subscription form
    // ─────────────────────────────────────────────────────────────────────────

    public function showMobile($id)
    {
        $request = request();
        $memberId = (int) ($request->input('member_id') ?: 0);
        $this->currentMember = $currentUser = $this->resolveMemberFromRequest($request, false);

        Log::info('subscription-mobile member resolution', [
            'subscription_id' => (int) $id,
            'query_member_id' => $memberId,
            'resolved_member_id' => (int) ($currentUser->id ?? 0),
            'has_token' => trim((string) ($request->input('payment_link_token') ?: $request->input('token') ?: $request->bearerToken() ?: '')) !== '',
        ]);

        if ($memberId > 0 && !$currentUser) {
            return redirect()->route('sw.mobile-payment.error');
        }

        View::share('currentUser', $currentUser);
        $record = GymSubscription::where('id', $id)->first();

        if (!$record) {
            return abort(404);
        }

        $title = $record->name;
        $mainSettings = $this->mainSettings;

        return view('software::Front.subscription_mobile', compact('title', 'record', 'mainSettings'));
    }

    public function showActivityMobile($id)
    {
        $request = request();
        $memberId = (int) ($request->input('member_id') ?: 0);
        $this->currentMember = $currentUser = $this->resolveMemberFromRequest($request, false);

        // member_id is optional, but when provided it must resolve to a member.
        if ($memberId > 0 && !$currentUser) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $record = GymActivity::find((int) $id);
        if (!$record) {
            return abort(404);
        }

        View::share('currentUser', $currentUser);
        $title = $record->name;
        $mainSettings = $this->mainSettings;
        $activities = GymActivity::orderBy('name_ar')->get();

        return view('software::Front.activity_mobile', compact('title', 'record', 'mainSettings', 'activities', 'currentUser'));
    }

    public function showStoreMobile($id)
    {
        $request = request();
        $memberId = (int) ($request->input('member_id') ?: 0);
        $this->currentMember = $currentUser = $this->resolveMemberFromRequest($request, false);

        // member_id is optional, but when provided it must resolve to a member.
        if ($memberId > 0 && !$currentUser) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $record = GymStoreProduct::find((int) $id);
        if (!$record) {
            return abort(404);
        }

        View::share('currentUser', $currentUser);
        $title = $record->name;
        $mainSettings = $this->mainSettings;
        $products = GymStoreProduct::orderBy('name_ar')->get();

        return view('software::Front.store_mobile', compact('title', 'record', 'mainSettings', 'products', 'currentUser'));
    }

    public function showTrainingPlanMobile(Request $request, $id)
    {
        $lang = (string) ($request->input('lang') ?: app()->getLocale());
        if (in_array($lang, ['ar', 'en'], true)) {
            app()->setLocale($lang);
            Carbon::setLocale($lang);
        }

        $this->currentMember = $currentUser = $this->resolveMemberFromRequest($request);
        if (!$currentUser) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $assignment = GymTrainingMember::with([
            'member',
            'training_plan.tasks' => function ($q) {
                $q->orderBy('order', 'asc')->orderBy('id', 'asc');
            },
            'diet_plan.tasks' => function ($q) {
                $q->orderBy('order', 'asc')->orderBy('id', 'asc');
            },
        ])->find((int) $id);

        if (!$assignment || (int) ($assignment->member_id ?? 0) !== (int) $currentUser->id) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $plan = $this->resolveAssignedTrainingPlan($assignment);
        $detailsHtml = (string) (
            $assignment->training_plan_details
            ?? $assignment->diet_plan_details
            ?? $assignment->plan_details
            ?? $plan->content
            ?? ''
        );

        $tasks = collect($plan?->tasks ?? [])->map(function ($task) use ($lang) {
            return $this->mapMobilePlanTask($task, $lang);
        })->filter(function (array $task) {
            return $task['title'] !== '' || $task['notes'] !== '';
        })->values();

        $title = (string) ($assignment->title ?? $plan->title ?? trans('sw.training_plan'));
        $mainSettings = $this->mainSettings;

        return view('software::Front.training_plan_mobile', [
            'title' => $title,
            'plan' => $plan,
            'assignment' => $assignment,
            'member' => $currentUser,
            'tasks' => $tasks,
            'mainSettings' => $mainSettings,
            'planDetailsHtml' => $detailsHtml,
            'isDietPlan' => (int) ($assignment->type ?? $plan->type ?? 0) === TypeConstants::DIET_PLAN_TYPE,
        ]);
    }

    public function showTrainingMemberLogMobile(Request $request)
    {
        $lang = $this->resolveMobileLanguage($request);
        $this->currentMember = $currentUser = $this->resolveMemberFromRequest($request);

        if (!$currentUser) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $memberId = (int) $currentUser->id;

        $memberModel = GymMember::find($memberId);
        if (!$memberModel) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $selectedType = trim((string) $request->input('type', 'all'));
        $allowedTypes = ['all', 'assessment', 'plan', 'medicine', 'note', 'file', 'track', 'ai', 'ai_plan'];
        if (!in_array($selectedType, $allowedTypes, true)) {
            $selectedType = 'all';
        }

        $logsQuery = GymTrainingMemberLog::query()
            ->where('member_id', $memberId)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($selectedType !== 'all') {
            $logsQuery->where('training_type', $selectedType);
        }

        $logs = $logsQuery->paginate(15)->appends($request->except('page'));

        $cards = $logs->getCollection()->map(function (GymTrainingMemberLog $log) use ($memberId, $request, $lang) {
            $details = $this->resolveMobileTrainingLogDetails($log, $lang);
            $summary = $this->buildMobileLogSummary($log, $details, $lang);

            return [
                'id' => (int) $log->id,
                'type' => (string) ($log->training_type ?? 'activity'),
                'type_label' => $this->mobileTrainingTypeLabel((string) ($log->training_type ?? 'activity'), $lang),
                'action_label' => $this->mobileActionLabel((string) ($log->action ?? 'updated'), $lang),
                'summary' => $summary,
                'date' => optional($log->created_at)->translatedFormat('d F Y') ?: '',
                'time' => optional($log->created_at)->format('H:i') ?: '',
                'details_url' => $this->buildMobileRouteWithToken('sw.training-member-log-mobile.detail', [
                    'log' => (int) $log->id,
                ], $request, $lang),
            ];
        });

        $logs->setCollection($cards);

        $countByType = GymTrainingMemberLog::query()
            ->where('member_id', $memberId)
            ->select('training_type', DB::raw('count(*) as total'))
            ->groupBy('training_type')
            ->pluck('total', 'training_type');

        $filterTypes = ['all', 'assessment', 'plan', 'medicine', 'note', 'file', 'track', 'ai', 'ai_plan'];
        $filters = collect($filterTypes)->map(function (string $type) use ($countByType, $selectedType, $memberId, $request, $lang) {
            $total = $type === 'all' ? (int) $countByType->sum() : (int) ($countByType[$type] ?? 0);

            return [
                'value' => $type,
                'label' => $type === 'all' ? (strtolower($lang) === 'ar' ? 'الكل' : 'All') : $this->mobileTrainingTypeLabel($type, $lang),
                'count' => $total,
                'active' => $selectedType === $type,
                'url' => $this->buildMobileRouteWithToken('sw.training-member-log-mobile', [
                    'type' => $type,
                ], $request, $lang),
            ];
        })->all();

        return view('software::Front.training_member_log_mobile', [
            'title' => trans('sw.training_member_logs'),
            'member' => $memberModel,
            'memberId' => $memberId,
            'logs' => $logs,
            'filters' => $filters,
            'selectedType' => $selectedType,
            'lang' => $lang,
            'isArabic' => strtolower($lang) === 'ar',
        ]);
    }

    public function showTrainingMemberLogMobileDetail(Request $request, $log)
    {
        $lang = $this->resolveMobileLanguage($request);
        $this->currentMember = $currentUser = $this->resolveMemberFromRequest($request);

        if (!$currentUser) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $memberId = (int) $currentUser->id;

        $memberModel = GymMember::find($memberId);
        if (!$memberModel) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $logModel = GymTrainingMemberLog::query()
            ->where('member_id', $memberId)
            ->where('id', (int) $log)
            ->first();

        if (!$logModel) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $details = $this->resolveMobileTrainingLogDetails($logModel, $lang);
        $summary = $this->buildMobileLogSummary($logModel, $details, $lang);
        $backUrl = $this->buildMobileRouteWithToken('sw.training-member-log-mobile', [
            // 'type' => (string) ($logModel->training_type ?? 'all'),
            'type' => 'all',
        ], $request, $lang);

        return view('software::Front.training_member_log_mobile_detail', [
            'title' => trans('sw.training_member_logs'),
            'member' => $memberModel,
            'memberId' => $memberId,
            'log' => $logModel,
            'details' => $details,
            'summary' => $summary,
            'typeLabel' => $this->mobileTrainingTypeLabel((string) ($logModel->training_type ?? 'activity'), $lang),
            'actionLabel' => $this->mobileActionLabel((string) ($logModel->action ?? 'updated'), $lang),
            'backUrl' => $backUrl,
            'lang' => $lang,
            'isArabic' => strtolower($lang) === 'ar',
        ]);
    }

    private function resolveMobileLanguage(Request $request): string
    {
        $lang = trim((string) ($request->input('lang') ?: app()->getLocale() ?: env('DEFAULT_LANG', 'en')));
        if (!in_array($lang, ['ar', 'en'], true)) {
            $lang = env('DEFAULT_LANG', 'en');
        }

        app()->setLocale($lang);
        Carbon::setLocale($lang);

        return $lang;
    }

    private function buildMobileRouteWithToken(string $routeName, array $params, Request $request, string $lang): string
    {
        $params = $this->mobileContextParams($params, $request);
        $params['lang'] = $lang;

        return route($routeName, $params);
    }

    protected function mobileTokenParam(?Request $request = null): string
    {
        $request = $request ?: request();
        $token = trim((string) ($request->input('payment_link_token') ?: $request->input('token') ?: $request->bearerToken() ?: ''));
        return $token !== '' ? $token : 'null';
    }

    protected function mobileMemberIdParam(?Request $request = null): string
    {
        $request = $request ?: request();
        $memberId = optional($this->currentMember)->id ?: $request->input('member_id');
        return $memberId ? (string) $memberId : 'null';
    }

    protected function mobileContextParams(array $params = [], ?Request $request = null): array
    {
        $params['token'] = $this->mobileTokenParam($request);
        $params['member_id'] = $this->mobileMemberIdParam($request);
        return $params;
    }

    private function resolveMobileTrainingLogDetails(GymTrainingMemberLog $log, string $lang): array
    {
        $meta = $this->parseTrainingLogMeta($log->meta);
        $type = (string) ($log->training_type ?? 'activity');

        if ($type === 'assessment') {
            $assessment = GymTrainingAssessment::find((int) ($log->reference_id ?? 0));
            $answers = $assessment
                ? (is_array($assessment->answers) ? $assessment->answers : (json_decode((string) $assessment->answers, true) ?: []))
                : [];

            return [
                'summary' => trim((string) ($assessment->notes ?? $log->notes ?? '')),
                'notes' => (string) ($assessment->notes ?? ''),
                'answers' => $answers,
                'date' => optional($assessment?->created_at)->format('Y-m-d'),
            ];
        }

        if ($type === 'plan') {
            $memberPlanId = (int) ($log->reference_id ?: ($meta['member_plan_id'] ?? 0));
            $logTrainingId = (int) ($log->training_id ?? 0);

            $planAssignment = $memberPlanId
                ? DB::table('sw_gym_training_members')->where('id', $memberPlanId)->first()
                : null;

            // Some environments/log writers store assignment id in training_id instead of reference_id.
            if (!$planAssignment && $logTrainingId > 0) {
                $planAssignment = DB::table('sw_gym_training_members')
                    ->where('id', $logTrainingId)
                    ->where('member_id', (int) $log->member_id)
                    ->first();

                if ($planAssignment) {
                    $memberPlanId = (int) ($planAssignment->id ?? $memberPlanId);
                }
            }

            $planId = (int) ($meta['plan_id'] ?? 0);
            if ($planAssignment && !$planId) {
                $planId = (int) ($planAssignment->plan_id ?? $planAssignment->training_plan_id ?? $planAssignment->diet_plan_id ?? 0);
            }

            // If no assignment-linked plan id found, fallback to training_id as direct plan id.
            if (!$planId && $logTrainingId > 0) {
                $planId = $logTrainingId;
            }

            $plan = $planId
                ? GymTrainingPlan::with(['tasks' => function ($q) {
                    $q->orderBy('order', 'asc')->orderBy('id', 'asc');
                }])->find($planId)
                : null;

            $summary = trim((string) ($planAssignment->title ?? $plan->title ?? $log->notes ?? ''));
            if ($summary === '') {
                $summary = strtolower($lang) === 'ar' ? 'خطة تدريب' : 'Training Plan';
            }

            $planTasks = collect($plan?->tasks ?? [])->map(function ($task) use ($lang) {
                return $this->mapMobilePlanTask($task, $lang);
            })->filter(function (array $task) {
                return $task['title'] !== '' || $task['notes'] !== '';
            });

            if ($planTasks->isEmpty()) {
                $planTasks = collect($this->extractMobilePlanTasksFromMeta($meta))->map(function ($task) use ($lang) {
                    return $this->mapMobilePlanTask($task, $lang);
                })->filter(function (array $task) {
                    return $task['title'] !== '' || $task['notes'] !== '';
                });
            }

            return [
                'summary' => $summary,
                'title' => (string) ($planAssignment->title ?? $plan->title ?? ''),
                'notes' => (string) ($planAssignment->notes ?? ($meta['notes'] ?? '')),
                'from_date' => $planAssignment->from_date ?? ($meta['from_date'] ?? null),
                'to_date' => $planAssignment->to_date ?? ($meta['to_date'] ?? null),
                'tasks' => $planTasks->values()->all(),
                'plan_details' => (string) (
                    $planAssignment->training_plan_details
                    ?? $planAssignment->diet_plan_details
                    ?? $planAssignment->plan_details
                    ?? $plan->content
                    ?? ''
                ),
                'member_plan_id' => $memberPlanId,
            ];
        }

        if ($type === 'medicine') {
            $medicineId = (int) ($log->reference_id ?: ($meta['medicine_id'] ?? 0));
            $medicine = $medicineId ? GymTrainingMedicine::find($medicineId) : null;
            $name = $medicine
                ? ($medicine->{'name_' . $lang} ?? $medicine->name_en ?? $medicine->name_ar ?? $medicine->name ?? '')
                : (string) ($meta['medicine_name'] ?? '');
            $dose = (string) ($meta['dose'] ?? ($medicine->dose ?? ''));
            $notes = (string) ($meta['notes'] ?? $log->notes ?? '');

            return [
                'summary' => trim(implode(' - ', array_filter([$name, $dose, $notes]))),
                'name' => $name,
                'dose' => $dose,
                'notes' => $notes,
            ];
        }

        if ($type === 'file') {
            $file = $log->reference_id ? GymTrainingFile::find((int) $log->reference_id) : null;
            $fileName = (string) ($file->file_name ?? $file->file_path ?? '');
            $path = trim((string) ($meta['path'] ?? ''));
            if ($path === '' && $fileName !== '') {
                $path = asset('uploads/training_files/' . ltrim($fileName, '/'));
            } elseif ($path !== '' && !preg_match('/^https?:\/\//i', $path)) {
                $path = asset(ltrim($path, '/'));
            }

            return [
                'summary' => (string) ($file->title ?? $fileName ?? $log->notes ?? ''),
                'title' => (string) ($file->title ?? ''),
                'file_name' => $fileName,
                'path' => $path,
            ];
        }

        if ($type === 'track') {
            $track = $log->reference_id ? GymTrainingTrack::find((int) $log->reference_id) : null;

            return [
                'summary' => trim((string) ($log->notes ?: trans('sw.progress_measurement_added'))),
                'measurements' => $track ? $this->mobileTrackMeasurements($track) : [],
                'calculations' => $track ? $this->mobileTrackCalculations($track, GymMember::find((int) $log->member_id)) : [],
            ];
        }

        if ($type === 'note') {
            return [
                'summary' => (string) ($log->notes ?? ''),
                'note' => (string) ($log->notes ?? ''),
            ];
        }

        if (in_array($type, ['ai', 'ai_plan'], true)) {
            $ai = $log->reference_id ? GymAiRecommendation::find((int) $log->reference_id) : null;
            $response = $ai && is_array($ai->ai_response) ? $ai->ai_response : [];

            return [
                'summary' => (string) ($response['summary'] ?? $response['title'] ?? $log->notes ?? trans('sw.ai_recommendation_generated')),
                'title' => (string) ($response['title'] ?? ''),
                'response' => $response,
            ];
        }

        return [
            'summary' => (string) ($log->notes ?? ''),
            'meta' => $meta,
        ];
    }

    private function parseTrainingLogMeta($meta): array
    {
        if (is_array($meta)) {
            return $meta;
        }

        if (is_string($meta) && trim($meta) !== '') {
            $decoded = json_decode($meta, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function extractMobilePlanTasksFromMeta(array $meta): array
    {
        $sources = [
            $meta['tasks'] ?? null,
            $meta['plan_tasks'] ?? null,
            $meta['task_details'] ?? null,
        ];

        foreach ($sources as $source) {
            if (is_array($source)) {
                return $source;
            }

            if (is_string($source) && trim($source) !== '') {
                $decoded = json_decode($source, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return [];
    }

    private function mapMobilePlanTask($task, string $lang): array
    {
        $isArabic = strtolower($lang) === 'ar';
        $read = function ($key, $default = null) use ($task) {
            if (is_array($task)) {
                return $task[$key] ?? $default;
            }

            return $task->{$key} ?? $default;
        };

        $title = trim((string) (
            $read('title')
            ?? $read('name_' . $lang)
            ?? $read('name_en')
            ?? $read('name_ar')
            ?? ''
        ));

        $description = trim((string) (
            $read('description')
            ?? $read('details')
            ?? $read('content')
            ?? ''
        ));

        $extra = [];
        if ($read('day_name')) {
            $extra[] = ($isArabic ? 'اليوم: ' : 'Day: ') . (string) $read('day_name');
        }
        if ($read('t_group')) {
            $extra[] = ($isArabic ? 'المجموعة: ' : 'Group: ') . (string) $read('t_group');
        }
        if ($read('t_repeats')) {
            $extra[] = ($isArabic ? 'التكرارات: ' : 'Repeats: ') . (string) $read('t_repeats');
        }
        if ($read('t_rest')) {
            $extra[] = ($isArabic ? 'الراحة: ' : 'Rest: ') . (string) $read('t_rest');
        }
        if ($read('d_calories')) {
            $extra[] = ($isArabic ? 'السعرات: ' : 'Calories: ') . (string) $read('d_calories');
        }
        if ($read('d_protein')) {
            $extra[] = ($isArabic ? 'البروتين: ' : 'Protein: ') . (string) $read('d_protein');
        }
        if ($read('d_carb')) {
            $extra[] = ($isArabic ? 'الكربوهيدرات: ' : 'Carb: ') . (string) $read('d_carb');
        }
        if ($read('d_fats')) {
            $extra[] = ($isArabic ? 'الدهون: ' : 'Fats: ') . (string) $read('d_fats');
        }

        $notes = trim(implode("\n", array_filter([$description, implode("\n", $extra)])));

        return [
            'id' => (int) ($read('id', 0) ?: 0),
            'title' => $title,
            'notes' => $notes,
        ];
    }

    private function buildMobileLogSummary(GymTrainingMemberLog $log, array $details, string $lang): string
    {
        $summary = trim((string) ($details['summary'] ?? $log->notes ?? ''));
        if ($summary === '') {
            $summary = trim(ucfirst(str_replace('_', ' ', (string) ($log->training_type ?? 'activity') . ' ' . (string) ($log->action ?? 'updated'))));
        }

        if (mb_strlen($summary) > 220) {
            $summary = mb_substr($summary, 0, 220) . '...';
        }

        if (strtolower($lang) !== 'ar') {
            return $summary;
        }

        return $summary;
    }

    private function mobileTrainingTypeLabel(string $type, string $lang): string
    {
        $isArabic = strtolower($lang) === 'ar';

        return match ($type) {
            'assessment' => $isArabic ? 'تقييم' : 'Assessment',
            'plan' => $isArabic ? 'خطة' : 'Plan',
            'medicine' => $isArabic ? 'دواء' : 'Medicine',
            'note' => $isArabic ? 'ملاحظة' : 'Note',
            'file' => $isArabic ? 'ملف' : 'File',
            'track' => $isArabic ? 'قياس' : 'Track',
            'ai', 'ai_plan' => $isArabic ? 'خطة ذكية' : 'AI Plan',
            default => $isArabic ? 'نشاط' : 'Activity',
        };
    }

    private function mobileActionLabel(string $action, string $lang): string
    {
        $isArabic = strtolower($lang) === 'ar';

        return match ($action) {
            'added' => $isArabic ? 'تمت الإضافة' : 'Added',
            'assigned' => $isArabic ? 'تم الإسناد' : 'Assigned',
            'uploaded' => $isArabic ? 'تم الرفع' : 'Uploaded',
            'generated' => $isArabic ? 'تم الإنشاء' : 'Generated',
            'updated' => $isArabic ? 'تم التحديث' : 'Updated',
            default => ucfirst(str_replace('_', ' ', $action)),
        };
    }

    private function mobileTrackMeasurements(GymTrainingTrack $track): array
    {
        $appendUnit = function ($value, string $unit): ?string {
            if (is_null($value) || $value === '') {
                return null;
            }

            return rtrim(rtrim((string) $value, '0'), '.') . ' ' . $unit;
        };

        return array_filter([
            'weight' => $appendUnit($track->weight, 'kg'),
            'height' => $appendUnit($track->height, 'cm'),
            'bmi' => $track->bmi ? (string) round((float) $track->bmi, 2) : null,
            'fat_percentage' => $track->fat_percentage ? (string) round((float) $track->fat_percentage, 2) . '%' : null,
            'muscle_mass' => $appendUnit($track->muscle_mass, 'kg'),
            'neck_circumference' => $appendUnit($track->neck_circumference, 'cm'),
            'chest_circumference' => $appendUnit($track->chest_circumference, 'cm'),
            'arm_circumference' => $appendUnit($track->arm_circumference, 'cm'),
            'abdominal_circumference' => $appendUnit($track->abdominal_circumference, 'cm'),
            'pelvic_circumference' => $appendUnit($track->pelvic_circumference, 'cm'),
            'thigh_circumference' => $appendUnit($track->thigh_circumference, 'cm'),
        ], function ($value) {
            return !is_null($value) && $value !== '';
        });
    }

    private function mobileTrackCalculations(GymTrainingTrack $track, ?GymMember $member): array
    {
        $weight = (float) ($track->weight ?? 0);
        $height = (float) ($track->height ?? 0);
        $fatPercentage = (float) ($track->fat_percentage ?? 0);

        if ($weight <= 0 || $height <= 0) {
            return [];
        }

        $heightMeters = $height / 100;
        if ($heightMeters <= 0) {
            return [];
        }

        $gender = (int) ($member->gender ?? 1);
        $age = 30;
        if (!empty($member->dob)) {
            $age = Carbon::parse($member->dob)->age;
        }

        $bmr = $gender === 2
            ? (10 * $weight + 6.25 * $height - 5 * $age - 161)
            : (10 * $weight + 6.25 * $height - 5 * $age + 5);
        $tdee = $bmr * 1.55;
        $bmi = $weight / ($heightMeters * $heightMeters);

        $result = [
            'bmr' => round($bmr, 2) . ' kcal/day',
            'tdee' => round($tdee, 2) . ' kcal/day',
            'bmi' => round($bmi, 2),
        ];

        if ($fatPercentage > 0) {
            $fatMass = ($weight * $fatPercentage) / 100;
            $result['body_fat_mass'] = round($fatMass, 2) . ' kg';
            $result['lean_body_mass'] = round($weight - $fatMass, 2) . ' kg';
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 2 — Process form & redirect to gateway
    // ─────────────────────────────────────────────────────────────────────────

    public function invoiceSubmit(Request $request)
    {
        $this->currentMember = $this->resolveMemberFromRequest($request, false);

        $subscriptionId = $request->input('subscription_id');
        $subscription   = GymSubscription::where('id', $subscriptionId)->first();

        if (!$subscription) {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        // ── Build member data ──────────────────────────────────────────────
        $memberData = [];

        if (!$this->currentMember) {
            // Guest — collect personal data from form
            if (GymMember::where('phone', $request->phone)->exists()) {
                return redirect()->back()->with('error', trans('front.error_member_exist'));
            }
            $memberData['name']    = $request->name;
            $memberData['phone']   = $request->phone;
            $memberData['email']   = $request->email;
            $memberData['address'] = $request->address;
            $memberData['dob']     = $request->dob ? Carbon::parse($request->dob) : null;
            $memberData['gender']  = $request->gender;
        } else {
            // Logged-in member — check no active subscription on chosen joining date
            $overlap = GymMemberSubscription::where('member_id', $this->currentMember->id)
                ->where('joining_date', '<=', $request->joining_date)
                ->where('expire_date',  '>=', $request->joining_date)
                ->first();

            if ($overlap) {
                return redirect()->back()->with('error', trans('front.error_member_subscription_joining_date'));
            }

            $memberData['name']    = $this->currentMember->name;
            $memberData['phone']   = $this->currentMember->phone;
            $memberData['email']   = $this->currentMember->email;
            $memberData['address'] = $this->currentMember->address;
            $memberData['dob']     = $this->currentMember->dob;
            $memberData['gender']  = $this->currentMember->gender;
        }

        // ── Amounts ────────────────────────────────────────────────────────
        $memberData['subscription_id']  = $subscriptionId;
        $memberData['joining_date']     = $request->joining_date;
        $memberData['payment_method']   = (int) $request->payment_method;
        $memberData['payment_channel']  = TypeConstants::CHANNEL_MOBILE_APP; // 3
        $memberData['amount']           = (float) $request->amount;
        $memberData['vat_percentage']   = (float) $request->vat_percentage;

        $vatPct = (float) $request->vat_percentage;
        if ($vatPct > 0) {
            $base                 = $memberData['amount'] / (1 + $vatPct / 100);
            $memberData['vat']    = round($memberData['amount'] - $base, 2);
        } else {
            $memberData['vat'] = 0;
        }

        // ── Route to correct gateway ───────────────────────────────────────
        $paymentMethod = $memberData['payment_method'];

        // Values sent from the form: 2=Tabby, 4=Tamara, 5=PayTabs, 6=Paymob
        if ($paymentMethod === 2) {
            $paymentUrl = $this->initiateTabby($subscription->toArray(), $memberData);
        } elseif ($paymentMethod === 4) {
            $paymentUrl = $this->initiateTamara($subscription->toArray(), $memberData);
        } elseif ($paymentMethod === 5) {
            $paymentUrl = $this->initiatePaytabs($subscription->toArray(), $memberData);
        } elseif ($paymentMethod === 6) {
            $paymentUrl = $this->initiatePaymob($subscription->toArray(), $memberData);
        } else {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        return redirect($paymentUrl);
    }

    public function activityInvoiceSubmit(Request $request)
    {
        $this->currentMember = $this->resolveMemberFromRequest($request, false);

        // Support multiple selected activities
        $activityIds = array_values(array_filter(array_map('intval', (array) $request->input('activity_ids', []))));
        $primaryId = (int) $request->input('activity_id');
        if (empty($activityIds) && $primaryId) {
            $activityIds = [$primaryId];
        }
        if (empty($activityIds)) {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        $selectedActivities = GymActivity::whereIn('id', $activityIds)->get();
        if ($selectedActivities->isEmpty()) {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        $memberData = $this->buildGenericMemberData($request);
        if (!$memberData) {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        $vatPercentage = (float) (@$this->mainSettings->vat_details['vat_percentage'] ?? 0);
        $baseAmount = (float) $selectedActivities->sum('price');
        $vatAmount = $vatPercentage > 0 ? round(($baseAmount * $vatPercentage) / 100, 2) : 0;
        $amount = round($baseAmount + $vatAmount, 2);

        $memberData['joining_date'] = Carbon::now()->toDateString();
        $memberData['payment_method'] = (int) $request->input('payment_method');
        $memberData['payment_channel'] = TypeConstants::CHANNEL_MOBILE_APP;
        $memberData['amount'] = $amount;
        $memberData['vat_percentage'] = $vatPercentage;
        $memberData['vat'] = $vatAmount;
        // Store full activity objects so invoice can display without re-querying
        $memberData['activity_ids'] = $selectedActivities->map(fn($a) => [
            'id'                   => $a->id,
            'name_ar'              => $a->name_ar,
            'name_en'              => $a->name_en,
            'price'                => $a->price,
            'reservation_limit'    => $a->reservation_limit,
            'reservation_duration' => $a->reservation_duration ?? null,
            'reservation_period'   => $a->reservation_period ?? null,
            'name'                 => $a->name ?? $a->name_ar,
            'content'              => $a->content ?? null,
            'image_name'           => $a->image_name ?? null,
        ])->values()->toArray();

        $primaryActivity = $selectedActivities->firstWhere('id', $primaryId) ?? $selectedActivities->first();
        $primaryId = (int) $primaryActivity->id;
        $itemName = $selectedActivities->count() === 1
            ? ($primaryActivity->name ?? 'Activity')
            : ($primaryActivity->name . ' (+' . ($selectedActivities->count() - 1) . ')');

        $paymentMethod = $memberData['payment_method'];
        if ($paymentMethod === 2) {
            $paymentUrl = $this->initiateGenericTabby('activity', $primaryId, $itemName, $memberData);
        } elseif ($paymentMethod === 4) {
            $paymentUrl = $this->initiateGenericTamara('activity', $primaryId, $itemName, (string)($primaryActivity->content ?? ''), $memberData);
        } elseif ($paymentMethod === 5) {
            $paymentUrl = $this->initiateGenericPaytabs('activity', $primaryId, $itemName, $memberData);
        } elseif ($paymentMethod === 6) {
            $paymentUrl = $this->initiateGenericPaymob('activity', $primaryId, $itemName, $memberData);
        } else {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        return redirect($paymentUrl);
    }

    public function storeInvoiceSubmit(Request $request)
    {
        $this->currentMember = $this->resolveMemberFromRequest($request, false);

        // store_items: array of {id, qty}
        $storeItemsRaw = (array) $request->input('store_items', []);
        $primaryProductId = (int) $request->input('product_id');

        // Build validated items list
        $storeItems = [];
        foreach ($storeItemsRaw as $item) {
            $pid = (int) ($item['id'] ?? 0);
            $qty = max(1, (int) ($item['qty'] ?? 1));
            if ($pid > 0) $storeItems[] = ['id' => $pid, 'qty' => $qty];
        }
        if (empty($storeItems) && $primaryProductId) {
            $storeItems = [['id' => $primaryProductId, 'qty' => 1]];
        }
        if (empty($storeItems)) {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        $productIds = array_column($storeItems, 'id');
        $selectedProducts = GymStoreProduct::whereIn('id', $productIds)->get()->keyBy('id');
        if ($selectedProducts->isEmpty()) {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        $memberData = $this->buildGenericMemberData($request);
        if (!$memberData) {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        $vatPercentage = (float) (@$this->mainSettings->vat_details['vat_percentage'] ?? 0);
        $baseAmount = 0;
        foreach ($storeItems as $item) {
            $p = $selectedProducts->get($item['id']);
            if ($p) $baseAmount += (float) $p->price * $item['qty'];
        }
        $vatAmount = $vatPercentage > 0 ? round(($baseAmount * $vatPercentage) / 100, 2) : 0;
        $amount = round($baseAmount + $vatAmount, 2);

        $memberData['joining_date'] = Carbon::now()->toDateString();
        $memberData['payment_method'] = (int) $request->input('payment_method');
        $memberData['payment_channel'] = TypeConstants::CHANNEL_MOBILE_APP;
        $memberData['amount'] = $amount;
        $memberData['vat_percentage'] = $vatPercentage;
        $memberData['vat'] = $vatAmount;
        $memberData['store_product_items'] = $storeItems; // stored in invoice response_code

        $primaryProduct = $selectedProducts->get($primaryProductId) ?? $selectedProducts->first();
        $primaryProductId = (int) $primaryProduct->id;
        $itemCount = count($storeItems);
        $itemName = $itemCount === 1
            ? ($primaryProduct->name ?? 'Product')
            : ($primaryProduct->name . ' (+' . ($itemCount - 1) . ')');

        $paymentMethod = $memberData['payment_method'];
        if ($paymentMethod === 2) {
            $paymentUrl = $this->initiateGenericTabby('store', $primaryProductId, $itemName, $memberData);
        } elseif ($paymentMethod === 4) {
            $paymentUrl = $this->initiateGenericTamara('store', $primaryProductId, $itemName, (string)($primaryProduct->content ?? ''), $memberData);
        } elseif ($paymentMethod === 5) {
            $paymentUrl = $this->initiateGenericPaytabs('store', $primaryProductId, $itemName, $memberData);
        } elseif ($paymentMethod === 6) {
            $paymentUrl = $this->initiateGenericPaymob('store', $primaryProductId, $itemName, $memberData);
        } else {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        return redirect($paymentUrl);
    }

    protected function buildGenericMemberData(Request $request): ?array
    {
        if ($this->currentMember) {
            return [
                'name'    => $this->currentMember->name,
                'phone'   => $this->currentMember->phone,
                'email'   => $this->currentMember->email ?? '',
                'address' => $this->currentMember->address ?? '',
                'dob'     => $this->currentMember->dob,
                'gender'  => $this->currentMember->gender,
            ];
        }

        // Only name + phone are required for activity/store guests
        if (!$request->name || !$request->phone) {
            return null;
        }

        if (GymMember::where('phone', $request->phone)->exists()) {
            return null;
        }

        return [
            'name'    => $request->name,
            'phone'   => $request->phone,
            'email'   => $request->email ?? '',
            'address' => $request->address ?? '',
            'dob'     => $request->dob ? Carbon::parse($request->dob) : null,
            'gender'  => $request->gender ?? 1,
        ];
    }

    protected function createGenericInvoice(array $member, int $paymentMethod, string $uniqueId, string $type, int $itemId): GymOnlinePaymentInvoice
    {
        return GymOnlinePaymentInvoice::create([
            'payment_id'      => $uniqueId,
            'member_id'       => optional($this->currentMember)->id,
            'status'          => TypeConstants::PENDING,
            'subscription_id' => null,
            'name'            => $member['name'],
            'email'           => $member['email'] ?? '',
            'phone'           => $member['phone'],
            'dob'             => $member['dob'],
            'address'         => $member['address'],
            'gender'          => $member['gender'],
            'amount'          => $member['amount'],
            'vat'             => $member['vat'],
            'vat_percentage'  => $member['vat_percentage'],
            'payment_method'  => $paymentMethod,
            'payment_channel' => $member['payment_channel'],
            'response_code'   => array_filter([
                'joining_date'        => $member['joining_date'],
                "is_{$type}"          => true,
                "{$type}_id"          => $itemId,
                'activity_ids'        => $member['activity_ids'] ?? null,
                'store_product_items' => $member['store_product_items'] ?? null,
                'token'               => $this->mobileTokenParam(),
                'member_id'           => $this->mobileMemberIdParam(),
            ], fn($v) => $v !== null),
        ]);
    }

    protected function genericErrorRoute(string $type, int $itemId): string
    {
        if ($type === 'activity') {
            return route('sw.activity-mobile', $this->mobileContextParams(['id' => $itemId]));
        }
        return route('sw.store-mobile', $this->mobileContextParams(['id' => $itemId]));
    }

    protected function initiateGenericTabby(string $type, int $itemId, string $itemName, array $member): string
    {
        $uniqueId   = uniqid();
        $invoice    = $this->createGenericInvoice($member, TypeConstants::TABBY_TRANSACTION, $uniqueId, $type, $itemId);
        $errorRoute = $this->genericErrorRoute($type, $itemId);

        $tabby  = new TabbyFrontController();
        $result = $tabby->createCheckoutSession([
            'amount'          => round($member['amount'], 2),
            'currency'        => env('TABBY_CURRENCY', 'SAR'),
            'description'     => $itemName,
            'buyer'           => ['name' => $member['name'], 'phone' => $member['phone'], 'email' => $member['email'] ?? '', 'address' => '', 'city' => '', 'zip' => '', 'country' => 'SA'],
            'order_reference' => (string) $invoice->id,
            'loyalty_level'   => 0,
            'order_history'   => [],
            'success_url'     => route('sw.tabby-mobile.verify',  ['invoice_id' => $uniqueId]),
            'cancel_url'      => route('sw.tabby-mobile.cancel',  ['invoice_id' => $uniqueId]),
            'failure_url'     => route('sw.tabby-mobile.failure', ['invoice_id' => $uniqueId]),
            'payment_type'    => $type . '_payment',
            'member_id'       => optional($this->currentMember)->id,
        ]);

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }

        $invoice->transaction_id = $result['checkout_id'];
        $invoice->save();
        return $result['payment_url'];
    }

    protected function initiateGenericTamara(string $type, int $itemId, string $itemName, string $itemContent, array $member): string
    {
        $uniqueId   = uniqid();
        $invoice    = $this->createGenericInvoice($member, TypeConstants::TAMARA_TRANSACTION, $uniqueId, $type, $itemId);
        $errorRoute = $this->genericErrorRoute($type, $itemId);

        $verifyUrl  = route('sw.tamara-mobile.verify', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $cancelUrl  = route('sw.tamara-mobile.cancel', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $failureUrl = route('sw.tamara-mobile.failure', $this->mobileContextParams(['invoice_id' => $uniqueId]));

        [, , $currency] = $this->getTamaraCredentials();
        $tamara = new TamaraFrontController();
        $result = $tamara->createCheckoutSession([
            'amount'           => round($member['amount'], 2),
            'currency'         => $currency,
            'description'      => $itemName,
            'buyer'            => ['name' => $member['name'], 'phone' => $member['phone'], 'email' => $member['email'] ?? '', 'address' => $member['address'] ?? '', 'city' => env('TAMARA_CITY', 'Riyadh')],
            'order_reference'  => (string) $invoice->id,
            'success_url'      => $verifyUrl,
            'cancel_url'       => $cancelUrl,
            'failure_url'      => $failureUrl,
            'notification_url' => route('tamara.webhook'),
            'payment_type'     => 'mobile_' . $type,
            'member_id'        => optional($this->currentMember)->id,
            'items'            => [[
                'title' => $itemName,
                'description' => $itemContent,
                'quantity' => 1,
                'unit_price' => round((float) $member['amount'] - (float) $member['vat'], 2),
                'total_amount' => round($member['amount'], 2),
                'reference_id' => (string) $invoice->id,
            ]],
        ]);

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }

        $invoice->transaction_id = $result['order_id'];
        $invoice->save();
        return $result['payment_url'];
    }

    protected function initiateGenericPaytabs(string $type, int $itemId, string $itemName, array $member): string
    {
        $uniqueId   = uniqid();
        $invoice    = $this->createGenericInvoice($member, TypeConstants::PAYTABS_TRANSACTION, $uniqueId, $type, $itemId);
        $errorRoute = $this->genericErrorRoute($type, $itemId);

        $verifyUrl  = route('sw.paytabs-mobile.verify', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $cancelUrl  = route('sw.paytabs-mobile.cancel', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $failureUrl = route('sw.paytabs-mobile.failure', $this->mobileContextParams(['invoice_id' => $uniqueId]));

        $paytabs = new PayTabsFrontController();
        $result  = $paytabs->createCheckoutSession([
            'amount'          => round($member['amount'], 2),
            'description'     => $itemName,
            'buyer'           => ['name' => $member['name'], 'email' => $member['email'] ?: 'member@gym.com', 'phone' => $member['phone'], 'city' => '', 'address' => ''],
            'cart_id'         => (string) $invoice->id,
            'success_url'     => $verifyUrl,
            'cancel_url'      => $cancelUrl,
            'failure_url'     => $failureUrl,
            'callback_url'    => $verifyUrl,
            'payment_type'    => $type . '_payment',
            'member_id'       => optional($this->currentMember)->id,
        ]);

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }

        $invoice->transaction_id = $result['tran_ref'] ?? '';
        $invoice->save();
        return $result['redirect_url'] ?? ($result['payment_url'] ?? $errorRoute);
    }

    protected function initiateGenericPaymob(string $type, int $itemId, string $itemName, array $member): string
    {
        $uniqueId   = uniqid();
        $invoice    = $this->createGenericInvoice($member, TypeConstants::PAYMOB_TRANSACTION, $uniqueId, $type, $itemId);
        $errorRoute = $this->genericErrorRoute($type, $itemId);

        $nameParts   = explode(' ', $member['name'], 2);
        $billingData = [
            'first_name' => $nameParts[0] ?? 'Gym', 'last_name' => $nameParts[1] ?? 'Member',
            'email' => $member['email'] ?: 'member@gym.com', 'phone_number' => $member['phone'],
            'apartment' => 'NA', 'floor' => 'NA', 'street' => $member['address'] ?: 'NA',
            'building' => 'NA', 'shipping_method' => 'NA', 'postal_code' => 'NA',
            'city' => 'NA', 'country' => 'EG', 'state' => 'NA',
        ];

        $paymob    = new PaymobFrontController();
        $iframeUrl = $paymob->payment([
            'name'         => $itemName,
            'price'        => round($member['amount'], 2),
            'desc'         => $itemName,
            'qty'          => 1,
            'no_fee'       => true,
            'billing_data' => $billingData,
            'redirect_url' => route('sw.paymob-mobile.verify', ['invoice_id' => $uniqueId]),
        ]);

        if (!$iframeUrl) {
            \Session::flash('error', trans('front.error_in_data'));
            $invoice->status = TypeConstants::FAILURE;
            $invoice->save();
            return $errorRoute;
        }
        return $iframeUrl;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 3a — Tabby: create checkout
    // ─────────────────────────────────────────────────────────────────────────

    protected function initiateTabby(array $subscription, array $member): string
    {
        $totalAmount    = round($member['amount'], 2);
        $priceBeforeVat = round($totalAmount - $member['vat'], 2);
        $uniqueId       = uniqid();

        $invoice = GymOnlinePaymentInvoice::create([
            'payment_id'      => $uniqueId,
            'member_id'       => optional($this->currentMember)->id,
            'status'          => TypeConstants::PENDING,
            'subscription_id' => $member['subscription_id'],
            'name'            => $member['name'],
            'email'           => $member['email'] ?? '',
            'phone'           => $member['phone'],
            'dob'             => $member['dob'],
            'address'         => $member['address'],
            'gender'          => $member['gender'],
            'amount'          => $totalAmount,
            'vat'             => $member['vat'],
            'vat_percentage'  => $member['vat_percentage'],
            'payment_method'  => TypeConstants::TABBY_TRANSACTION,
            'payment_channel' => $member['payment_channel'],
            'response_code'   => ['joining_date' => $member['joining_date']],
        ]);

        // Build order history for Tabby buyer_history
        $orderHistory = [];
        $loyaltyLevel = 0;
        if ($this->currentMember) {
            $loyaltyLevel = GymMemberSubscription::where('member_id', $this->currentMember->id)->count();
            $previousSubs = GymMemberSubscription::where('member_id', $this->currentMember->id)
                ->orderBy('created_at', 'desc')->limit(10)->get();
            foreach ($previousSubs as $sub) {
                $orderHistory[] = [
                    'purchased_at' => Carbon::parse($sub->joining_date ?? $sub->created_at)->toISOString(),
                    'amount'       => (string) round($sub->amount_paid, 2),
                    'status'       => 'complete',
                    'buyer'        => ['phone' => $member['phone'], 'email' => $member['email'] ?? '', 'name' => $member['name']],
                    'shipping_address' => ['city' => '', 'address' => '', 'zip' => '', 'country' => 'SA'],
                    'payment_method' => 'card',
                ];
            }
        }

        $tabby = new TabbyFrontController();
        $result = $tabby->createCheckoutSession([
            'amount'           => $totalAmount,
            'currency'         => env('TABBY_CURRENCY', 'SAR'),
            'description'      => $subscription['name'] ?? '',
            'buyer'            => [
                'name'    => $member['name'],
                'phone'   => $member['phone'],
                'email'   => $member['email'] ?? '',
                'address' => '',
                'city'    => '',
                'zip'     => '',
                'country' => 'SA',
            ],
            'order_reference'  => (string) $invoice->id,
            'loyalty_level'    => $loyaltyLevel,
            'order_history'    => $orderHistory,
            'success_url'      => route('sw.tabby-mobile.verify',  ['invoice_id' => $uniqueId]),
            'cancel_url'       => route('sw.tabby-mobile.cancel',  ['invoice_id' => $uniqueId]),
            'failure_url'      => route('sw.tabby-mobile.failure', ['invoice_id' => $uniqueId]),
            'payment_type'     => 'member_subscription',
            'member_id'        => optional($this->currentMember)->id,
            'subscription_id'  => $member['subscription_id'],
        ]);

        $errorRoute = route('sw.subscription-mobile', $this->mobileContextParams(['id' => $subscription['id']]));

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }

        $invoice->transaction_id = $result['checkout_id'];
        $invoice->response_code  = array_merge(
            (array) $invoice->response_code,
            ['tabby_checkout' => $result, 'joining_date' => $member['joining_date']]
        );
        $invoice->save();

        return $result['payment_url'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 3b — Tamara: create checkout
    // ─────────────────────────────────────────────────────────────────────────

    protected function initiateTamara(array $subscription, array $member): string
    {
        $totalAmount    = round($member['amount'], 2);
        $priceBeforeVat = round($totalAmount - $member['vat'], 2);
        $uniqueId       = uniqid();

        $invoice = GymOnlinePaymentInvoice::create([
            'payment_id'      => $uniqueId,
            'member_id'       => optional($this->currentMember)->id,
            'status'          => TypeConstants::PENDING,
            'subscription_id' => $member['subscription_id'],
            'name'            => $member['name'],
            'email'           => $member['email'] ?? '',
            'phone'           => $member['phone'],
            'dob'             => $member['dob'],
            'address'         => $member['address'],
            'gender'          => $member['gender'],
            'amount'          => $totalAmount,
            'vat'             => $member['vat'],
            'vat_percentage'  => $member['vat_percentage'],
            'payment_method'  => TypeConstants::TAMARA_TRANSACTION,
            'payment_channel' => $member['payment_channel'],
            'response_code'   => ['joining_date' => $member['joining_date']],
        ]);

        [, , $tamaraCurrency] = $this->getTamaraCredentials();

        $verifyUrl  = route('sw.tamara-mobile.verify', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $cancelUrl  = route('sw.tamara-mobile.cancel', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $failureUrl = route('sw.tamara-mobile.failure', $this->mobileContextParams(['invoice_id' => $uniqueId]));

        $tamara = new TamaraFrontController();
        $result = $tamara->createCheckoutSession([
            'amount'          => $totalAmount,
            'currency'        => $tamaraCurrency,
            'description'     => $subscription['name'] ?? '',
            'buyer'           => [
                'name'    => $member['name'],
                'phone'   => $member['phone'],
                'email'   => $member['email'] ?? '',
                'address' => $member['address'] ?? '',
                'city'    => env('TAMARA_CITY', 'Riyadh'),
            ],
            'order_reference'    => (string) $invoice->id,
            'success_url'        => $verifyUrl,
            'cancel_url'         => $cancelUrl,
            'failure_url'        => $failureUrl,
            'notification_url'   => route('tamara.webhook'),
            'payment_type'       => 'mobile_member_subscription',
            'member_id'          => optional($this->currentMember)->id,
            'subscription_id'    => $member['subscription_id'],
            'items'              => [[
                'title'        => $subscription['name'] ?? 'Subscription',
                'description'  => $subscription['content'] ?? '',
                'quantity'     => 1,
                'unit_price'   => $priceBeforeVat,
                'total_amount' => $totalAmount,
                'reference_id' => (string) $invoice->id,
            ]],
        ]);

        $errorRoute = route('sw.subscription-mobile', $this->mobileContextParams(['id' => $subscription['id']]));

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }

        $invoice->transaction_id = $result['order_id'];
        $invoice->response_code  = array_merge(
            (array) $invoice->response_code,
            ['tamara_checkout' => $result, 'joining_date' => $member['joining_date']]
        );
        $invoice->save();

        return $result['payment_url'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 3c — PayTabs: create checkout
    // ─────────────────────────────────────────────────────────────────────────

    protected function initiatePaytabs(array $subscription, array $member): string
    {
        $totalAmount = round($member['amount'], 2);
        $uniqueId    = uniqid();

        $invoice = GymOnlinePaymentInvoice::create([
            'payment_id'      => $uniqueId,
            'member_id'       => optional($this->currentMember)->id,
            'status'          => TypeConstants::PENDING,
            'subscription_id' => $member['subscription_id'],
            'name'            => $member['name'],
            'email'           => $member['email'] ?? '',
            'phone'           => $member['phone'],
            'dob'             => $member['dob'],
            'address'         => $member['address'],
            'gender'          => $member['gender'],
            'amount'          => $totalAmount,
            'vat'             => $member['vat'],
            'vat_percentage'  => $member['vat_percentage'],
            'payment_method'  => TypeConstants::PAYTABS_TRANSACTION,
            'payment_channel' => $member['payment_channel'],
            'response_code'   => ['joining_date' => $member['joining_date']],
        ]);

        $ptSettings = \Modules\Generic\Models\Setting::first();
        $ptCurrency = $ptSettings ? ($ptSettings->payments['paytabs']['currency'] ?? 'SAR') : 'SAR';

        $verifyUrl  = route('sw.paytabs-mobile.verify', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $cancelUrl  = route('sw.paytabs-mobile.cancel', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $failureUrl = route('sw.paytabs-mobile.failure', $this->mobileContextParams(['invoice_id' => $uniqueId]));

        $paytabs = new PayTabsFrontController();
        $result  = $paytabs->createCheckoutSession([
            'cart_id'         => $uniqueId,
            'amount'          => $totalAmount,
            'currency'        => $ptCurrency,
            'description'     => $subscription['name'] ?? 'Gym Subscription',
            'buyer'           => [
                'name'    => $member['name'],
                'phone'   => $member['phone'],
                'email'   => $member['email'] ?? '',
                'address' => $member['address'] ?? 'Riyadh',
                'city'    => env('PAYTABS_CITY', 'Riyadh'),
                'country' => env('PAYTABS_COUNTRY', 'SA'),
                'zip'     => '00000',
            ],
            'success_url'     => $verifyUrl,
            'cancel_url'      => $cancelUrl,
            'failure_url'     => $failureUrl,
            'callback_url'    => $verifyUrl,
            'payment_type'    => 'member_subscription',
            'member_id'       => optional($this->currentMember)->id,
            'subscription_id' => $member['subscription_id'],
        ]);

        $errorRoute = route('sw.subscription-mobile', $this->mobileContextParams(['id' => $subscription['id']]));

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }

        $invoice->transaction_id = $result['tran_ref'];
        $invoice->response_code  = array_merge(
            (array) $invoice->response_code,
            ['paytabs_checkout' => $result, 'joining_date' => $member['joining_date']]
        );
        $invoice->save();

        return $result['payment_url'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 4a — Tabby: verify & capture
    // ─────────────────────────────────────────────────────────────────────────

    public function tabbyVerify(Request $request)
    {
        $invoiceId    = $request->invoice_id;
        $tabbyPayId   = $request->payment_id; // Tabby sends this in the redirect

        $invoice = GymOnlinePaymentInvoice::with(['subscription' => fn($q) => $q->withTrashed()])
            ->where('payment_id', $invoiceId)->first();

        if (!$invoice) {
            return redirect()->route('sw.mobile-payment.error');
        }

        // Already processed
        if ($invoice->member_subscription_id) {
            $rcCheck = (array) $invoice->response_code;
            if (!empty($rcCheck['is_pt']))      $invoiceRoute = 'sw.pt-invoice-mobile';
            elseif (!empty($rcCheck['is_upgrade'])) $invoiceRoute = 'sw.upgrade-invoice-mobile';
            else                                $invoiceRoute = 'sw.invoice-mobile';
            return redirect()->route($invoiceRoute, ['id' => $invoice->member_subscription_id]);
        }

        if ($this->isGenericItemPayment($invoice) && (int) $invoice->status === TypeConstants::SUCCESS) {
            return $this->redirectToGenericItemPage($invoice, $request->token ?? null);
        }

        // Use transaction_id saved at checkout time if tabby didn't send payment_id in redirect
        $tabbyPaymentId = $tabbyPayId ?: $invoice->transaction_id;

        if (!$tabbyPaymentId) {
            Log::error('Tabby Mobile: no payment_id', compact('invoiceId'));
            return redirect()->route('sw.mobile-payment.error');
        }

        $tabby   = new TabbyFrontController();
        $payment = null;

        // Retry loop: Tabby may not have moved to AUTHORIZED immediately
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $payment = $tabby->getPaymentStatus($tabbyPaymentId);
            $status  = $payment['status'] ?? null;

            Log::info('Tabby Mobile verify attempt', ['attempt' => $attempt + 1, 'status' => $status]);

            if ($status && $status !== 'CREATED') {
                break;
            }
            if ($attempt < 4) {
                sleep(2);
            }
        }

        $status = $payment['status'] ?? null;

        if ($status === 'CREATED') {
            // Still pending — webhook will finalize
            \Session::flash('info', trans('front.payment_processing'));
            return redirect()->route('sw.subscription-mobile', [
                'id'    => $invoice->subscription_id,
                'token' => $request->token,
            ]);
        }

        if (!in_array($status, ['AUTHORIZED', 'CLOSED'])) {
            $invoice->status = TypeConstants::FAILURE;
            $invoice->save();
            return redirect()->route('sw.mobile-payment.error');
        }

        // Capture if not already closed
        if ($status === 'AUTHORIZED') {
            $capture = $tabby->capturePayment($tabbyPaymentId, (float) $invoice->amount);
            if (!$capture['success']) {
                $invoice->status = TypeConstants::FAILURE;
                $invoice->save();
                return redirect()->route('sw.mobile-payment.error');
            }
        }

        $invoice->status       = TypeConstants::SUCCESS;
        $invoice->transaction_id = $tabbyPaymentId;
        $invoice->response_code = array_merge(
            (array) $invoice->response_code,
            ['tabby_verify' => $payment]
        );
        $invoice->save();

        if ($this->isGenericItemPayment($invoice)) {
            return $this->redirectToGenericItemPage($invoice, $request->token ?? null);
        }

        $joiningDate = $invoice->response_code['joining_date'] ?? Carbon::now()->toDateString();
        $sub = $this->finalizeMobileCheckout($invoice, $joiningDate);

        if ($sub) {
            if ($sub->is_pt ?? false)         $finalRoute = 'sw.pt-invoice-mobile';
            elseif ($sub->is_upgrade ?? false) $finalRoute = 'sw.upgrade-invoice-mobile';
            else                              $finalRoute = 'sw.invoice-mobile';
            return redirect()->route($finalRoute, ['id' => $sub->id]);
        }

        return redirect()->route('sw.mobile-payment.error');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 4b — Tamara: verify, authorise & capture
    // ─────────────────────────────────────────────────────────────────────────

    public function tamaraVerify(Request $request)
    {
        $invoiceId = (string) (
            $request->input('invoice_id')
            ?? $request->input('invoiceId')
            ?? $request->input('order_reference_id')
            ?? $request->input('orderReferenceId')
            ?? ''
        );
        // Tamara callback params can vary in key naming/casing across environments.
        $paymentStatus = strtolower((string) (
            $request->input('paymentStatus')
            ?? $request->input('payment_status')
            ?? $request->input('order_status')
            ?? $request->input('status')
            ?? ''
        ));
        $orderId = (string) ($request->input('orderId') ?? $request->input('order_id') ?? '');

        $invoiceQuery = GymOnlinePaymentInvoice::with(['subscription' => fn($q) => $q->withTrashed()]);
        $invoice = null;

        if ($invoiceId !== '') {
            $invoice = (clone $invoiceQuery)
                ->where('payment_id', $invoiceId)
                ->orWhere('transaction_id', $invoiceId)
                ->orWhere('id', is_numeric($invoiceId) ? (int) $invoiceId : -1)
                ->orderByDesc('id')
                ->first();
        }

        if (!$invoice && $orderId !== '') {
            $invoice = (clone $invoiceQuery)
                ->where('transaction_id', $orderId)
                ->orWhere('payment_id', $orderId)
                ->orderByDesc('id')
                ->first();
        }

        if (!$invoice) {
            Log::warning('Tamara Mobile verify: invoice not found', [
                'invoice_id' => $invoiceId,
                'order_id' => $orderId,
                'params' => $request->all(),
            ]);
            return redirect()->route('sw.mobile-payment.error');
        }

        if ($invoice->member_subscription_id) {
            $rcCheck = (array) $invoice->response_code;
            if (!empty($rcCheck['is_pt']))      $invoiceRoute = 'sw.pt-invoice-mobile';
            elseif (!empty($rcCheck['is_upgrade'])) $invoiceRoute = 'sw.upgrade-invoice-mobile';
            else                                $invoiceRoute = 'sw.invoice-mobile';
            return redirect()->route($invoiceRoute, ['id' => $invoice->member_subscription_id]);
        }

        if ($this->isGenericItemPayment($invoice) && (int) $invoice->status === TypeConstants::SUCCESS) {
            return $this->redirectToGenericItemPage($invoice, $request->token ?? null);
        }

        $tamaraCheckout = (array) (((array) $invoice->response_code)['tamara_checkout'] ?? []);
        $tamaraOrderId = (string) ($invoice->transaction_id ?: $orderId ?: ($tamaraCheckout['order_id'] ?? ''));
        $joiningDate   = $invoice->response_code['joining_date'] ?? Carbon::now()->toDateString();

        if ($tamaraOrderId === '') {
            $invoice->status = TypeConstants::FAILURE;
            $invoice->response_code = array_merge((array) $invoice->response_code, ['tamara_verify' => $request->all()]);
            $invoice->save();
            return redirect()->route('sw.mobile-payment.error');
        }

        // Authorise order (source of truth). Do not fail only because redirect query param differs.
        $authorise       = $this->tamaraAuthoriseOrder($tamaraOrderId);
        $authoriseStatus = strtolower((string) ($authorise['status'] ?? ''));
        $autoCaptured    = (bool) ($authorise['auto_captured'] ?? false);

        Log::info('Tamara Mobile authorise', [
            'payment_status' => $paymentStatus,
            'status' => $authoriseStatus,
            'auto_captured' => $autoCaptured,
            'invoice_id' => $invoiceId,
            'order_id' => $tamaraOrderId,
        ]);

        $isAuthorised = in_array($authoriseStatus, ['authorised', 'authorized', 'fully_captured', 'partially_captured'], true);

        // If the authorise call returned an unexpected/empty status, fall back to checking
        // order status directly — the webhook may have already captured the order.
        if (!$isAuthorised && !$autoCaptured) {
            $orderStatus       = $this->tamaraGetOrderStatus($tamaraOrderId);
            $orderStatusStr    = strtolower((string) ($orderStatus['status'] ?? ''));
            $isAuthorised      = in_array($orderStatusStr, ['authorised', 'authorized', 'fully_captured', 'partially_captured', 'approved', 'captured'], true);

            Log::info('Tamara Mobile order status fallback', [
                'order_status' => $orderStatusStr,
                'is_authorised' => $isAuthorised,
                'order_id' => $tamaraOrderId,
            ]);

            if ($isAuthorised) {
                // Already captured; skip capture step below by treating as auto-captured.
                $autoCaptured    = true;
                $authoriseStatus = $orderStatusStr;
                $authorise       = $orderStatus;
            }
        }

        if (!$isAuthorised && !$autoCaptured) {
            $invoice->status = TypeConstants::FAILURE;
            $invoice->response_code = array_merge((array) $invoice->response_code, [
                'tamara_verify' => $request->all(),
                'tamara_authorise' => $authorise,
            ]);
            $invoice->save();
            return redirect()->route('sw.mobile-payment.error');
        }

        // Capture if authorised and not already auto-captured.
        if (in_array($authoriseStatus, ['authorised', 'authorized'], true) && !$autoCaptured) {
            $captureItemTitle = optional($invoice->subscription)->name
                ?: (string) (((array) $invoice->response_code)['subscription_name'] ?? 'Gym Subscription');

            $capture = $this->tamaraCapturePayment(
                $tamaraOrderId,
                number_format((float) $invoice->amount, 2, '.', ''),
                [['title' => $captureItemTitle, 'quantity' => 1,
                  'unit_price' => $invoice->amount, 'total_amount' => $invoice->amount,
                  'reference_id' => (string) $invoice->id]]
            );
            Log::info('Tamara Mobile capture', ['capture' => $capture]);

            $captureStatus = strtolower((string) ($capture['status'] ?? ''));
            $captureOk     = ($capture['capture_id'] ?? null)
                || in_array($captureStatus, ['fully_captured', 'partially_captured'], true);

            if (!$captureOk) {
                // Capture failed — check if order was already fully captured (e.g., concurrent request).
                $orderStatus    = $this->tamaraGetOrderStatus($tamaraOrderId);
                $orderStatusStr = strtolower((string) ($orderStatus['status'] ?? ''));
                $captureOk      = in_array($orderStatusStr, ['fully_captured', 'partially_captured', 'approved', 'captured'], true);

                Log::info('Tamara Mobile capture failed, checking order status', [
                    'order_status' => $orderStatusStr,
                    'capture_ok'   => $captureOk,
                    'order_id'     => $tamaraOrderId,
                ]);
            }

            if (!$captureOk) {
                $invoice->status = TypeConstants::FAILURE;
                $invoice->response_code = array_merge((array) $invoice->response_code, [
                    'tamara_verify'   => $request->all(),
                    'tamara_authorise' => $authorise,
                    'tamara_capture'  => $capture,
                ]);
                $invoice->save();
                return redirect()->route('sw.mobile-payment.error');
            }
        }

        $invoice->status = TypeConstants::SUCCESS;
        $invoice->transaction_id = $tamaraOrderId;
        $invoice->response_code = array_merge(
            (array) $invoice->response_code,
            [
                'tamara_verify' => $request->all(),
                'tamara_authorise' => $authorise,
            ]
        );
        $invoice->save();

        if ($this->isGenericItemPayment($invoice)) {
            return $this->redirectToGenericItemPage($invoice, $request->token ?? null);
        }

        $sub = $this->finalizeMobileCheckout($invoice, $joiningDate);

        if ($sub) {
            if ($sub->is_pt ?? false)         $finalRoute = 'sw.pt-invoice-mobile';
            elseif ($sub->is_upgrade ?? false) $finalRoute = 'sw.upgrade-invoice-mobile';
            else                              $finalRoute = 'sw.invoice-mobile';
            return redirect()->route($finalRoute, ['id' => $sub->id]);
        }

        return redirect()->route('sw.mobile-payment.error');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 4c — PayTabs: verify payment status
    // ─────────────────────────────────────────────────────────────────────────

    public function paytabsVerify(Request $request)
    {
        $invoiceId = $request->invoice_id;
        $cartId    = (string) ($request->input('cart_id') ?? $request->input('cartId') ?? '');
        $tranRef   = (string) ($request->input('tran_ref') ?? $request->input('tranRef') ?? '');

        $invoiceQuery = GymOnlinePaymentInvoice::with(['subscription' => fn($q) => $q->withTrashed()]);
        $invoice = null;

        if ($invoiceId !== null && $invoiceId !== '') {
            $invoice = (clone $invoiceQuery)
                ->where('payment_id', $invoiceId)
                ->orWhere('transaction_id', $invoiceId)
                ->orWhere('id', is_numeric($invoiceId) ? (int) $invoiceId : -1)
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$invoice && $cartId !== '') {
            $invoice = (clone $invoiceQuery)
                ->where('payment_id', $cartId)
                ->orWhere('transaction_id', $cartId)
                ->orWhere('id', is_numeric($cartId) ? (int) $cartId : -1)
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$invoice && $tranRef !== '') {
            $invoice = (clone $invoiceQuery)
                ->where('transaction_id', $tranRef)
                ->orWhere('payment_id', $tranRef)
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$invoice) {
            Log::warning('PayTabs Mobile verify: invoice not found', [
                'invoice_id' => $invoiceId,
                'cart_id'    => $cartId,
                'tran_ref'   => $tranRef,
                'params'     => $request->all(),
            ]);
            return redirect()->route('sw.mobile-payment.error');
        }

        if ($invoice->member_subscription_id) {
            $rcCheck = (array) $invoice->response_code;
            if (!empty($rcCheck['is_pt']))          $invoiceRoute = 'sw.pt-invoice-mobile';
            elseif (!empty($rcCheck['is_upgrade'])) $invoiceRoute = 'sw.upgrade-invoice-mobile';
            else                                    $invoiceRoute = 'sw.invoice-mobile';
            return redirect()->route($invoiceRoute, ['id' => $invoice->member_subscription_id]);
        }

        if ($this->isGenericItemPayment($invoice) && (int) $invoice->status === TypeConstants::SUCCESS) {
            return $this->redirectToGenericItemPage($invoice, $request->token ?? null);
        }

        $joiningDate = $invoice->response_code['joining_date'] ?? Carbon::now()->toDateString();
        $paytabsCheckout = (array) (((array) $invoice->response_code)['paytabs_checkout'] ?? []);
        $cartId = (string) ($cartId ?: ($paytabsCheckout['cart_id'] ?? ''));

        // PayTabs sends tran_ref in the callback — use it to fill transaction_id if missing.
        $tranRef = (string) ($invoice->transaction_id ?: $tranRef);
        if ($tranRef && !$invoice->transaction_id) {
            $invoice->transaction_id = $tranRef;
            $invoice->save();
        }

        // PayTabs sends payment_result directly in the redirect callback payload.
        // Use that as primary source; only hit queryTransaction API as fallback.
        $callbackPayload = $request->all();
        $responseStatus  = $callbackPayload['payment_result']['response_status']
            ?? $request->input('payment_result.response_status')
            ?? null;

        Log::info('PayTabs Mobile verify (callback)', [
            'invoice_id'      => $invoiceId,
            'tran_ref'        => $tranRef,
            'response_status' => $responseStatus,
            'payload_keys'    => array_keys($callbackPayload),
        ]);

        // If callback did NOT carry the status, query PayTabs API directly.
        if ($responseStatus === null && $tranRef) {
            $paytabs = new PayTabsFrontController();
            $payment = $paytabs->queryTransaction($tranRef);
            Log::info('PayTabs Mobile verify (query)', ['tran_ref' => $tranRef, 'response' => $payment]);
            $responseStatus = $payment['payment_result']['response_status'] ?? null;
            if ($payment) {
                $callbackPayload = array_merge($callbackPayload, $payment);
            }
        }

        if ($responseStatus === null) {
            Log::error('PayTabs Mobile verify: could not determine response_status', [
                'invoice_id' => $invoiceId, 'tran_ref' => $tranRef,
            ]);
            $invoice->status = TypeConstants::FAILURE;
            $invoice->response_code = array_merge((array) $invoice->response_code, ['paytabs_verify' => $callbackPayload]);
            $invoice->save();
            return redirect()->route('sw.mobile-payment.error');
        }

        Log::info('PayTabs Mobile verify status', ['tran_ref' => $tranRef, 'status' => $responseStatus]);

        if (strtoupper($responseStatus) !== 'A') {
            $invoice->status = TypeConstants::FAILURE;
            $invoice->response_code = array_merge((array) $invoice->response_code, ['paytabs_verify' => $callbackPayload]);
            $invoice->save();
            return redirect()->route('sw.mobile-payment.error');
        }

        $invoice->status = TypeConstants::SUCCESS;
        $invoice->response_code = array_merge(
            (array) $invoice->response_code,
            ['paytabs_verify' => $callbackPayload]
        );
        $invoice->save();

        if ($this->isGenericItemPayment($invoice)) {
            return $this->redirectToGenericItemPage($invoice, $request->token ?? null);
        }

        $sub = $this->finalizeMobileCheckout($invoice, $joiningDate);

        if ($sub) {
            if ($sub->is_pt ?? false)         $finalRoute = 'sw.pt-invoice-mobile';
            elseif ($sub->is_upgrade ?? false) $finalRoute = 'sw.upgrade-invoice-mobile';
            else                              $finalRoute = 'sw.invoice-mobile';
            return redirect()->route($finalRoute, ['id' => $sub->id]);
        }

        return redirect()->route('sw.mobile-payment.error');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 3d — Paymob: create checkout
    // ─────────────────────────────────────────────────────────────────────────

    protected function initiatePaymob(array $subscription, array $member): string
    {
        $totalAmount = round($member['amount'], 2);
        $uniqueId    = uniqid();

        $invoice = GymOnlinePaymentInvoice::create([
            'payment_id'      => $uniqueId,
            'member_id'       => optional($this->currentMember)->id,
            'status'          => TypeConstants::PENDING,
            'subscription_id' => $member['subscription_id'],
            'name'            => $member['name'],
            'email'           => $member['email'] ?? '',
            'phone'           => $member['phone'],
            'dob'             => $member['dob'],
            'address'         => $member['address'],
            'gender'          => $member['gender'],
            'amount'          => $totalAmount,
            'vat'             => $member['vat'],
            'vat_percentage'  => $member['vat_percentage'],
            'payment_method'  => TypeConstants::PAYMOB_TRANSACTION,
            'payment_channel' => $member['payment_channel'],
            'response_code'   => ['joining_date' => $member['joining_date']],
        ]);

        $errorRoute = route('sw.subscription-mobile', $this->mobileContextParams(['id' => $subscription['id']]));

        // Build billing data from member info
        $nameParts   = explode(' ', $member['name'], 2);
        $billingData = [
            'first_name'      => $nameParts[0] ?? 'Gym',
            'last_name'       => $nameParts[1] ?? 'Member',
            'email'           => $member['email'] ?? 'member@gym.com',
            'phone_number'    => $member['phone'] ?? '01000000000',
            'apartment'       => 'NA',
            'floor'           => 'NA',
            'street'          => $member['address'] ?? 'NA',
            'building'        => 'NA',
            'shipping_method' => 'NA',
            'postal_code'     => 'NA',
            'city'            => 'NA',
            'country'         => 'EG',
            'state'           => 'NA',
        ];

        // Paymob verify URL — Paymob appends its callback params to this URL
        $verifyUrl = route('sw.paymob-mobile.verify', ['invoice_id' => $uniqueId]);

        $paymob   = new PaymobFrontController();
        $iframeUrl = $paymob->payment([
            'name'         => $subscription['name'] ?? 'Gym Subscription',
            'price'        => $totalAmount,
            'desc'         => $subscription['name'] ?? 'Gym Subscription',
            'qty'          => 1,
            'no_fee'       => true,
            'billing_data' => $billingData,
            'redirect_url' => $verifyUrl,
        ]);

        if (!$iframeUrl) {
            Log::error('Paymob Mobile: failed to get iframe URL', ['invoice_id' => $uniqueId]);
            \Session::flash('error', trans('front.error_in_data'));
            $invoice->status = TypeConstants::FAILURE;
            $invoice->save();
            return $errorRoute;
        }

        return $iframeUrl;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 4d — Paymob: verify payment callback
    // ─────────────────────────────────────────────────────────────────────────

    public function paymobVerify(Request $request)
    {
        $invoiceId     = $request->invoice_id;
        $success       = $request->input('success');
        $transactionId = $request->input('id');

        $invoice = GymOnlinePaymentInvoice::with(['subscription' => fn($q) => $q->withTrashed()])
            ->where('payment_id', $invoiceId)->first();

        if (!$invoice) {
            return redirect()->route('sw.mobile-payment.error');
        }

        // Already processed
        if ($invoice->member_subscription_id) {
            $rcCheck = (array) $invoice->response_code;
            if (!empty($rcCheck['is_pt']))      $invoiceRoute = 'sw.pt-invoice-mobile';
            elseif (!empty($rcCheck['is_upgrade'])) $invoiceRoute = 'sw.upgrade-invoice-mobile';
            else                                $invoiceRoute = 'sw.invoice-mobile';
            return redirect()->route($invoiceRoute, ['id' => $invoice->member_subscription_id]);
        }

        if ($this->isGenericItemPayment($invoice) && (int) $invoice->status === TypeConstants::SUCCESS) {
            return $this->redirectToGenericItemPage($invoice, $request->token ?? null);
        }

        // Verify HMAC if secret is configured
        $pmSettings  = \Modules\Generic\Models\Setting::first();
        $hmacSecret  = $pmSettings ? ($pmSettings->payments['paymob']['hmac_secret'] ?? '') : '';

        if ($hmacSecret && !$this->verifyPaymobHmac($request, $hmacSecret)) {
            Log::error('Paymob Mobile: HMAC verification failed', ['invoice_id' => $invoiceId]);
            $invoice->status = TypeConstants::FAILURE;
            $invoice->save();
            return redirect()->route('sw.mobile-payment.error');
        }

        $joiningDate = $invoice->response_code['joining_date'] ?? Carbon::now()->toDateString();

        if ($success !== 'true' && $success !== true) {
            Log::warning('Paymob Mobile: payment not successful', ['invoice_id' => $invoiceId, 'success' => $success]);
            $invoice->status = TypeConstants::FAILURE;
            $invoice->response_code = array_merge((array) $invoice->response_code, ['paymob_verify' => $request->all()]);
            $invoice->save();
            return redirect()->route('sw.mobile-payment.error');
        }

        $invoice->status       = TypeConstants::SUCCESS;
        $invoice->transaction_id = $transactionId;
        $invoice->response_code  = array_merge(
            (array) $invoice->response_code,
            ['paymob_verify' => $request->all()]
        );
        $invoice->save();

        if ($this->isGenericItemPayment($invoice)) {
            return $this->redirectToGenericItemPage($invoice, $request->token ?? null);
        }

        Log::info('Paymob Mobile verify success', ['invoice_id' => $invoiceId, 'transaction_id' => $transactionId]);

        $sub = $this->finalizeMobileCheckout($invoice, $joiningDate);

        if ($sub) {
            if ($sub->is_pt ?? false)         $finalRoute = 'sw.pt-invoice-mobile';
            elseif ($sub->is_upgrade ?? false) $finalRoute = 'sw.upgrade-invoice-mobile';
            else                              $finalRoute = 'sw.invoice-mobile';
            return redirect()->route($finalRoute, ['id' => $sub->id]);
        }

        return redirect()->route('sw.mobile-payment.error');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 5 — Show invoice (post-payment success page)
    // ─────────────────────────────────────────────────────────────────────────

    public function invoiceMobile($id)
    {
        $invoice = GymMemberSubscription::with(['subscription', 'member'])->where('id', $id)->first();

        if (!$invoice) {
            return abort(404);
        }

        $title        = trans('front.invoice');
        $mainSettings = $this->mainSettings;
        $qr_img_invoice = $invoice->qr_code ?? null;

        return view('software::Front.invoice_mobile', compact('title', 'invoice', 'mainSettings', 'qr_img_invoice'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Error / Cancel pages
    // ─────────────────────────────────────────────────────────────────────────

    public function paymentError()
    {
        $title = trans('front.payment_error_title');
        return view('software::Front.payment_error_mobile', compact('title'));
    }

    public function tabbyCancel(Request $request)
    {
        $this->markInvoiceFailed($request->invoice_id);
        \Session::flash('error', trans('front.tabby_error_cancel_body_msg'));
        return $this->redirectToSubscriptionOrError($request->invoice_id, $request->token ?? null);
    }

    public function tabbyFailure(Request $request)
    {
        $this->markInvoiceFailed($request->invoice_id);
        \Session::flash('error', trans('front.tabby_error_failure_body_msg'));
        return $this->redirectToSubscriptionOrError($request->invoice_id, $request->token ?? null);
    }

    public function tamaraCancel(Request $request)
    {
        $this->markInvoiceFailed($request->invoice_id);
        \Session::flash('error', trans('front.error_in_data'));
        return $this->redirectToSubscriptionOrError($request->invoice_id, $request->token ?? null);
    }

    public function tamaraFailure(Request $request)
    {
        $this->markInvoiceFailed($request->invoice_id);
        \Session::flash('error', trans('front.error_in_data'));
        return $this->redirectToSubscriptionOrError($request->invoice_id, $request->token ?? null);
    }

    public function paytabsCancel(Request $request)
    {
        $this->markInvoiceFailed($request->invoice_id);
        \Session::flash('error', trans('front.error_in_data'));
        return $this->redirectToSubscriptionOrError($request->invoice_id, $request->token ?? null);
    }

    public function paytabsFailure(Request $request)
    {
        $this->markInvoiceFailed($request->invoice_id);
        \Session::flash('error', trans('front.error_in_data'));
        return $this->redirectToSubscriptionOrError($request->invoice_id, $request->token ?? null);
    }

    public function paymobCancel(Request $request)
    {
        $this->markInvoiceFailed($request->invoice_id);
        \Session::flash('error', trans('front.paymob_error_cancel_body_msg'));
        return $this->redirectToSubscriptionOrError($request->invoice_id, $request->token ?? null);
    }

    public function paymobFailure(Request $request)
    {
        $this->markInvoiceFailed($request->invoice_id);
        \Session::flash('error', trans('front.paymob_error_failure_body_msg'));
        return $this->redirectToSubscriptionOrError($request->invoice_id, $request->token ?? null);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Core finalization — creates member + subscription + money-box entry
    // ─────────────────────────────────────────────────────────────────────────

    protected function finalizeMobileCheckout(GymOnlinePaymentInvoice $invoice, string $joiningDate): ?GymMemberSubscription
    {
        $rc = (array) $invoice->response_code;
        $notificationPayload = null;

        // Delegate to upgrade finalizer when flagged
        if (!empty($rc['is_upgrade'])) {
            return $this->finalizeUpgradeMobileCheckout($invoice, $joiningDate);
        }

        // Delegate to PT finalizer when flagged
        if (!empty($rc['is_pt'])) {
            $ptMemberId = $this->finalizePtMobileCheckout($invoice, $joiningDate);
            if ($ptMemberId) {
                $dummy = new GymMemberSubscription();
                $dummy->id    = $ptMemberId; // bypass $guarded to set PK
                $dummy->is_pt = true;
                return $dummy;
            }
            return null;
        }

        $subscription = $invoice->subscription
            ?? GymSubscription::withTrashed()->find($invoice->subscription_id);

        if (!$subscription) {
            Log::error('Mobile Finalize: subscription not found', ['invoice_id' => $invoice->id]);
            return null;
        }

        $lockKey = 'mobile_finalize_' . $invoice->id;
        DB::selectOne('SELECT GET_LOCK(?, 30) AS locked', [$lockKey]);

        try {
            $memberSub = DB::transaction(function () use ($invoice, $joiningDate, $subscription, &$notificationPayload) {
                // Re-read with exclusive row lock
                $invoice = GymOnlinePaymentInvoice::where('id', $invoice->id)->lockForUpdate()->first();

                // Idempotency
                if ($invoice->member_subscription_id) {
                    return GymMemberSubscription::find($invoice->member_subscription_id);
                }

                // ── Resolve or create member ───────────────────────────────
                $member        = null;
                $typeOfPayment = TypeConstants::RenewMember;
                $isNewMember   = false;

                if ($invoice->member_id) {
                    $member = GymMember::find($invoice->member_id);
                }
                if (!$member && $invoice->phone) {
                    $member = GymMember::where('phone', $invoice->phone)->first();
                }
                if (!$member && $invoice->email) {
                    $member = GymMember::where('email', $invoice->email)->first();
                }
                if (!$member) {
                    $maxCode = str_pad(((int) GymMember::withTrashed()->max('code') + 1), 14, '0', STR_PAD_LEFT);
                    $member  = GymMember::create([
                        'code'    => $maxCode,
                        'name'    => $invoice->name,
                        'gender'  => $invoice->gender,
                        'phone'   => $invoice->phone,
                        'address' => $invoice->address,
                        'dob'     => $invoice->dob,
                    ]);
                    $typeOfPayment = TypeConstants::CreateMember;
                    $isNewMember = true;
                }

                // ── Create member subscription ─────────────────────────────
                $joining    = Carbon::parse($joiningDate);
                $periodDays = (int) ($subscription->period ?? 0);
                $expire     = (clone $joining)->addDays(max($periodDays, 0));

                $memberSub = GymMemberSubscription::create([
                    'subscription_id'        => $invoice->subscription_id,
                    'member_id'              => $member->id,
                    'workouts'               => $subscription->workouts ?? 0,
                    'amount_paid'            => $invoice->amount,
                    'vat'                    => $invoice->vat,
                    'vat_percentage'         => $invoice->vat_percentage,
                    'joining_date'           => $joining->toDateTimeString(),
                    'expire_date'            => $expire->toDateTimeString(),
                    'status'                 => TypeConstants::Active,
                    'freeze_limit'           => $subscription->freeze_limit ?? 0,
                    'number_times_freeze'    => $subscription->number_times_freeze ?? 0,
                    'amount_before_discount' => $subscription->price ?? 0,
                    'discount_value'         => $this->calculateDiscountValue($subscription),
                    'discount_type'          => $this->getDiscountType($subscription),
                    'payment_type'           => $this->resolveGatewayPaymentTypeId((int) ($invoice->payment_method ?? TypeConstants::ONLINE_PAYMENT)),
                ]);

                // ── Update invoice ─────────────────────────────────────────
                $invoice->status                 = TypeConstants::SUCCESS;
                $invoice->member_subscription_id = $memberSub->id;
                $invoice->invoice_type           = 'subscription';
                $invoice->save();

                // ── MoneyBox entry ─────────────────────────────────────────
                $this->createMoneyBoxEntry($invoice, $member, $typeOfPayment, $memberSub->id);

                // ── ZATCA Billing Invoice entry ─────────────────────────────
                $totalAmount     = (float) ($invoice->amount ?? 0);
                $vatAmount       = (float) ($invoice->vat ?? 0);
                if ($vatAmount > 0) {
                    $amountBeforeVat = round($totalAmount - $vatAmount, 2);
                } elseif ((float) ($invoice->vat_percentage ?? 0) > 0) {
                    $amountBeforeVat = round($totalAmount / (1 + $invoice->vat_percentage / 100), 2);
                    $vatAmount       = round($totalAmount - $amountBeforeVat, 2);
                } else {
                    $amountBeforeVat = $totalAmount;
                }
                try {
                    SwBillingService::createInvoiceFromMember(
                        $member,
                        $memberSub->id,
                        $amountBeforeVat,
                        $vatAmount
                    );
                } catch (\Throwable $e) {
                    Log::warning('ZATCA billing invoice creation failed', [
                        'member_id' => $member->id,
                        'member_subscription_id' => $memberSub->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $notificationPayload = [
                    'event_code' => $isNewMember ? 'new_member' : 'renew_member',
                    'membership' => $memberSub->loadMissing(['member', 'subscription']),
                    'phone' => $member->phone ?? null,
                    'branch_setting_id' => $this->resolveBranchSettingId($member),
                ];

                return $memberSub;
            });

            if ($memberSub && $notificationPayload) {
                $this->sendMembershipEventNotification(
                    $notificationPayload['event_code'],
                    $notificationPayload['membership'],
                    $notificationPayload['phone'],
                    $notificationPayload['branch_setting_id']
                );
            }

            return $memberSub;
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?)', [$lockKey]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tamara API helpers (authorise + capture — not in TamaraFrontController)
    // ─────────────────────────────────────────────────────────────────────────

    protected function tamaraAuthoriseOrder(string $orderId): array
    {
        [$token, $baseUrl] = array_slice($this->getTamaraCredentials(), 0, 2);

        try {
            $response = Http::withoutVerifying()
                ->withToken($token)
                ->post("{$baseUrl}/orders/{$orderId}/authorise");
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Tamara Mobile authorise error: ' . $e->getMessage());
            return [];
        }
    }

    protected function tamaraGetOrderStatus(string $orderId): array
    {
        [$token, $baseUrl] = array_slice($this->getTamaraCredentials(), 0, 2);

        try {
            $response = Http::withoutVerifying()
                ->withToken($token)
                ->get("{$baseUrl}/orders/{$orderId}");
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Tamara Mobile getOrderStatus error: ' . $e->getMessage());
            return [];
        }
    }

    protected function tamaraCapturePayment(string $orderId, string $amount, array $items = []): array
    {
        [$token, $baseUrl, $currency] = $this->getTamaraCredentials();

        $amountStr = number_format((float) $amount, 2, '.', '');

        $captureItems = [];
        foreach ($items as $item) {
            $captureItems[] = [
                'reference_id' => (string) ($item['reference_id'] ?? ''),
                'type'         => 'Digital',
                'name'         => $item['title'] ?? '',
                'sku'          => (string) ($item['reference_id'] ?? ''),
                'quantity'     => (int) ($item['quantity'] ?? 1),
                'unit_price'   => ['amount' => number_format((float) ($item['unit_price'] ?? $amount), 2, '.', ''), 'currency' => $currency],
                'total_amount' => ['amount' => number_format((float) ($item['total_amount'] ?? $amount), 2, '.', ''), 'currency' => $currency],
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->withToken($token)
                ->post("{$baseUrl}/payments/capture", [
                    'order_id'        => $orderId,
                    'total_amount'    => ['amount' => $amountStr, 'currency' => $currency],
                    'shipping_info'   => ['shipped_at' => now()->toISOString(), 'shipping_company' => 'Digital'],
                    'items'           => $captureItems,
                    'shipping_amount' => ['amount' => '0.00', 'currency' => $currency],
                    'tax_amount'      => ['amount' => '0.00', 'currency' => $currency],
                ]);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Tamara Mobile capture error: ' . $e->getMessage());
            return [];
        }
    }

    protected function getTamaraCredentials(): array
    {
        $settings     = \Modules\Generic\Models\Setting::first();
        $tamara       = $settings ? ($settings->payments['tamara'] ?? []) : [];
        $token        = $tamara['token']    ?? '';
        $currency     = $tamara['currency'] ?? 'SAR';
        $isProduction = !((bool) ($tamara['is_test'] ?? true));
        $baseUrl      = $isProduction ? 'https://api.tamara.co' : 'https://api-sandbox.tamara.co';
        return [$token, $baseUrl, $currency];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    protected function resolveMemberFromRequest(Request $request, bool $allowTokenFallback = true): ?GymMember
    {
        /* note: remove this line after relase new version of mobile app with payment-link token support, to prevent fallback to token when member_id is absent in payment links. */
        $allowTokenFallback = true;
        $rawToken = @$request->input('token');
        $request->merge(['payment_link_token' => $rawToken]);
        /* end note */

        // 2) Primary mobile-web resolver: member_id coming from payment links.
        $requestedMemberId = (int) ($request->input('member_id') ?: 0);
        $requestedMember = $requestedMemberId > 0 ? GymMember::find($requestedMemberId) : null;
        if ($requestedMember) {
            return $requestedMember;
        }

        // 3) For payment-link strict mode, do not fallback to token.
        if (!$allowTokenFallback) {
            return null;
        }

        // 4) Backward compatibility only: resolve from token if member_id is absent.
        $rawToken = $request->input('payment_link_token')
            ?: $request->input('token')
            ?: $request->bearerToken();
        if (!$rawToken) {
            return null;
        }

        $rawToken = trim((string) preg_replace('/^Bearer\s+/i', '', (string) $rawToken));
        if ($rawToken === '') {
            return $requestedMember;
        }

        // Build robust token candidates (url-encoded / plus-space variations).
        $decoded = urldecode($rawToken);
        $tokenCandidates = array_values(array_unique(array_filter([
            $rawToken,
            $decoded,
            str_replace(' ', '+', $rawToken),
            str_replace(' ', '+', $decoded),
        ])));

        // 5) Legacy flow: map push token -> member.
        $pushToken = DB::table('sw_gym_push_tokens')
            ->whereIn('token', $tokenCandidates)
            ->orderByDesc('id')
            ->first();

        if ($pushToken && $pushToken->member_id) {
            $member = GymMember::find($pushToken->member_id);
            if ($member) {
                return $member;
            }
        }

        // 6) Legacy flow fallback: token may be app API token (hashed/plain in api_token).
        foreach ($tokenCandidates as $plainToken) {
            $member = GymMember::where('api_token', hash('sha256', $plainToken))
                ->orWhere('api_token', $plainToken)
                ->first();
            if ($member) {
                return $member;
            }
        }

        // 1) If guard already resolved the member from Authorization header, use it directly.
        $guardMember = $request->user('api') ?: \Auth::guard('api')->user();
        if ($guardMember instanceof GymMember) {
            return $guardMember;
        }

        return null;
    }

    protected function resolveAssignedTrainingPlan(GymTrainingMember $assignment): ?GymTrainingPlan
    {
        $assignmentType = (int) ($assignment->type ?? 0);

        if ($assignmentType === TypeConstants::DIET_PLAN_TYPE && $assignment->diet_plan) {
            return $assignment->diet_plan;
        }

        if ($assignment->training_plan) {
            return $assignment->training_plan;
        }

        if ($assignment->diet_plan) {
            return $assignment->diet_plan;
        }

        return null;
    }

    protected function markInvoiceFailed(?string $paymentId): void
    {
        if (!$paymentId) return;
        $inv = GymOnlinePaymentInvoice::where('payment_id', $paymentId)->first();
        if ($inv && $inv->status !== TypeConstants::SUCCESS) {
            $inv->status = TypeConstants::FAILURE;
            $inv->save();
        }
    }

    protected function redirectToSubscriptionOrError(?string $paymentId, ?string $token)
    {
        if ($paymentId) {
            $inv = GymOnlinePaymentInvoice::where('payment_id', $paymentId)->first();
            if ($inv) {
                $rc = (array) $inv->response_code;
                if (!empty($rc['is_activity'])) {
                    return redirect()->route('sw.activity-mobile', [
                        'id' => (int) ($rc['activity_id'] ?? 0),
                        'token' => $token,
                    ]);
                }
                if (!empty($rc['is_store'])) {
                    return redirect()->route('sw.store-mobile', [
                        'id' => (int) ($rc['store_id'] ?? 0),
                        'token' => $token,
                    ]);
                }
                $params = ['id' => $inv->subscription_id];
                if ($token) $params['token'] = $token;
                return redirect()->route('sw.subscription-mobile', $params);
            }
        }
        return redirect()->route('sw.mobile-payment.error');
    }

    protected function isGenericItemPayment(GymOnlinePaymentInvoice $invoice): bool
    {
        $rc = (array) $invoice->response_code;
        return !empty($rc['is_activity']) || !empty($rc['is_store']);
    }

    protected function redirectToGenericItemPage(GymOnlinePaymentInvoice $invoice, ?string $token)
    {
        if ((int) $invoice->status === TypeConstants::SUCCESS) {
            $this->finalizeGenericMobileCheckout($invoice);
            $invoice->refresh();
        }

        $rc = (array) $invoice->response_code;
        $token = $token ?: ($rc['token'] ?? null);
        if (!empty($rc['is_activity'])) {
            return redirect()->route('sw.activity-invoice-mobile', [
                'invoice_id' => $invoice->payment_id,
                'token'      => $token,
            ]);
        }
        if (!empty($rc['is_store'])) {
            return redirect()->route('sw.store-order-invoice-mobile', [
                'invoice_id' => $invoice->payment_id,
                'token'      => $token,
            ]);
        }

        return redirect()->route('sw.mobile-payment.error');
    }

    protected function finalizeGenericMobileCheckout(GymOnlinePaymentInvoice $invoice): void
    {
        $rc = (array) $invoice->response_code;
        if (!empty($rc['generic_finalized'])) {
            return;
        }

        $lockKey = 'generic_mobile_finalize_' . $invoice->id;
        DB::selectOne('SELECT GET_LOCK(?, 30) AS locked', [$lockKey]);

        try {
            DB::transaction(function () use ($invoice) {
                $lockedInvoice = GymOnlinePaymentInvoice::where('id', $invoice->id)->lockForUpdate()->first();
                if (!$lockedInvoice || (int) $lockedInvoice->status !== TypeConstants::SUCCESS) {
                    return;
                }

                $rc = (array) $lockedInvoice->response_code;
                if (!empty($rc['generic_finalized'])) {
                    return;
                }

                $result = [];
                if (!empty($rc['is_activity'])) {
                    $result = $this->finalizeActivityMobileCheckout($lockedInvoice, $rc);
                    $lockedInvoice->invoice_type           = 'activity';
                    $lockedInvoice->member_subscription_id = $result['non_member_id'] ?? null;
                } elseif (!empty($rc['is_store'])) {
                    $result = $this->finalizeStoreMobileCheckout($lockedInvoice, $rc);
                    $lockedInvoice->invoice_type           = 'store';
                    $lockedInvoice->member_subscription_id = $result['store_order_id'] ?? null;
                }

                $lockedInvoice->response_code = array_merge($rc, $result, ['generic_finalized' => true]);
                $lockedInvoice->save();
            });
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?)', [$lockKey]);
        }
    }

    protected function finalizeActivityMobileCheckout(GymOnlinePaymentInvoice $invoice, array $rc): array
    {
        if (!empty($rc['non_member_id'])) {
            return ['non_member_id' => (int) $rc['non_member_id']];
        }

        // Support: new format (array of objects), old format (array of IDs), or single activity_id
        $rawActivityData = $rc['activity_ids'] ?? [(int) ($rc['activity_id'] ?? 0)];
        $rawActivityData = (array) $rawActivityData;
        // Detect whether items are full objects or plain IDs
        if (!empty($rawActivityData) && is_array($rawActivityData[0])) {
            // New format: full activity objects stored directly — use them, no DB query needed
            $activities = collect($rawActivityData)->map(fn($a) => (object) $a);
        } else {
            $activityIds = array_values(array_filter(array_map('intval', $rawActivityData)));
            $activities  = !empty($activityIds) ? GymActivity::whereIn('id', $activityIds)->get() : collect();
        }
        if ($activities->isEmpty()) {
            return [];
        }

        $member = null;
        if ($invoice->member_id) {
            $member = GymMember::find((int) $invoice->member_id);
        }
        if (!$member && !empty($invoice->phone)) {
            $member = GymMember::where('phone', $invoice->phone)->first();
        }

        $branchSettingId = $this->resolveBranchSettingId($member);
        $userId = null;

        $activitiesPayload = $activities->map(fn($a) => [
            'id'                   => (int) ($a->id ?? 0),
            'name_ar'              => (string) ($a->name_ar ?? ''),
            'name_en'              => (string) ($a->name_en ?? ''),
            'price'                => (string) ($a->price ?? '0'),
            'reservation_limit'    => (string) ($a->reservation_limit ?? '0'),
            'reservation_duration' => isset($a->reservation_duration) && $a->reservation_duration ? (string) $a->reservation_duration : null,
            'reservation_period'   => isset($a->reservation_period)   && $a->reservation_period   ? (string) $a->reservation_period   : null,
            'name'                 => (string) ($a->name ?? $a->name_ar ?? ''),
            'content'              => $a->content ?? null,
            'image_name'           => $a->image_name ?? null,
        ])->values()->all();

        $totalBaseAmount = (float) $activities->sum('price');

        $nonMemberData = [
            'name'                   => (string) ($invoice->name ?? trans('sw.guest')),
            'phone'                  => (string) ($invoice->phone ?? ''),
            'activities'             => $activitiesPayload,
            'price'                  => (float) $invoice->amount,
            'vat'                    => (float) ($invoice->vat ?? 0),
            'amount_paid'            => (float) $invoice->amount,
            'amount_remaining'       => 0,
            'amount_before_discount' => $totalBaseAmount,
            'discount_value'         => 0,
            'discount_type'          => 0,
            'payment_type'           => $this->resolveGatewayPaymentTypeId((int) ($invoice->payment_method ?? TypeConstants::ONLINE_PAYMENT)),
            'branch_setting_id'      => $branchSettingId,
        ];

        $nonMemberColumns = Schema::getColumnListing('sw_gym_non_members');
        $nonMemberInsert = array_intersect_key($nonMemberData, array_flip($nonMemberColumns));
        $nonMember = GymNonMember::create($nonMemberInsert);

        $activityNames = $activities->map(fn($a) => (string) ($a->{'name_' . $this->lang} ?? $a->name_en ?? $a->name_ar ?? $a->name ?? trans('sw.activity')))->implode(', ');
        $notes = str_replace(':activities', $activityNames, trans('sw.non_member_moneybox_add_msg'));
        $notes = str_replace(':member', (string) ($invoice->name ?? trans('sw.guest')), $notes);
        $notes = str_replace(':amount_paid', round((float) $invoice->amount, 2), $notes);
        $notes = str_replace(':amount_remaining', 0, $notes);
        if (!empty($invoice->vat_percentage)) {
            $notes .= ' - ' . trans('sw.vat_added');
        }

        $this->createGenericMoneyBoxEntry([
            'member_id' => $member?->id,
            'amount' => (float) $invoice->amount,
            'vat' => (float) ($invoice->vat ?? 0),
            'notes' => $notes,
            'type' => TypeConstants::CreateNonMember,
            'payment_type' => $this->resolveGatewayPaymentTypeId((int) ($invoice->payment_method ?? TypeConstants::ONLINE_PAYMENT)),
            'branch_setting_id' => $branchSettingId,
            'user_id' => null,
            'non_member_subscription_id' => (int) $nonMember->id,
        ]);

        $this->createUserLogEntry($notes, TypeConstants::CreateMoneyBoxAdd, null, $branchSettingId);
        $memberNotes = str_replace(':name', (string) ($invoice->name ?? trans('sw.guest')), trans('sw.add_non_member'));
        $this->createUserLogEntry($memberNotes, TypeConstants::CreateNonMember, null, $branchSettingId);

        return ['non_member_id' => (int) $nonMember->id];
    }

    protected function finalizeStoreMobileCheckout(GymOnlinePaymentInvoice $invoice, array $rc): array
    {
        if (!empty($rc['store_order_id'])) {
            return ['store_order_id' => (int) $rc['store_order_id']];
        }

        // Support both single store_id and multi store_product_items
        $storeItems = $rc['store_product_items'] ?? [['id' => (int) ($rc['store_id'] ?? 0), 'qty' => 1]];
        $productIds = array_values(array_filter(array_column((array) $storeItems, 'id')));
        $allProducts = !empty($productIds) ? GymStoreProduct::whereIn('id', $productIds)->get()->keyBy('id') : collect();
        if ($allProducts->isEmpty()) {
            return [];
        }

        $member = null;
        if ($invoice->member_id) {
            $member = GymMember::find((int) $invoice->member_id);
        }
        if (!$member && !empty($invoice->phone)) {
            $member = GymMember::where('phone', $invoice->phone)->first();
        }

        $branchSettingId = $this->resolveBranchSettingId($member);
        $userId = null;

        $productsJson = [];
        $totalBaseAmount = 0;
        foreach ($storeItems as $item) {
            $p = $allProducts->get((int) $item['id']);
            if (!$p) continue;
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $productsJson[] = ['id' => (int) $p->id, 'quantity' => $qty, 'price' => (float) $p->price];
            $totalBaseAmount += (float) $p->price * $qty;
        }

        $orderData = [
            'member_id'              => $member?->id,
            'user_id'                => $userId,
            'products'               => $productsJson,
            'amount_paid'            => (float) $invoice->amount,
            'amount_remaining'       => 0,
            'amount_before_discount' => $totalBaseAmount,
            'discount_value'         => 0,
            'discount_type'          => 0,
            'vat'                    => (float) ($invoice->vat ?? 0),
            'payment_type'           => $this->resolveGatewayPaymentTypeId((int) ($invoice->payment_method ?? TypeConstants::ONLINE_PAYMENT)),
            'payment_status'         => 'paid',
            'total_amount'           => (float) $invoice->amount,
            'branch_setting_id'      => $branchSettingId,
        ];

        $orderColumns = Schema::getColumnListing('sw_gym_store_orders');
        $orderInsert = array_intersect_key($orderData, array_flip($orderColumns));
        $order = GymStoreOrder::create($orderInsert);

        foreach ($storeItems as $item) {
            $p = $allProducts->get((int) $item['id']);
            if (!$p) continue;
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $orderProductData = [
                'order_id'          => (int) $order->id,
                'product_id'        => (int) $p->id,
                'quantity'          => $qty,
                'price'             => (float) $p->price,
                'branch_setting_id' => $branchSettingId,
            ];
            $orderProductColumns = Schema::getColumnListing('sw_gym_store_order_product');
            GymStoreOrderProduct::create(array_intersect_key($orderProductData, array_flip($orderProductColumns)));

            if (Schema::hasColumn('sw_gym_store_products', 'quantity')) {
                GymStoreProduct::where('id', $p->id)->decrement('quantity', $qty);
            }
        }

        $productNames = collect($storeItems)
            ->map(fn($item) => optional($allProducts->get((int) $item['id']))->name)
            ->filter()->implode(', ');
        $notes = trans('sw.add_store_order', [
            'price'    => round((float) $invoice->amount, 2),
            'username' => $member?->name ?? trans('sw.guest'),
        ]);
        if (!empty($invoice->vat_percentage)) {
            $notes .= ' - ' . trans('sw.vat_added');
        }

        $this->createGenericMoneyBoxEntry([
            'member_id'        => $member?->id,
            'amount'           => (float) $invoice->amount,
            'vat'              => (float) ($invoice->vat ?? 0),
            'notes'            => $notes,
            'type'             => TypeConstants::CashSale,
            'payment_type'     => $this->resolveGatewayPaymentTypeId((int) ($invoice->payment_method ?? TypeConstants::ONLINE_PAYMENT)),
            'branch_setting_id'=> $branchSettingId,
            'user_id'          => null,
            'store_order_id'   => (int) $order->id,
            'is_store_balance' => 0,
        ]);

        $this->createUserLogEntry($notes, TypeConstants::CreateStoreOrder, null, $branchSettingId);

        return ['store_order_id' => (int) $order->id, 'store_product_items' => $storeItems];
    }

    protected function createGenericMoneyBoxEntry(array $data): void
    {
        $branchSettingId = (int) ($data['branch_setting_id'] ?? 1);
        $moneyBoxQuery = GymMoneyBox::query();
        if (Schema::hasColumn('sw_gym_money_boxes', 'branch_setting_id')) {
            $moneyBoxQuery->where('branch_setting_id', $branchSettingId);
        }

        $lastBox = $moneyBoxQuery->orderByDesc('id')->first();
        $amountBefore = $lastBox ? (float) $lastBox->amount_before : 0;
        $operation = $lastBox ? (int) $lastBox->operation : TypeConstants::Add;
        $amountAfter = $this->computeAmountAfter((float) ($data['amount'] ?? 0), $amountBefore, $operation);

        $moneyBoxData = [
            'user_id' => $data['user_id'] ?? null,
            'member_id' => $data['member_id'] ?? null,
            'amount' => (float) ($data['amount'] ?? 0),
            'vat' => (float) ($data['vat'] ?? 0),
            'operation' => TypeConstants::Add,
            'amount_before' => $amountAfter,
            'notes' => (string) ($data['notes'] ?? ''),
            'type' => (int) ($data['type'] ?? TypeConstants::CreateMoneyBoxAdd),
            'payment_type' => (int) ($data['payment_type'] ?? TypeConstants::ONLINE_PAYMENT),
            'branch_setting_id' => $branchSettingId,
            'non_member_subscription_id' => $data['non_member_subscription_id'] ?? null,
            'store_order_id' => $data['store_order_id'] ?? null,
            'member_pt_subscription_id' => $data['member_pt_subscription_id'] ?? null,
            'is_store_balance' => $data['is_store_balance'] ?? null,
        ];

        $moneyBoxColumns = Schema::getColumnListing('sw_gym_money_boxes');
        GymMoneyBox::create(array_intersect_key($moneyBoxData, array_flip($moneyBoxColumns)));
    }

    protected function createUserLogEntry(string $notes, int $type, ?int $userId, int $branchSettingId): void
    {
        $payload = [
            'user_id' => $userId,
            'type' => $type,
            'notes' => $notes,
            'branch_setting_id' => $branchSettingId,
        ];

        $columns = Schema::getColumnListing('sw_gym_user_logs');
        GymUserLog::create(array_intersect_key($payload, array_flip($columns)));
    }

    protected function resolveBranchSettingId(?GymMember $member): int
    {
        $branchSettingId = (int) ($member->branch_setting_id ?? 0);
        if ($branchSettingId > 0) {
            return $branchSettingId;
        }

        $branchSettingId = (int) ($this->user_sw->branch_setting_id ?? 0);
        if ($branchSettingId > 0) {
            return $branchSettingId;
        }

        return 1;
    }

    protected function resolveSystemUserId(?GymMember $member, int $branchSettingId): ?int
    {
        if (!empty($this->user_sw?->id)) {
            return (int) $this->user_sw->id;
        }

        if (!empty($member?->user_id)) {
            return (int) $member->user_id;
        }

        $userId = GymUser::where('branch_setting_id', $branchSettingId)->orderBy('id')->value('id');
        if ($userId) {
            return (int) $userId;
        }

        $fallback = GymUser::orderBy('id')->value('id');
        return $fallback ? (int) $fallback : null;
    }

    protected function createMoneyBoxEntry(GymOnlinePaymentInvoice $invoice, GymMember $member, int $type, int $memberSubId): void
    {
        if (GymMoneyBox::where('online_subscription_id', $invoice->id)->exists()) {
            return;
        }

        $lastBox     = GymMoneyBox::orderBy('id', 'desc')->first();
        $amountBefore = $lastBox ? (float) $lastBox->amount_before : 0;
        $operation   = $lastBox ? (int) $lastBox->operation : TypeConstants::Add;
        $amountAfter = $this->computeAmountAfter($invoice->amount, $amountBefore, $operation);

        $notes = trans('sw.member_moneybox_add_msg', [
            'subscription'    => optional($invoice->subscription)->name,
            'member'          => $member->name,
            'amount_paid'     => $invoice->amount,
            'amount_remaining'=> 0,
        ]);

        $discountVal = $this->calculateDiscountValue($invoice->subscription);
        if ($discountVal > 0) {
            $notes .= ' - ' . trans('sw.discount_msg', ['value' => $discountVal]);
        }
        if ($invoice->vat_percentage) {
            $notes .= ' - ' . trans('sw.vat_added');
        }

        GymMoneyBox::create([
            'operation'               => TypeConstants::Add,
            'amount'                  => $invoice->amount,
            'vat'                     => $invoice->vat,
            'amount_before'           => $amountAfter,
            'notes'                   => $notes,
            'member_id'               => $member->id,
            'type'                    => $type,
            'payment_type'            => $this->resolveGatewayPaymentTypeId((int) ($invoice->payment_method ?? TypeConstants::ONLINE_PAYMENT)),
            'member_subscription_id'  => $memberSubId,
            'online_subscription_id'  => $invoice->id,
        ]);
    }

    /**
     * Resolve the sw_gym_payment_types.payment_id for a given gateway TypeConstant.
     * Looks up a row whose payment_method column matches the gateway constant
     * (e.g. Tabby=4, Paymob=5, Tamara=6, PayTabs=8).
     * Falls back to ONLINE_PAYMENT (1) if no row is configured for this gateway.
     */
    protected function resolveGatewayPaymentTypeId(int $paymentMethod): int
    {
        static $cache = [];
        if (!isset($cache[$paymentMethod])) {
            $row = \Modules\Software\Models\GymPaymentType::where('payment_method', $paymentMethod)->first();
            $cache[$paymentMethod] = $row ? (int) $row->payment_id : TypeConstants::ONLINE_PAYMENT;
        }
        return $cache[$paymentMethod];
    }

    protected function computeAmountAfter(float $amount, float $amountBefore, int $operation): float
    {
        if ($operation === TypeConstants::Add) return $amountBefore + $amount;
        if ($operation === TypeConstants::Sub) return $amountBefore - $amount;
        return $amount;
    }

    protected function calculateDiscountValue($subscription): float
    {
        if (!$subscription) return 0.0;
        $price     = (float) ($subscription->price ?? 0);
        $type      = (int)   ($subscription->default_discount_type ?? 0);
        $value     = (float) ($subscription->default_discount_value ?? 0);
        if ($type === 1 && $value > 0) return round(($value / 100) * $price, 2);
        if ($type === 2 && $value > 0) return round($value, 2);
        return 0.0;
    }

    protected function getDiscountType($subscription): ?int
    {
        if (!$subscription) return null;
        $type = (int) ($subscription->default_discount_type ?? 0);
        return $type > 0 ? $type : null;
    }

    protected function verifyPaymobHmac(Request $request, string $hmacSecret): bool
    {
        $receivedHmac = $request->input('hmac');
        if (!$receivedHmac) {
            return true; // No HMAC sent — skip verification
        }

        $concatenatedString =
            $request->input('amount_cents') .
            $request->input('created_at') .
            $request->input('currency') .
            $request->input('error_occured') .
            $request->input('has_parent_transaction') .
            $request->input('id') .
            $request->input('integration_id') .
            $request->input('is_3d_secure') .
            $request->input('is_auth') .
            $request->input('is_capture') .
            $request->input('is_refunded') .
            $request->input('is_standalone_payment') .
            $request->input('is_voided') .
            $request->input('order') .
            $request->input('owner') .
            $request->input('pending') .
            $request->input('source_data_pan') .
            $request->input('source_data_sub_type') .
            $request->input('source_data_type') .
            $request->input('success');

        $calculatedHmac = hash_hmac('sha512', $concatenatedString, $hmacSecret);
        return hash_equals($calculatedHmac, $receivedHmac);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPGRADE MOBILE — STEP 1: Show eligible upgrade subscriptions
    // ─────────────────────────────────────────────────────────────────────────

    public function showUpgradeMobile()
    {
        $this->currentMember = $member = $this->resolveMemberFromRequest(request());

        if (!$member) {
            return abort(403, trans('front.error_in_data'));
        }

        // Pick the current membership using priority: Active -> Freeze -> Coming -> Expired
        $activeSub = GymMemberSubscription::with('subscription')
            ->where('member_id', $member->id)
            ->whereIn('status', [
                TypeConstants::Active,
                TypeConstants::Freeze,
                TypeConstants::Coming,
                TypeConstants::Expired,
            ])
            ->orderByRaw('CASE status
                WHEN ' . TypeConstants::Active . ' THEN 1
                WHEN ' . TypeConstants::Freeze . ' THEN 2
                WHEN ' . TypeConstants::Coming . ' THEN 3
                WHEN ' . TypeConstants::Expired . ' THEN 4
                ELSE 5 END')
            ->orderBy('id', 'desc')
            ->first();

        if (!$activeSub) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $currentPrice = (float) ($activeSub->price ?? ($activeSub->subscription->price ?? 0));

        // Eligible upgrades: mobile, active, price > current, not current
        $upgrades = GymSubscription::where('is_mobile', 1)
            ->where(DB::raw('CAST(price AS DECIMAL(10,2))'), '>', $currentPrice)
            ->where('id', '!=', $activeSub->subscription_id)
            ->orderBy('price', 'asc')
            ->get();

        $vatPercentage   = @$this->mainSettings->vat_details['vat_percentage'] ?? 0;
        $title           = trans('sw.upgrade_subscription_title');
        $mainSettings    = $this->mainSettings;

        return view('software::Front.upgrade_subscription_mobile', compact(
            'title', 'member', 'activeSub', 'upgrades', 'currentPrice', 'vatPercentage', 'mainSettings'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPGRADE MOBILE — STEP 2: Process upgrade form & redirect to gateway
    // ─────────────────────────────────────────────────────────────────────────

    public function upgradeInvoiceSubmit(Request $request)
    {
        $this->currentMember = $member = $this->resolveMemberFromRequest($request);

        if (!$member) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $newSubscriptionId   = (int) $request->input('subscription_id');
        $oldSubscriptionId   = (int) $request->input('old_subscription_id');
        $activeMemberSubId   = (int) $request->input('active_member_sub_id');
        $diffAmount          = (float) $request->input('amount');
        $vatPercentage       = (float) $request->input('vat_percentage');
        $paymentMethod       = (int) $request->input('payment_method');

        $newSubscription = GymSubscription::find($newSubscriptionId);
        if (!$newSubscription) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $vatAmt = $vatPercentage > 0
            ? round($diffAmount - ($diffAmount / (1 + $vatPercentage / 100)), 2)
            : 0;

        $memberData = [
            'name'               => $member->name,
            'phone'              => $member->phone,
            'email'              => $member->email ?? '',
            'address'            => $member->address ?? '',
            'dob'                => $member->dob,
            'gender'             => $member->gender,
            'joining_date'       => Carbon::now()->toDateString(),
            'payment_method'     => $paymentMethod,
            'payment_channel'    => TypeConstants::CHANNEL_MOBILE_APP,
            'amount'             => $diffAmount,
            'vat_percentage'     => $vatPercentage,
            'vat'                => $vatAmt,
            'subscription_id'    => $newSubscriptionId,
            'old_subscription_id'=> $oldSubscriptionId,
            'active_member_sub_id' => $activeMemberSubId,
        ];

        $subscriptionData = ['id' => $newSubscriptionId, 'name' => $newSubscription->name, 'content' => $newSubscription->content ?? ''];

        if ($paymentMethod === 2) {
            $url = $this->initiateUpgradeTabby($subscriptionData, $memberData);
        } elseif ($paymentMethod === 4) {
            $url = $this->initiateUpgradeTamara($subscriptionData, $memberData);
        } elseif ($paymentMethod === 5) {
            $url = $this->initiateUpgradePaytabs($subscriptionData, $memberData);
        } elseif ($paymentMethod === 6) {
            $url = $this->initiateUpgradePaymob($subscriptionData, $memberData);
        } else {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        return redirect($url);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPGRADE MOBILE — Gateway initiators
    // ─────────────────────────────────────────────────────────────────────────

    private function createUpgradeInvoice(array $member, int $paymentMethod, string $uniqueId): GymOnlinePaymentInvoice
    {
        return GymOnlinePaymentInvoice::create([
            'payment_id'      => $uniqueId,
            'member_id'       => $this->currentMember->id,
            'status'          => TypeConstants::PENDING,
            'subscription_id' => $member['subscription_id'],
            'name'            => $member['name'],
            'email'           => $member['email'],
            'phone'           => $member['phone'],
            'dob'             => $member['dob'],
            'address'         => $member['address'],
            'gender'          => $member['gender'],
            'amount'          => $member['amount'],
            'vat'             => $member['vat'],
            'vat_percentage'  => $member['vat_percentage'],
            'payment_method'  => $paymentMethod,
            'payment_channel' => $member['payment_channel'],
            'response_code'   => [
                'is_upgrade'           => true,
                'joining_date'         => $member['joining_date'],
                'old_subscription_id'  => $member['old_subscription_id'],
                'active_member_sub_id' => $member['active_member_sub_id'],
            ],
        ]);
    }

    protected function initiateUpgradeTabby(array $sub, array $member): string
    {
        $uniqueId   = uniqid();
        $invoice    = $this->createUpgradeInvoice($member, TypeConstants::TABBY_TRANSACTION, $uniqueId);
        $errorRoute = route('sw.upgrade-subscription-mobile', $this->mobileContextParams());

        $tabby  = new TabbyFrontController();
        $result = $tabby->createCheckoutSession([
            'amount'          => $member['amount'],
            'currency'        => env('TABBY_CURRENCY', 'SAR'),
            'description'     => $sub['name'],
            'buyer'           => ['name' => $member['name'], 'phone' => $member['phone'], 'email' => $member['email'], 'address' => '', 'city' => '', 'zip' => '', 'country' => 'SA'],
            'order_reference' => (string) $invoice->id,
            'loyalty_level'   => 0, 'order_history' => [],
            'success_url'     => route('sw.tabby-mobile.verify',  ['invoice_id' => $uniqueId]),
            'cancel_url'      => route('sw.tabby-mobile.cancel',  ['invoice_id' => $uniqueId]),
            'failure_url'     => route('sw.tabby-mobile.failure', ['invoice_id' => $uniqueId]),
            'payment_type'    => 'upgrade_subscription',
            'member_id'       => $this->currentMember->id,
            'subscription_id' => $sub['id'],
        ]);

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }
        $invoice->transaction_id = $result['checkout_id'];
        $invoice->save();
        return $result['payment_url'];
    }

    protected function initiateUpgradeTamara(array $sub, array $member): string
    {
        $priceBeforeVat = round($member['amount'] - $member['vat'], 2);
        $uniqueId       = uniqid();
        $invoice        = $this->createUpgradeInvoice($member, TypeConstants::TAMARA_TRANSACTION, $uniqueId);
        $errorRoute     = route('sw.upgrade-subscription-mobile', $this->mobileContextParams());

        $verifyUrl  = route('sw.tamara-mobile.verify', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $cancelUrl  = route('sw.tamara-mobile.cancel', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $failureUrl = route('sw.tamara-mobile.failure', $this->mobileContextParams(['invoice_id' => $uniqueId]));

        [, , $currency] = $this->getTamaraCredentials();
        $tamara = new TamaraFrontController();
        $result = $tamara->createCheckoutSession([
            'amount'           => $member['amount'],
            'currency'         => $currency,
            'description'      => $sub['name'],
            'buyer'            => ['name' => $member['name'], 'phone' => $member['phone'], 'email' => $member['email'], 'address' => $member['address'], 'city' => env('TAMARA_CITY', 'Riyadh')],
            'order_reference'  => (string) $invoice->id,
            'success_url'      => $verifyUrl,
            'cancel_url'       => $cancelUrl,
            'failure_url'      => $failureUrl,
            'notification_url' => route('tamara.webhook'),
            'payment_type'     => 'mobile_upgrade_subscription',
            'member_id'        => $this->currentMember->id,
            'subscription_id'  => $sub['id'],
            'items'            => [['title' => $sub['name'], 'description' => $sub['content'] ?? '', 'quantity' => 1, 'unit_price' => $priceBeforeVat, 'total_amount' => $member['amount'], 'reference_id' => (string) $invoice->id]],
        ]);

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }
        $invoice->transaction_id = $result['order_id'];
        $invoice->save();
        return $result['payment_url'];
    }

    protected function initiateUpgradePaytabs(array $sub, array $member): string
    {
        $uniqueId   = uniqid();
        $invoice    = $this->createUpgradeInvoice($member, TypeConstants::PAYTABS_TRANSACTION, $uniqueId);
        $errorRoute = route('sw.upgrade-subscription-mobile', $this->mobileContextParams());

        $verifyUrl  = route('sw.paytabs-mobile.verify', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $cancelUrl  = route('sw.paytabs-mobile.cancel', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $failureUrl = route('sw.paytabs-mobile.failure', $this->mobileContextParams(['invoice_id' => $uniqueId]));

        $paytabs = new PayTabsFrontController();
        $result  = $paytabs->createCheckoutSession([
            'amount'          => $member['amount'],
            'description'     => $sub['name'],
            'buyer'           => ['name' => $member['name'], 'email' => $member['email'] ?: 'member@gym.com', 'phone' => $member['phone'], 'city' => '', 'address' => ''],
            'cart_id'         => (string) $invoice->id,
            'success_url'     => $verifyUrl,
            'cancel_url'      => $cancelUrl,
            'failure_url'     => $failureUrl,
            'callback_url'    => route('sw.paytabs-mobile.verify', ['invoice_id' => $uniqueId]),
            'payment_type'    => 'upgrade_subscription',
            'member_id'       => $this->currentMember->id,
            'subscription_id' => $sub['id'],
        ]);

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }
        $invoice->transaction_id = $result['tran_ref'] ?? '';
        $invoice->save();
        return $result['redirect_url'] ?? ($result['payment_url'] ?? $errorRoute);
    }

    protected function initiateUpgradePaymob(array $sub, array $member): string
    {
        $uniqueId   = uniqid();
        $invoice    = $this->createUpgradeInvoice($member, TypeConstants::PAYMOB_TRANSACTION, $uniqueId);
        $errorRoute = route('sw.upgrade-subscription-mobile', $this->mobileContextParams());

        $parts       = explode(' ', $member['name'], 2);
        $billingData = [
            'first_name' => $parts[0] ?? 'Gym', 'last_name' => $parts[1] ?? 'Member',
            'email' => $member['email'] ?: 'member@gym.com', 'phone_number' => $member['phone'],
            'apartment' => 'NA', 'floor' => 'NA', 'street' => $member['address'] ?: 'NA',
            'building' => 'NA', 'shipping_method' => 'NA', 'postal_code' => 'NA',
            'city' => 'NA', 'country' => 'EG', 'state' => 'NA',
        ];

        $paymob    = new PaymobFrontController();
        $iframeUrl = $paymob->payment([
            'name'         => $sub['name'], 'price' => $member['amount'],
            'desc'         => $sub['name'], 'qty' => 1, 'no_fee' => true,
            'billing_data' => $billingData,
            'redirect_url' => route('sw.paymob-mobile.verify', ['invoice_id' => $uniqueId]),
        ]);

        if (!$iframeUrl) {
            \Session::flash('error', trans('front.error_in_data'));
            $invoice->status = TypeConstants::FAILURE;
            $invoice->save();
            return $errorRoute;
        }
        return $iframeUrl;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPGRADE MOBILE — Finalize: expire old sub, create new one
    // ─────────────────────────────────────────────────────────────────────────

    protected function finalizeUpgradeMobileCheckout(GymOnlinePaymentInvoice $invoice, string $joiningDate): ?GymMemberSubscription
    {
        $rc                = (array) $invoice->response_code;
        $activeMemberSubId = (int) ($rc['active_member_sub_id'] ?? 0);
        $newSubId          = (int) $invoice->subscription_id;

        $newSubscription = GymSubscription::withTrashed()->find($newSubId);
        if (!$newSubscription) {
            Log::error('Upgrade Mobile Finalize: new subscription not found', ['invoice_id' => $invoice->id]);
            return null;
        }

        $lockKey = 'upgrade_mobile_finalize_' . $invoice->id;
        DB::selectOne('SELECT GET_LOCK(?, 30) AS locked', [$lockKey]);

        try {
            return DB::transaction(function () use ($invoice, $joiningDate, $newSubscription, $activeMemberSubId, $rc) {
                $invoice = GymOnlinePaymentInvoice::where('id', $invoice->id)->lockForUpdate()->first();

                // Idempotency
                if ($invoice->member_subscription_id) {
                    return GymMemberSubscription::find($invoice->member_subscription_id);
                }

                $member = GymMember::find($invoice->member_id);
                if (!$member) return null;

                // Expire the old active subscription immediately
                if ($activeMemberSubId) {
                    GymMemberSubscription::where('id', $activeMemberSubId)
                        ->where('member_id', $member->id)
                        ->update(['expire_date' => Carbon::now()->toDateString(), 'status' => TypeConstants::Expired]);
                }

                // Create new subscription starting today
                $joining    = Carbon::parse($joiningDate);
                $periodDays = (int) ($newSubscription->period ?? 0);
                $expire     = (clone $joining)->addDays(max($periodDays, 0));

                $newMemberSub = GymMemberSubscription::create([
                    'subscription_id'        => $newSubscription->id,
                    'member_id'              => $member->id,
                    'workouts'               => $newSubscription->workouts ?? 0,
                    'amount_paid'            => $invoice->amount,
                    'vat'                    => $invoice->vat,
                    'vat_percentage'         => $invoice->vat_percentage,
                    'joining_date'           => $joining->toDateTimeString(),
                    'expire_date'            => $expire->toDateTimeString(),
                    'status'                 => TypeConstants::Active,
                    'freeze_limit'           => $newSubscription->freeze_limit ?? 0,
                    'number_times_freeze'    => $newSubscription->number_times_freeze ?? 0,
                    'amount_before_discount' => $newSubscription->price ?? 0,
                    'discount_value'         => $this->calculateDiscountValue($newSubscription),
                    'discount_type'          => $this->getDiscountType($newSubscription),
                    'payment_type'           => $this->resolveGatewayPaymentTypeId((int) ($invoice->payment_method ?? TypeConstants::ONLINE_PAYMENT)),
                ]);

                $invoice->status                 = TypeConstants::SUCCESS;
                $invoice->member_subscription_id = $newMemberSub->id;
                $invoice->invoice_type           = 'subscription';
                $invoice->save();

                $this->createMoneyBoxEntry($invoice, $member, TypeConstants::RenewMember, $newMemberSub->id);

                // ── ZATCA Billing Invoice entry ─────────────────────────────
                $totalAmount     = (float) ($invoice->amount ?? 0);
                $vatAmount       = (float) ($invoice->vat ?? 0);
                if ($vatAmount > 0) {
                    $amountBeforeVat = round($totalAmount - $vatAmount, 2);
                } elseif ((float) ($invoice->vat_percentage ?? 0) > 0) {
                    $amountBeforeVat = round($totalAmount / (1 + $invoice->vat_percentage / 100), 2);
                    $vatAmount       = round($totalAmount - $amountBeforeVat, 2);
                } else {
                    $amountBeforeVat = $totalAmount;
                }
                try {
                    SwBillingService::createInvoiceFromMember(
                        $member,
                        $newMemberSub->id,
                        $amountBeforeVat,
                        $vatAmount
                    );
                } catch (\Throwable $e) {
                    Log::warning('ZATCA billing invoice creation failed (upgrade)', [
                        'member_id' => $member->id,
                        'member_subscription_id' => $newMemberSub->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $newMemberSub->is_upgrade = true;
                return $newMemberSub;
            });
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?)', [$lockKey]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPGRADE MOBILE — STEP 5: Show upgrade invoice
    // ─────────────────────────────────────────────────────────────────────────

    public function upgradeInvoiceMobile($id)
    {
        $memberSub = GymMemberSubscription::with(['subscription', 'member'])->find($id);
        if (!$memberSub) return abort(404);

        $title        = trans('sw.upgrade_subscription_title');
        $mainSettings = $this->mainSettings;

        return view('software::Front.upgrade_invoice_mobile', compact('title', 'memberSub', 'mainSettings'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT MOBILE — STEP 1: Show PT subscription classes + trainer schedule
    // ─────────────────────────────────────────────────────────────────────────

    public function showPtMobile($id)
    {
        $request = request();
        // Keep variable name for blade compatibility, but source from member_id.
        $hasToken = (int) ($request->input('member_id') ?: 0) > 0;

        $this->currentMember = $currentUser = $this->resolveMemberFromRequest($request, false);

        $hasActiveMainSubscription = false;
        if ($currentUser) {
            $hasActiveMainSubscription = GymMemberSubscription::where('member_id', $currentUser->id)
                ->whereDate('expire_date', '>=', Carbon::now()->toDateString())
                ->orderByDesc('id')
                ->exists();
        }

        $ptSubscription = \Modules\Software\Models\GymPTSubscription::with([
            'classes' => function ($q) {
                $q->where('is_active', true)->with(['activeClassTrainers.trainer']);
            }
        ])->find($id);

        if (!$ptSubscription) {
            return abort(404);
        }

        $title        = $ptSubscription->name;
        $mainSettings = $this->mainSettings;

        return view('software::Front.pt_subscription_mobile', compact(
            'title', 'ptSubscription', 'mainSettings', 'currentUser',
            'hasActiveMainSubscription', 'hasToken'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT MOBILE — STEP 2: Process form & redirect to gateway
    // ─────────────────────────────────────────────────────────────────────────

    public function ptInvoiceSubmit(Request $request)
    {
        $this->currentMember = $this->resolveMemberFromRequest($request, false);

        $ptClassId        = (int) $request->input('pt_class_id');
        $ptClassTrainerId = (int) $request->input('pt_class_trainer_id');

        $ptClass = \Modules\Software\Models\GymPTClass::with(['activeClassTrainers.trainer'])->find($ptClassId);
        if (!$ptClass) {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        $classTrainer = \Modules\Software\Models\GymPTClassTrainer::find($ptClassTrainerId);
        if (!$classTrainer || $classTrainer->class_id != $ptClassId) {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        // Build member data
        $memberData = [];
        if (!$this->currentMember) {
            if (\Modules\Software\Models\GymMember::where('phone', $request->phone)->exists()) {
                return redirect()->back()->with('error', trans('front.error_member_exist'));
            }
            $memberData['name']    = $request->name;
            $memberData['phone']   = $request->phone;
            $memberData['email']   = $request->email ?? '';
            $memberData['address'] = $request->address ?? '';
            $memberData['dob']     = $request->dob ? Carbon::parse($request->dob) : null;
            $memberData['gender']  = $request->gender;
        } else {
            $memberData['name']    = $this->currentMember->name;
            $memberData['phone']   = $this->currentMember->phone;
            $memberData['email']   = $this->currentMember->email ?? '';
            $memberData['address'] = $this->currentMember->address ?? '';
            $memberData['dob']     = $this->currentMember->dob;
            $memberData['gender']  = $this->currentMember->gender;
        }

        $memberData['joining_date']        = $request->joining_date ?? Carbon::now()->toDateString();
        $memberData['payment_method']      = (int) $request->payment_method;
        $memberData['payment_channel']     = TypeConstants::CHANNEL_MOBILE_APP;
        $memberData['amount']              = (float) $request->amount;
        $memberData['vat_percentage']      = (float) $request->vat_percentage;
        $memberData['pt_subscription_id']  = (int) $ptClass->pt_subscription_id;
        $memberData['pt_class_id']         = $ptClassId;
        $memberData['pt_class_trainer_id'] = $ptClassTrainerId;
        $memberData['pt_total_sessions']   = (int) ($ptClass->total_sessions ?? 0);

        $vatPct = $memberData['vat_percentage'];
        $memberData['vat'] = $vatPct > 0 ? round($memberData['amount'] - ($memberData['amount'] / (1 + $vatPct / 100)), 2) : 0;

        $paymentMethod = $memberData['payment_method'];
        $ptSubscriptionData = ['id' => $ptClass->pt_subscription_id, 'name' => $ptClass->name, 'content' => $ptClass->content ?? ''];

        if ($paymentMethod === 2) {
            $paymentUrl = $this->initiatePtTabby($ptSubscriptionData, $memberData);
        } elseif ($paymentMethod === 4) {
            $paymentUrl = $this->initiatePtTamara($ptSubscriptionData, $memberData);
        } elseif ($paymentMethod === 5) {
            $paymentUrl = $this->initiatePtPaytabs($ptSubscriptionData, $memberData);
        } elseif ($paymentMethod === 6) {
            $paymentUrl = $this->initiatePtPaymob($ptSubscriptionData, $memberData);
        } else {
            return redirect()->back()->with('error', trans('front.error_in_data'));
        }

        return redirect($paymentUrl);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT MOBILE — STEP 3: Initiate gateway (reuse parent methods with PT invoice)
    // ─────────────────────────────────────────────────────────────────────────

    private function createPtInvoice(array $member, int $paymentMethod, string $uniqueId): GymOnlinePaymentInvoice
    {
        return GymOnlinePaymentInvoice::create([
            'payment_id'      => $uniqueId,
            'member_id'       => optional($this->currentMember)->id,
            'status'          => TypeConstants::PENDING,
            'subscription_id' => null,
            'name'            => $member['name'],
            'email'           => $member['email'] ?? '',
            'phone'           => $member['phone'],
            'dob'             => $member['dob'],
            'address'         => $member['address'],
            'gender'          => $member['gender'],
            'amount'          => $member['amount'],
            'vat'             => $member['vat'],
            'vat_percentage'  => $member['vat_percentage'],
            'payment_method'  => $paymentMethod,
            'payment_channel' => $member['payment_channel'],
            'response_code'   => [
                'is_pt'               => true,
                'joining_date'        => $member['joining_date'],
                'pt_subscription_id'  => $member['pt_subscription_id'],
                'pt_class_id'         => $member['pt_class_id'],
                'pt_class_trainer_id' => $member['pt_class_trainer_id'],
                'pt_total_sessions'   => $member['pt_total_sessions'],
            ],
        ]);
    }

    protected function initiatePtTabby(array $ptSub, array $member): string
    {
        $totalAmount = round($member['amount'], 2);
        $uniqueId    = uniqid();
        $invoice     = $this->createPtInvoice($member, TypeConstants::TABBY_TRANSACTION, $uniqueId);
        $errorRoute  = route('sw.pt-subscription-mobile', $this->mobileContextParams(['id' => $ptSub['id']]));

        $tabby  = new TabbyFrontController();
        $result = $tabby->createCheckoutSession([
            'amount'          => $totalAmount,
            'currency'        => env('TABBY_CURRENCY', 'SAR'),
            'description'     => $ptSub['name'],
            'buyer'           => ['name' => $member['name'], 'phone' => $member['phone'], 'email' => $member['email'] ?? '', 'address' => '', 'city' => '', 'zip' => '', 'country' => 'SA'],
            'order_reference' => (string) $invoice->id,
            'loyalty_level'   => 0,
            'order_history'   => [],
            'success_url'     => route('sw.tabby-mobile.verify',  ['invoice_id' => $uniqueId]),
            'cancel_url'      => route('sw.tabby-mobile.cancel',  ['invoice_id' => $uniqueId]),
            'failure_url'     => route('sw.tabby-mobile.failure', ['invoice_id' => $uniqueId]),
            'payment_type'    => 'pt_subscription',
            'member_id'       => optional($this->currentMember)->id,
            'subscription_id' => $ptSub['id'],
        ]);

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }

        $invoice->transaction_id = $result['checkout_id'];
        $invoice->save();
        return $result['payment_url'];
    }

    protected function initiatePtTamara(array $ptSub, array $member): string
    {
        $totalAmount    = round($member['amount'], 2);
        $priceBeforeVat = round($totalAmount - $member['vat'], 2);
        $uniqueId       = uniqid();
        $invoice        = $this->createPtInvoice($member, TypeConstants::TAMARA_TRANSACTION, $uniqueId);
        $errorRoute     = route('sw.pt-subscription-mobile', $this->mobileContextParams(['id' => $ptSub['id']]));

        $verifyUrl  = route('sw.tamara-mobile.verify', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $cancelUrl  = route('sw.tamara-mobile.cancel', $this->mobileContextParams(['invoice_id' => $uniqueId]));
        $failureUrl = route('sw.tamara-mobile.failure', $this->mobileContextParams(['invoice_id' => $uniqueId]));

        [, , $tamaraCurrency] = $this->getTamaraCredentials();
        $tamara = new TamaraFrontController();
        $result = $tamara->createCheckoutSession([
            'amount'           => $totalAmount,
            'currency'         => $tamaraCurrency,
            'description'      => $ptSub['name'],
            'buyer'            => ['name' => $member['name'], 'phone' => $member['phone'], 'email' => $member['email'] ?? '', 'address' => $member['address'] ?? '', 'city' => env('TAMARA_CITY', 'Riyadh')],
            'order_reference'  => (string) $invoice->id,
            'success_url'      => $verifyUrl,
            'cancel_url'       => $cancelUrl,
            'failure_url'      => $failureUrl,
            'notification_url' => route('tamara.webhook'),
            'payment_type'     => 'mobile_pt_subscription',
            'member_id'        => optional($this->currentMember)->id,
            'subscription_id'  => $ptSub['id'],
            'items'            => [['title' => $ptSub['name'], 'description' => $ptSub['content'] ?? '', 'quantity' => 1, 'unit_price' => $priceBeforeVat, 'total_amount' => $totalAmount, 'reference_id' => (string) $invoice->id]],
        ]);

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }

        $invoice->transaction_id = $result['order_id'];
        $invoice->save();
        return $result['payment_url'];
    }

    protected function initiatePtPaytabs(array $ptSub, array $member): string
    {
        $totalAmount = round($member['amount'], 2);
        $uniqueId    = uniqid();
        $invoice     = $this->createPtInvoice($member, TypeConstants::PAYTABS_TRANSACTION, $uniqueId);
        $errorRoute  = route('sw.pt-subscription-mobile', $this->mobileContextParams(['id' => $ptSub['id']]));

        $paytabs = new PayTabsFrontController();
        $result  = $paytabs->createCheckoutSession([
            'amount'          => $totalAmount,
            'description'     => $ptSub['name'],
            'buyer'           => [
                'name'    => $member['name'],
                'email'   => $member['email'] ?? 'member@gym.com',
                'phone'   => $member['phone'],
                'city'    => '',
                'address' => '',
            ],
            'success_url'     => route('sw.paytabs-mobile.verify', ['invoice_id' => $uniqueId]),
            'cancel_url'      => route('sw.paytabs-mobile.verify', ['invoice_id' => $uniqueId]),
            'failure_url'     => route('sw.paytabs-mobile.verify', ['invoice_id' => $uniqueId]),
            'callback_url'    => route('sw.paytabs-mobile.verify', ['invoice_id' => $uniqueId]),
            'cart_id'         => (string) $invoice->id,
            'payment_type'    => 'pt_subscription',
            'member_id'       => optional($this->currentMember)->id,
            'subscription_id' => $ptSub['id'],
        ]);

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }

        $invoice->transaction_id = $result['tran_ref'] ?? '';
        $invoice->save();
        return $result['redirect_url'] ?? ($result['payment_url'] ?? $errorRoute);
    }

    protected function initiatePtPaymob(array $ptSub, array $member): string
    {
        $totalAmount = round($member['amount'], 2);
        $uniqueId    = uniqid();
        $invoice     = $this->createPtInvoice($member, TypeConstants::PAYMOB_TRANSACTION, $uniqueId);
        $errorRoute  = route('sw.pt-subscription-mobile', $this->mobileContextParams(['id' => $ptSub['id']]));

        $nameParts   = explode(' ', $member['name'], 2);
        $billingData = [
            'first_name' => $nameParts[0] ?? 'Gym', 'last_name' => $nameParts[1] ?? 'Member',
            'email' => $member['email'] ?? 'member@gym.com', 'phone_number' => $member['phone'] ?? '01000000000',
            'apartment' => 'NA', 'floor' => 'NA', 'street' => $member['address'] ?? 'NA',
            'building' => 'NA', 'shipping_method' => 'NA', 'postal_code' => 'NA',
            'city' => 'NA', 'country' => 'EG', 'state' => 'NA',
        ];

        $paymob    = new PaymobFrontController();
        $iframeUrl = $paymob->payment([
            'name'         => $ptSub['name'],
            'price'        => $totalAmount,
            'desc'         => $ptSub['name'],
            'qty'          => 1,
            'no_fee'       => true,
            'billing_data' => $billingData,
            'redirect_url' => route('sw.paymob-mobile.verify', ['invoice_id' => $uniqueId]),
        ]);

        if (!$iframeUrl) {
            \Session::flash('error', trans('front.error_in_data'));
            $invoice->status = TypeConstants::FAILURE;
            $invoice->save();
            return $errorRoute;
        }

        return $iframeUrl;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT MOBILE — STEP 4: Finalize PT checkout (creates GymPTMember)
    // ─────────────────────────────────────────────────────────────────────────

    protected function finalizePtMobileCheckout(GymOnlinePaymentInvoice $invoice, string $joiningDate): ?int
    {
        $rc               = (array) $invoice->response_code;
        $ptClassId        = (int) ($rc['pt_class_id'] ?? 0);
        $ptClassTrainerId = (int) ($rc['pt_class_trainer_id'] ?? 0);
        $ptSubscriptionId = (int) ($rc['pt_subscription_id'] ?? 0);
        $totalSessions    = (int) ($rc['pt_total_sessions'] ?? 0);
        $notificationPayload = null;

        $ptClass = \Modules\Software\Models\GymPTClass::find($ptClassId);
        if (!$ptClass) {
            Log::error('PT Mobile Finalize: class not found', ['invoice_id' => $invoice->id]);
            return null;
        }

        $lockKey = 'pt_mobile_finalize_' . $invoice->id;
        DB::selectOne('SELECT GET_LOCK(?, 30) AS locked', [$lockKey]);

        try {
            $ptMemberId = DB::transaction(function () use ($invoice, $joiningDate, $ptClass, $ptClassId, $ptClassTrainerId, $ptSubscriptionId, $totalSessions, &$notificationPayload) {
                $invoice = GymOnlinePaymentInvoice::where('id', $invoice->id)->lockForUpdate()->first();

                // Idempotency: check if pt_member_id already set in response_code
                $rc = (array) $invoice->response_code;
                if (!empty($rc['pt_member_id'])) {
                    return (int) $rc['pt_member_id'];
                }

                // Resolve or create member
                $member = null;
                if ($invoice->member_id) {
                    $member = GymMember::find($invoice->member_id);
                }
                if (!$member && $invoice->phone) {
                    $member = GymMember::where('phone', $invoice->phone)->first();
                }
                if (!$member) {
                    $maxCode = str_pad(((int) GymMember::withTrashed()->max('code') + 1), 14, '0', STR_PAD_LEFT);
                    $member  = GymMember::create([
                        'code'    => $maxCode,
                        'name'    => $invoice->name,
                        'gender'  => $invoice->gender,
                        'phone'   => $invoice->phone,
                        'address' => $invoice->address,
                        'dob'     => $invoice->dob,
                    ]);
                }

                $joining    = Carbon::parse($joiningDate);
                $expireDate = $totalSessions > 0
                    ? (clone $joining)->addDays($totalSessions * 7) // 1 session/week estimate
                    : (clone $joining)->addMonths(3);

                $resolvedTrainerId = 0;
                if ($ptClassTrainerId > 0) {
                    $resolvedTrainerId = (int) (\Modules\Software\Models\GymPTClassTrainer::where('id', $ptClassTrainerId)
                        ->value('trainer_id') ?? 0);
                }
                if ($resolvedTrainerId <= 0) {
                    $resolvedTrainerId = (int) (\Modules\Software\Models\GymPTClassTrainer::where('class_id', $ptClassId)
                        ->orderByDesc('is_active')
                        ->orderByDesc('id')
                        ->value('trainer_id') ?? 0);
                }
                if ($resolvedTrainerId <= 0 && Schema::hasTable('sw_gym_pt_subscription_trainers')) {
                    $resolvedTrainerId = (int) (DB::table('sw_gym_pt_subscription_trainers')
                        ->where('pt_class_id', $ptClassId)
                        ->orderByDesc('id')
                        ->value('pt_trainer_id') ?? 0);
                }
                if ($resolvedTrainerId <= 0) {
                    $resolvedTrainerId = (int) (DB::table('sw_gym_pt_trainers')->orderBy('id')->value('id') ?? 0);
                }

                $ptMemberData = [
                    'member_id'          => $member->id,
                    'pt_subscription_id' => $ptSubscriptionId,
                    'pt_class_id'        => $ptClassId,
                    'pt_trainer_id'      => $resolvedTrainerId > 0 ? $resolvedTrainerId : null,
                    'class_id'           => $ptClassId,
                    'class_trainer_id'   => $ptClassTrainerId > 0 ? $ptClassTrainerId : null,
                    'classes'            => $totalSessions,
                    'visits'             => 0,
                    'total_sessions'     => $totalSessions,
                    'remaining_sessions' => $totalSessions,
                    'start_date'         => $joining->toDateString(),
                    'end_date'           => $expireDate->toDateString(),
                    'expire_date'        => $expireDate->toDateString(),
                    'joining_date'       => $joining->toDateString(),
                    'amount_paid'        => $invoice->amount,
                    'paid_amount'        => $invoice->amount,
                    'amount_remaining'   => 0,
                    'vat'                => (float) ($invoice->vat ?? 0),
                    'vat_percentage'     => (int) ($invoice->vat_percentage ?? 0),
                    'payment_type'       => $this->resolveGatewayPaymentTypeId((int) ($invoice->payment_method ?? TypeConstants::ONLINE_PAYMENT)),
                    'is_active'          => 1,
                    'branch_setting_id'  => $member->branch_setting_id ?? null,
                ];

                $ptMemberColumns = Schema::getColumnListing('sw_gym_pt_members');
                $ptMemberInsert = array_intersect_key($ptMemberData, array_flip($ptMemberColumns));
                $ptMember = \Modules\Software\Models\GymPTMember::create($ptMemberInsert);

                // Store pt_member_id for idempotency; reuse member_subscription_id column
                $invoice->member_subscription_id = $ptMember->id;
                $invoice->invoice_type           = 'pt_subscription';
                $invoice->status                 = TypeConstants::SUCCESS;
                $invoice->response_code          = array_merge($rc, ['pt_member_id' => $ptMember->id]);
                $invoice->save();

                // Money box
                $notes = trans('sw.pt_member_moneybox_add_msg', [
                    'subscription' => ($ptClass->name ?? trans('sw.pt_subscription')) . ' (' . $totalSessions . ')',
                    'member' => $member->name,
                    'amount_paid' => (float) $invoice->amount,
                    'amount_remaining' => 0,
                ]);
                if (!empty($invoice->vat_percentage)) {
                    $notes .= ' - ' . trans('sw.vat_added');
                }

                $this->createGenericMoneyBoxEntry([
                    'member_id' => $member->id,
                    'amount' => (float) $invoice->amount,
                    'vat' => (float) ($invoice->vat ?? 0),
                    'notes' => $notes,
                    'type' => TypeConstants::CreatePTMember,
                    'payment_type' => $this->resolveGatewayPaymentTypeId((int) ($invoice->payment_method ?? TypeConstants::ONLINE_PAYMENT)),
                    'branch_setting_id' => $this->resolveBranchSettingId($member),
                    'user_id' => null,
                    'member_pt_subscription_id' => (int) $ptMember->id,
                ]);

                $this->createUserLogEntry($notes, TypeConstants::CreateMoneyBoxAdd, null, $this->resolveBranchSettingId($member));
                $this->createUserLogEntry($notes, TypeConstants::CreatePTMember, null, $this->resolveBranchSettingId($member));

                // ── ZATCA Billing Invoice entry ─────────────────────────────
                $totalAmount     = (float) ($invoice->amount ?? 0);
                $vatAmount       = (float) ($invoice->vat ?? 0);
                if ($vatAmount > 0) {
                    $amountBeforeVat = round($totalAmount - $vatAmount, 2);
                } elseif ((float) ($invoice->vat_percentage ?? 0) > 0) {
                    $amountBeforeVat = round($totalAmount / (1 + $invoice->vat_percentage / 100), 2);
                    $vatAmount       = round($totalAmount - $amountBeforeVat, 2);
                } else {
                    $amountBeforeVat = $totalAmount;
                }
                try {
                    SwBillingService::createInvoiceFromPtMember(
                        $ptMember,
                        $amountBeforeVat,
                        $vatAmount
                    );
                } catch (\Throwable $e) {
                    Log::warning('ZATCA billing invoice creation failed (PT member)', [
                        'pt_member_id' => $ptMember->id,
                        'member_id' => $member->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $notificationPayload = [
                    'event_code' => 'new_pt_member',
                    'membership' => $ptMember->loadMissing(['member', 'pt_subscription', 'class']),
                    'phone' => $member->phone ?? null,
                    'branch_setting_id' => $this->resolveBranchSettingId($member),
                ];

                return $ptMember->id;
            });

            if ($ptMemberId && $notificationPayload) {
                $this->sendMembershipEventNotification(
                    $notificationPayload['event_code'],
                    $notificationPayload['membership'],
                    $notificationPayload['phone'],
                    $notificationPayload['branch_setting_id']
                );
            }

            return $ptMemberId;
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?)', [$lockKey]);
        }
    }

    protected function sendMembershipEventNotification(string $eventCode, $membership, ?string $phone = null, ?int $branchSettingId = null): void
    {
        try {
            $notificationService = app(NotificationService::class);
            $result = $notificationService->sendEventNotification($eventCode, $membership, $phone, $branchSettingId);

            if (!$result['success']) {
                Log::warning('Mobile event notification was not sent', [
                    'event_code' => $eventCode,
                    'message' => $result['message'] ?? null,
                    'membership_id' => $membership->id ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Mobile event notification dispatch failed', [
                'event_code' => $eventCode,
                'membership_id' => $membership->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT MOBILE — STEP 5: Show PT invoice
    // ─────────────────────────────────────────────────────────────────────────

    public function ptInvoiceMobile($id)
    {
        $ptMember = \Modules\Software\Models\GymPTMember::with(['member', 'class', 'classTrainer.trainer'])->find($id);
        if (!$ptMember) {
            return abort(404);
        }
        $title        = trans('sw.pt_subscription_mobile_title');
        $mainSettings = $this->mainSettings;
        return view('software::Front.pt_invoice_mobile', compact('title', 'ptMember', 'mainSettings'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIVITY / STORE — Invoice pages (post-payment success)
    // ─────────────────────────────────────────────────────────────────────────

    public function activityInvoiceMobile(Request $request)
    {
        $invoice = GymOnlinePaymentInvoice::where('payment_id', $request->invoice_id)->first();
        if (!$invoice) return abort(404);

        $rc              = (array) $invoice->response_code;
        $storedActivities = $rc['activity_ids'] ?? [];
        // Support old format (plain IDs) by falling back to DB query
        if (!empty($storedActivities) && !is_array($storedActivities[0])) {
            $storedActivities = GymActivity::whereIn('id', array_filter(array_map('intval', $storedActivities)))
                ->get()->map(fn($a) => [
                    'id' => $a->id, 'name_ar' => $a->name_ar, 'name_en' => $a->name_en,
                    'price' => $a->price, 'name' => $a->name ?? $a->name_ar,
                    'content' => $a->content ?? null, 'image_name' => $a->image_name ?? null,
                ])->values()->toArray();
        }

        $title        = trans('front.invoice');
        $mainSettings = $this->mainSettings;
        return view('software::Front.activity_invoice_mobile', compact('title', 'invoice', 'mainSettings'))
            ->with('storedActivities', $storedActivities);
    }

    public function storeOrderInvoiceMobile(Request $request)
    {
        $invoice = GymOnlinePaymentInvoice::where('payment_id', $request->invoice_id)->first();
        if (!$invoice) return abort(404);

        $rc               = (array) $invoice->response_code;
        $storeItems       = $rc['store_product_items'] ?? [['id' => (int) ($rc['store_id'] ?? 0), 'qty' => 1]];
        $productIds       = array_values(array_filter(array_column((array) $storeItems, 'id')));
        $products         = GymStoreProduct::whereIn('id', $productIds)->get()->keyBy('id');

        $title        = trans('front.invoice');
        $mainSettings = $this->mainSettings;
        return view('software::Front.store_order_invoice_mobile', compact('title', 'invoice', 'products', 'storeItems', 'mainSettings'));
    }
}

