<?php

namespace Modules\Software\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymAiReport as GymAiReportModel;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberAttendee;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymSubscription;

/**
 * GymAiReport — Standalone AI Business Report Class
 *
 * Language is resolved from  env('DEFAULT_LANG', 'ar')
 * and applied to: ChatGPT prompt, email labels, RTL layout, SMS text.
 *
 * GETTER  →  getter($from, $to)
 *            Collects KPI data → calls ChatGPT → saves to sw_ai_reports → returns record ID + report.
 *
 * SETTER  →  setter($reportId, $email, $phone)
 *            Loads saved report from DB by ID → sends email + SMS → updates delivery status.
 *
 * Configuration (Setting::integrations['ai']):
 *   openai_key    — OpenAI secret key  (required)
 *   openai_model  — GPT model          (default: gpt-4o)
 */
class GymAiReport
{
    private Setting $settings;
    private string  $lang;

    public function __construct()
    {
        $this->settings = Setting::first();
        $this->lang     = env('DEFAULT_LANG', 'ar');
    }

    // =========================================================================
    //  GETTER — Collect gym data → ChatGPT → Save to DB → Return ID + report
    // =========================================================================

    /**
     * Collect KPI data, call ChatGPT, persist the result to sw_ai_reports,
     * and return the saved record ID alongside the report.
     *
     * @return array  ['id' => int, 'report' => array, 'generated_at' => string]
     */
    public function getter(string $from, string $to): array
    {
        $integrations = $this->settings->integrations ?? [];
        $gymData      = $this->collectData($from, $to);
        $report       = $this->callChatGPT($gymData);

        $record = GymAiReportModel::create([
            'branch_setting_id' => $this->settings->id ?? 1,
            'type'              => 'executive',
            'method'            => 'chatgpt',
            'model_used'        => $integrations['ai']['openai_model'] ?? 'gpt-4o',
            'lang'              => $this->lang,
            'from_date'         => $from,
            'to_date'           => $to,
            'gym_data'          => $gymData,
            'report'            => $report,
        ]);

        // Auto-send to support_email, noreply_email, and all AI notify_emails
        $autoEmails = array_values(array_filter(array_unique(array_merge(
            [
                trim((string)($this->settings->support_email ?? '')),
                trim((string)($this->settings->noreply_email ?? '')),
            ],
            array_map('trim', (array)($integrations['ai']['notify_emails'] ?? []))
        ))));

        if (!empty($autoEmails)) {
            $sentEmails = [];
            foreach ($autoEmails as $email) {
                if ($email && $this->sendEmail($email, $report)) {
                    $sentEmails[] = $email;
                }
            }
            if (!empty($sentEmails)) {
                $record->update([
                    'email_sent'    => true,
                    'email_sent_to' => implode(', ', $sentEmails),
                    'email_sent_at' => now(),
                ]);
            }
        }

        return [
            'id'           => $record->id,
            'report'       => $report,
            'generated_at' => $record->created_at->toDateTimeString(),
        ];
    }

    /**
     * Resolve the correct language sub-report.
     * Kept for forward-compatibility — currently reports are single-language.
     */
    private function resolveReport(array $report): array
    {
        return $report;
    }

    // =========================================================================
    //  SETTER — Load report from DB by ID → Email + SMS → Update delivery status
    // =========================================================================

    /**
     * Load a saved report from sw_ai_reports by ID, deliver it via
     * email and/or SMS, and update the delivery columns.
     *
     * @param  int          $reportId  ID from sw_ai_reports
     * @param  string|null  $email
     * @param  string|null  $phone
     * @return array        ['email' => bool, 'sms' => bool]
     * @throws \RuntimeException if record not found
     */
    public function setter(int $reportId, ?string $email = null, ?string $phone = null): array
    {
        $record = GymAiReportModel::findOrFail($reportId);
        $report = $this->resolveReport($record->report ?? []);

        if (!$report) {
            throw new \RuntimeException("Report #{$reportId} has no generated content.");
        }

        $results = ['email' => false, 'sms' => false];

        if ($email) {
            $results['email'] = $this->sendEmail($email, $report);
            if ($results['email']) {
                $record->markEmailSent($email);
            }
        }

        if ($phone) {
            $results['sms'] = $this->sendSms($phone, $report);
            if ($results['sms']) {
                $record->markSmsSent($phone);
            }
        }

        return $results;
    }

    /**
     * Deliver a saved report to multiple email addresses and/or phone numbers.
     *
     * @param  int      $reportId
     * @param  array    $emails   List of email addresses
     * @param  array    $phones   List of phone numbers
     * @return array    ['emails' => [email => bool, …], 'sms' => [phone => bool, …]]
     * @throws \RuntimeException if record not found or has no content
     */
    public function setterMulti(int $reportId, array $emails = [], array $phones = []): array
    {
        $record = GymAiReportModel::findOrFail($reportId);
        $report = $this->resolveReport($record->report ?? []);

        if (!$report) {
            throw new \RuntimeException("Report #{$reportId} has no generated content.");
        }

        $results = ['emails' => [], 'sms' => []];

        foreach ($emails as $email) {
            $email = trim($email);
            if (!$email) continue;
            $ok = $this->sendEmail($email, $report);
            $results['emails'][$email] = $ok;
            if ($ok) {
                $record->markEmailSent($email);
            }
        }

        foreach ($phones as $phone) {
            $phone = trim($phone);
            if (!$phone) continue;
            $ok = $this->sendSms($phone, $report);
            $results['sms'][$phone] = $ok;
            if ($ok) {
                $record->markSmsSent($phone);
            }
        }

        return $results;
    }

    // =========================================================================
    //  PRIVATE — Translations
    // =========================================================================

    private function lbl(): array
    {
        if ($this->lang === 'ar') {
            return [
                'report_subtitle'  => 'التقرير التنفيذي للأعمال',
                'executive_summary'=> 'الملخص التنفيذي',
                'kpi_analysis'     => 'تحليل مؤشرات الأداء',
                'attendance'       => 'تحليل الحضور',
                'top_packages'     => 'الباقات الرائدة',
                'weak_packages'    => 'الباقات الضعيفة',
                'sales_insights'   => 'رؤى المبيعات',
                'risk_alerts'      => 'تنبيهات المخاطر',
                'recommendations'  => 'التوصيات الاستراتيجية',
                'action_plan'      => 'خطة العمل للشهر القادم',
                'no_data'          => 'لا توجد بيانات',
                'footer'           => 'تم الإنشاء بواسطة جيماوي &bull; سري وخاص',
                'subject'          => 'التقرير التنفيذي الذكي',
                'sms_label'        => 'التقرير الذكي',
                'revenue'          => '💰 الإيراد: ',
                'renewal'          => '🔄 التجديد: ',
                'churn'            => '⚠️ الإلغاء: ',
                'new_members'      => '👥 أعضاء جدد: ',
                'risks_prefix'     => '🚨 مخاطر: ',
                'actions_prefix'   => '✅ إجراءات: ',
                'dir'              => 'rtl',
                'align'            => 'right',
                'list_padding'     => 'padding-right:20px;padding-left:0',
                'ol_padding'       => 'padding-right:22px;padding-left:0',
                'border_side'      => 'border-right',
            ];
        }

        return [
            'report_subtitle'  => 'Executive Business Report',
            'executive_summary'=> 'Executive Summary',
            'kpi_analysis'     => 'KPI Analysis',
            'attendance'       => 'Attendance Analysis',
            'top_packages'     => 'Top Packages',
            'weak_packages'    => 'Weak Packages',
            'sales_insights'   => 'Sales Insights',
            'risk_alerts'      => 'Risk Alerts',
            'recommendations'  => 'Strategic Recommendations',
            'action_plan'      => 'Next Month Action Plan',
            'no_data'          => 'No data',
            'footer'           => 'Generated by Gymmawy &bull; Confidential',
            'subject'          => 'AI Executive Report',
            'sms_label'        => 'AI Report',
            'revenue'          => '💰 Revenue: ',
            'renewal'          => '🔄 Renewal: ',
            'churn'            => '⚠️ Churn: ',
            'new_members'      => '👥 New: ',
            'risks_prefix'     => '🚨 ',
            'actions_prefix'   => '✅ ',
            'dir'              => 'ltr',
            'align'            => 'left',
            'list_padding'     => 'padding-left:20px;padding-right:0',
            'ol_padding'       => 'padding-left:22px;padding-right:0',
            'border_side'      => 'border-left',
        ];
    }

    private function gymName(): string
    {
        if ($this->lang === 'ar') {
            return $this->settings->name_ar ?: ($this->settings->name_en ?? 'Gym');
        }
        return $this->settings->name_en ?: ($this->settings->name_ar ?? 'Gym');
    }

    // =========================================================================
    //  PRIVATE — Data Collection
    // =========================================================================

    private function collectData(string $from, string $to): array
    {
        $saleTypes = [
            TypeConstants::CreateMember, TypeConstants::RenewMember, TypeConstants::EditMember,
            TypeConstants::CreateMemberPayAmountRemainingForm,
            TypeConstants::CreateSubscription, TypeConstants::EditSubscription,
            TypeConstants::CreatePTMember, TypeConstants::RenewPTMember, TypeConstants::EditPTMember,
            TypeConstants::CreatePTMemberPayAmountRemainingForm,
            TypeConstants::CreatePTSubscription, TypeConstants::EditPTSubscription,
            TypeConstants::CreateActivity, TypeConstants::EditActivity,
            TypeConstants::CreateNonMember, TypeConstants::EditNonMember,
            TypeConstants::CreateStoreOrder, TypeConstants::EditStoreOrder, TypeConstants::CashSale,
        ];

        $mkQuery = fn() => GymMoneyBox::branch()
            ->where('operation', TypeConstants::Add)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        $totalRevenue = (clone $mkQuery())->whereIn('type', $saleTypes)->sum('amount');
        $subRevenue   = (clone $mkQuery())->whereIn('type', [
            TypeConstants::CreateMember, TypeConstants::RenewMember, TypeConstants::EditMember,
            TypeConstants::CreateMemberPayAmountRemainingForm,
            TypeConstants::CreateSubscription, TypeConstants::EditSubscription,
        ])->sum('amount');
        $ptRevenue    = (clone $mkQuery())->whereIn('type', [
            TypeConstants::CreatePTMember, TypeConstants::RenewPTMember, TypeConstants::EditPTMember,
            TypeConstants::CreatePTMemberPayAmountRemainingForm,
            TypeConstants::CreatePTSubscription, TypeConstants::EditPTSubscription,
        ])->sum('amount');
        $storeRevenue = (clone $mkQuery())->whereIn('type', [
            TypeConstants::CreateStoreOrder, TypeConstants::EditStoreOrder, TypeConstants::CashSale,
        ])->sum('amount');

        $newMembers    = GymMember::branch()
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count();
        $totalActive   = GymMemberSubscription::branch()->where('status', TypeConstants::Active)->count();
        $totalFrozen   = GymMemberSubscription::branch()->where('status', TypeConstants::Freeze)->count();
        $expiredPeriod = GymMemberSubscription::branch()->where('status', TypeConstants::Expired)
            ->whereDate('expire_date', '>=', $from)->whereDate('expire_date', '<=', $to)->count();
        $renewals      = (clone $mkQuery())->whereIn('type', [TypeConstants::RenewMember, TypeConstants::RenewPTMember])->count();
        $debtMembers   = GymMemberSubscription::branch()->where('amount_remaining', '>', 0)->where('status', TypeConstants::Active)->count();
        $totalDebt     = GymMemberSubscription::branch()->where('amount_remaining', '>', 0)->sum('amount_remaining');

        // Package names use the correct language column
        $nameCol = $this->lang === 'ar' ? 'name_ar' : 'name_en';
        $fallCol = $this->lang === 'ar' ? 'name_en' : 'name_ar';

        $topPackages = GymSubscription::branch()
            ->withCount(['member_subscriptions' => fn($q) => $q->where('status', TypeConstants::Active)])
            ->orderByDesc('member_subscriptions_count')->limit(5)->get()
            ->map(fn($s) => ($s->{$nameCol} ?: $s->{$fallCol}) . ' (' . $s->member_subscriptions_count . ' — ' . $s->price . ')')
            ->toArray();

        $weakPackages = GymSubscription::branch()
            ->withCount(['member_subscriptions' => fn($q) => $q->where('status', TypeConstants::Active)])
            ->having('member_subscriptions_count', '<=', 2)->orderBy('member_subscriptions_count')->limit(5)->get()
            ->map(fn($s) => ($s->{$nameCol} ?: $s->{$fallCol}) . ' (' . $s->member_subscriptions_count . ')')
            ->toArray();

        $attBase        = fn() => GymMemberAttendee::branch()
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
        $totalVisits    = (clone $attBase())->count();
        $uniqueVisitors = (clone $attBase())->distinct('member_id')->count('member_id');
        $peakHours      = (clone $attBase())->selectRaw('HOUR(created_at) as h, COUNT(*) as c')
            ->groupBy('h')->orderByDesc('c')->limit(3)->pluck('h')
            ->map(fn($h) => sprintf('%02d:00', $h))->implode(', ');
        $lowHours       = (clone $attBase())->selectRaw('HOUR(created_at) as h, COUNT(*) as c')
            ->groupBy('h')->orderBy('c')->limit(3)->pluck('h')
            ->map(fn($h) => sprintf('%02d:00', $h))->implode(', ');

        $currency       = $this->settings->currency ?? 'SAR';
        $churnRate      = ($totalActive + $expiredPeriod) > 0
            ? round($expiredPeriod / ($totalActive + $expiredPeriod) * 100, 1) : 0;
        $renewalRate    = $expiredPeriod > 0 ? round($renewals / $expiredPeriod * 100, 1) : 0;
        $avgMemberValue = $totalActive > 0 ? round($totalRevenue / $totalActive, 2) : 0;

        return [
            'gym_name'   => $this->gymName(),
            'currency'   => $currency,
            'period'     => ['from' => $from, 'to' => $to],
            'revenue'    => [
                'total'         => "{$totalRevenue} {$currency}",
                'subscriptions' => "{$subRevenue} {$currency}",
                'pt_sessions'   => "{$ptRevenue} {$currency}",
                'store'         => "{$storeRevenue} {$currency}",
            ],
            'members'    => [
                'new_in_period'     => $newMembers,
                'total_active'      => $totalActive,
                'total_frozen'      => $totalFrozen,
                'expired_in_period' => $expiredPeriod,
                'renewals'          => $renewals,
                'members_with_debt' => $debtMembers,
                'total_debt'        => "{$totalDebt} {$currency}",
            ],
            'kpis'       => [
                'churn_rate'       => "{$churnRate}%",
                'renewal_rate'     => "{$renewalRate}%",
                'avg_member_value' => "{$avgMemberValue} {$currency}",
            ],
            'packages'   => ['top' => $topPackages, 'weak' => $weakPackages],
            'attendance' => [
                'total_visits'          => $totalVisits,
                'unique_visitors'       => $uniqueVisitors,
                'avg_visits_per_member' => $uniqueVisitors > 0 ? round($totalVisits / $uniqueVisitors, 1) : 0,
                'inactive_members'      => max(0, $totalActive - $uniqueVisitors),
                'peak_hours'            => $peakHours ?: 'N/A',
                'low_hours'             => $lowHours  ?: 'N/A',
            ],
        ];
    }

    // =========================================================================
    //  PRIVATE — ChatGPT API Call
    // =========================================================================

    private function callChatGPT(array $gymData): array
    {
        $integrations = $this->settings->integrations ?? [];
        $apiKey       = $integrations['ai']['openai_key'] ?? null;
        $model        = $integrations['ai']['openai_model'] ?? 'gpt-4o';

        if (!$apiKey) {
            throw new \RuntimeException('OpenAI API key is not configured. Add it in Settings → Integrations → AI.');
        }

        $response = Http::withToken($apiKey)
            ->withoutVerifying()
            ->timeout(90)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'           => $model,
                'messages'        => [
                    [
                        'role'    => 'system',
                        'content' => $this->systemInstruction(),
                    ],
                    ['role' => 'user', 'content' => $this->buildPrompt($gymData)],
                ],
                'temperature'     => 0.3,
                'max_tokens'      => 2500,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (!$response->successful()) {
            Log::error('[GymAiReport] ChatGPT error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('ChatGPT API error: HTTP ' . $response->status());
        }

        $content = $response->json('choices.0.message.content', '{}');
        $parsed  = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse ChatGPT response.');
        }

        return $parsed ?? [];
    }

    private function systemInstruction(): string
    {
        if ($this->lang === 'ar') {
            return 'أنت مستشار استخباراتي أعمال رياضي أول. '
                 . 'أجب دائماً بـ JSON صحيح فقط — بدون markdown وبدون نص إضافي. '
                 . 'اكتب جميع قيم النصوص باللغة العربية الفصحى حصراً.';
        }

        return 'You are a senior gym business intelligence consultant. '
             . 'Always respond with strict valid JSON only — no markdown, no extra text. '
             . 'Write all text values in English.';
    }

    private function buildPrompt(array $gymData): string
    {
        $json = json_encode($gymData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $langInstruction = $this->lang === 'ar'
            ? 'مهم: اكتب جميع النصوص باللغة العربية الفصحى فقط.'
            : 'Write all text in English.';

        return <<<PROMPT
{$langInstruction}

Analyze the following gym performance data and generate a structured Executive AI Report.

Return STRICT JSON using exactly this structure:
{
  "executive_summary": "",
  "kpi_analysis": {
    "total_revenue": "",
    "renewal_rate": "",
    "new_members": "",
    "churn_rate": "",
    "average_member_value": ""
  },
  "top_packages": [],
  "weak_packages": [],
  "attendance_analysis": {
    "average_visits_per_member": "",
    "inactive_members": "",
    "peak_hours": "",
    "low_hours": ""
  },
  "sales_insights": [],
  "risk_alerts": [],
  "strategic_recommendations": [],
  "next_month_action_plan": []
}

Rules:
- Executive-level language only.
- Every insight must be data-driven.
- Highlight risks with urgency.
- Recommendations must be actionable and revenue-focused.

Gym Data:
{$json}
PROMPT;
    }

    // =========================================================================
    //  PRIVATE — Delivery
    // =========================================================================

    private function sendEmail(string $toEmail, array $report): bool
    {
        $html    = $this->buildEmailHtml($report);
        $lbl     = $this->lbl();
        $gymName = $this->gymName();
        $subject = $gymName . ' — ' . $lbl['subject'] . ' ' . now()->format('Y-m');

        try {
            Mail::send([], [], function ($m) use ($toEmail, $html, $subject, $gymName) {
                $m->to($toEmail)
                  ->from(config('mail.from.address', 'noreply@gymmawy.com'), $gymName)
                  ->subject($subject)
                  ->html($html);
            });
            return true;
        } catch (\Exception $e) {
            Log::error('[GymAiReport] Email error: ' . $e->getMessage());
            return false;
        }
    }

    private function sendSms(string $phone, array $report): bool
    {
        if (!($this->settings->active_sms ?? false) || !env('SMS_GATEWAY')) {
            return false;
        }

        try {
            $sms = SMSFactory::create(env('SMS_GATEWAY'));
            $sms->send($phone, $this->buildSmsText($report));
            return true;
        } catch (\Exception $e) {
            Log::error('[GymAiReport] SMS error: ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    //  PRIVATE — Email HTML Builder
    // =========================================================================

    private function buildEmailHtml(array $r): string
    {
        $lbl     = $this->lbl();
        $isAr    = $this->lang === 'ar';
        $gymName = e($this->gymName());
        $date    = now()->format('Y-m-d');
        $dir     = $lbl['dir'];
        $align   = $lbl['align'];
        $listPad = $lbl['list_padding'];
        $olPad   = $lbl['ol_padding'];

        $P = '#1A3A5C';
        $D = '#c0392b';
        $S = '#1e8449';
        $I = '#1a5276';

        // ── KPI Cards (2-column grid) ──────────────────────────────────────
        $kpiCards = '';
        if (!empty($r['kpi_analysis']) && is_array($r['kpi_analysis'])) {
            $cells = [];
            foreach ($r['kpi_analysis'] as $k => $v) {
                $label     = ucwords(str_replace('_', ' ', $k));
                $cardColor = str_contains($k, 'churn')
                    ? $D
                    : (str_contains($k, 'renewal') || str_contains($k, 'revenue') || str_contains($k, 'value') ? $S : $P);
                $cells[] = "<td class='colkpi' style='padding:0 5px 10px'>
                    <div style='background:#fff;border:1px solid #dde3eb;border-top:3px solid {$cardColor};border-radius:5px;padding:13px 12px;text-align:center'>
                      <p style='margin:0 0 6px;font-size:10px;color:#888;text-transform:uppercase;letter-spacing:0.6px'>{$label}</p>
                      <p style='margin:0;font-size:17px;font-weight:700;color:{$cardColor}'>" . e((string)$v) . "</p>
                    </div>
                  </td>";
            }
            $kpiHtml = '';
            foreach (array_chunk($cells, 2) as $row) {
                if (count($row) < 2) {
                    $row[] = '<td class="colkpi" style="padding:0 5px 10px"></td>';
                }
                $kpiHtml .= '<tr>' . implode('', $row) . '</tr>';
            }
            $kpiCards = "<table class='kpitable' width='100%' cellpadding='0' cellspacing='0' style='margin:-5px'>{$kpiHtml}</table>";
        }

        // ── Attendance table (alternating rows) ────────────────────────────
        $attRows = '';
        if (!empty($r['attendance_analysis']) && is_array($r['attendance_analysis'])) {
            $i = 0;
            foreach ($r['attendance_analysis'] as $k => $v) {
                $label   = ucwords(str_replace('_', ' ', $k));
                $rowBg   = ($i % 2 === 0) ? '#f8fafc' : '#fff';
                $attRows .= "<tr style='background:{$rowBg}'>
                    <td style='padding:9px 14px;color:#555;font-size:13px;text-align:{$align};width:56%'>{$label}</td>
                    <td style='padding:9px 14px;color:{$I};font-size:13px;font-weight:600;text-align:{$align}'>" . e((string)$v) . "</td>
                  </tr>";
                $i++;
            }
        }

        // ── List / OL builders ──────────────────────────────────────────────
        $ul = fn(array $items, string $color = '#444') =>
            "<ul style='margin:0;{$listPad}'>"
            . implode('', array_map(fn($i) => "<li style='color:{$color};font-size:13px;margin-bottom:6px;line-height:1.75'>" . e((string)$i) . '</li>', $items))
            . '</ul>';

        $ol = fn(array $items) =>
            "<ol style='margin:0;{$olPad}'>"
            . implode('', array_map(fn($i) => "<li style='color:#333;font-size:13px;margin-bottom:6px;line-height:1.75'>" . e((string)$i) . '</li>', $items))
            . '</ol>';

        // ── Block builder ───────────────────────────────────────────────────
        $block = fn(string $icon, string $title, string $content, string $color) =>
            "<div style='margin-bottom:18px;border-radius:6px;overflow:hidden;border:1px solid #dde3eb;box-shadow:0 1px 3px rgba(0,0,0,.06)'>
              <div style='background:{$color};padding:10px 16px;text-align:{$align}'>
                <span style='color:#fff;font-size:13px;font-weight:700'>{$icon}&nbsp;{$title}</span>
              </div>
              <div style='padding:16px;background:#fff;direction:{$dir}'>{$content}</div>
            </div>";

        $noData      = "<p style='color:#aaa;font-size:12px;margin:0'>{$lbl['no_data']}</p>";
        $accentSide  = $isAr ? 'right' : 'left';
        $body        = '';

        // Executive Summary — quoted accent style
        if (!empty($r['executive_summary'])) {
            $body .= $block('📋', $lbl['executive_summary'],
                "<p style='margin:0;color:#333;font-size:13px;line-height:1.9;border-{$accentSide}:3px solid {$P};padding-{$accentSide}:12px;background:#f8fafc;padding-top:8px;padding-bottom:8px'>"
                    . e($r['executive_summary']) . '</p>',
                $P);
        }

        // KPI Cards
        if ($kpiCards) {
            $body .= $block('📊', $lbl['kpi_analysis'], $kpiCards, $P);
        }

        // Attendance — styled alternating table
        if ($attRows) {
            $body .= $block('🏃', $lbl['attendance'],
                "<table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;border:1px solid #dde3eb;border-radius:4px'>{$attRows}</table>",
                $I);
        }

        // Top & Weak Packages — clean 2-column
        $topHtml  = !empty($r['top_packages'])  ? $ul((array)$r['top_packages'],  $S) : $noData;
        $weakHtml = !empty($r['weak_packages']) ? $ul((array)$r['weak_packages'], $D) : $noData;

        $body .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:18px'>
          <tr valign='top'>
            <td class='col2' width='50%' style='padding-right:8px'>
              <div style='border:1px solid #dde3eb;border-radius:6px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)'>
                <div style='background:{$S};padding:10px 16px;text-align:{$align}'>
                  <span style='color:#fff;font-size:13px;font-weight:700'>🏆&nbsp;{$lbl['top_packages']}</span>
                </div>
                <div style='padding:16px;background:#fff;direction:{$dir}'>{$topHtml}</div>
              </div>
            </td>
            <td class='col2' width='50%' style='padding-left:8px'>
              <div style='border:1px solid #dde3eb;border-radius:6px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)'>
                <div style='background:{$D};padding:10px 16px;text-align:{$align}'>
                  <span style='color:#fff;font-size:13px;font-weight:700'>⚠️&nbsp;{$lbl['weak_packages']}</span>
                </div>
                <div style='padding:16px;background:#fff;direction:{$dir}'>{$weakHtml}</div>
              </div>
            </td>
          </tr>
        </table>";

        if (!empty($r['sales_insights'])) {
            $body .= $block('💡', $lbl['sales_insights'], $ul((array)$r['sales_insights']), $I);
        }

        if (!empty($r['risk_alerts'])) {
            $body .= $block('🚨', $lbl['risk_alerts'], $ul((array)$r['risk_alerts'], $D), $D);
        }

        if (!empty($r['strategic_recommendations'])) {
            $body .= $block('🎯', $lbl['recommendations'], $ul((array)$r['strategic_recommendations'], $S), $S);
        }

        if (!empty($r['next_month_action_plan'])) {
            $body .= $block('📅', $lbl['action_plan'], $ol((array)$r['next_month_action_plan']), '#e67e22');
        }

        $font = $isAr
            ? "'Segoe UI', Tahoma, Arial, sans-serif"
            : "'Segoe UI', Arial, sans-serif";

        return <<<HTML
<!DOCTYPE html>
<html lang="{$this->lang}" dir="{$dir}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$lbl['subject']}</title>
<style>
@media only screen and (max-width:600px){
  .w640  { width:100%!important; }
  .hpad  { padding-left:16px!important; padding-right:16px!important; }
  .h1    { font-size:20px!important; }
  .kpitable { margin:0!important; }
  .colkpi{
    width:100%!important;
    display:block!important;
    box-sizing:border-box!important;
    padding:0 0 10px!important;
  }
  .col2{
    width:100%!important;
    display:block!important;
    box-sizing:border-box!important;
    padding:0 0 12px!important;
  }
}
</style>
</head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:{$font}">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:32px 16px">
    <tr>
      <td align="center">
        <table class="w640" width="640" cellpadding="0" cellspacing="0" style="max-width:640px;width:100%">

          <tr>
            <td class="hpad" style="background:{$P};padding:28px 32px;border-radius:8px 8px 0 0;text-align:{$align}">
              <p style="margin:0;color:#7fafd0;font-size:11px;text-transform:uppercase;letter-spacing:1.5px">{$lbl['report_subtitle']}</p>
              <h1 class="h1" style="margin:8px 0 4px;color:#fff;font-size:24px;font-weight:700">{$gymName}</h1>
              <p style="margin:0;color:#a0c4e0;font-size:12px">{$date}</p>
            </td>
          </tr>

          <tr>
            <td class="hpad" style="background:#f4f7fb;padding:24px 28px;direction:{$dir}">
              {$body}
            </td>
          </tr>

          <tr>
            <td style="background:#e8ecf1;padding:14px 20px;border-radius:0 0 8px 8px;text-align:center">
              <p style="margin:0;color:#999;font-size:11px">{$lbl['footer']} &bull; {$date}</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    // =========================================================================
    //  PRIVATE — SMS Text Builder
    // =========================================================================

    private function buildSmsText(array $r): string
    {
        $lbl   = $this->lbl();
        $gym   = $this->gymName();
        $date  = now()->format('Y-m-d');
        $lines = ["📊 {$gym} | {$lbl['sms_label']} {$date}", str_repeat('─', 28)];

        if (!empty($r['executive_summary'])) {
            $lines[] = mb_substr((string)$r['executive_summary'], 0, 220);
        }

        $kpi = $r['kpi_analysis'] ?? [];
        if (!empty($kpi['total_revenue']))   $lines[] = $lbl['revenue']    . $kpi['total_revenue'];
        if (!empty($kpi['renewal_rate']))    $lines[] = $lbl['renewal']    . $kpi['renewal_rate'];
        if (!empty($kpi['churn_rate']))      $lines[] = $lbl['churn']      . $kpi['churn_rate'];
        if (!empty($kpi['new_members']))     $lines[] = $lbl['new_members']. $kpi['new_members'];

        if (!empty($r['risk_alerts'])) {
            $lines[] = $lbl['risks_prefix'] . implode(' | ', array_slice((array)$r['risk_alerts'], 0, 2));
        }

        if (!empty($r['next_month_action_plan'])) {
            $lines[] = $lbl['actions_prefix'] . implode(' | ', array_slice((array)$r['next_month_action_plan'], 0, 2));
        }

        return implode("\n", $lines);
    }
}
