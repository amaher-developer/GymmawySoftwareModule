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
        $lbl      = $this->lbl();
        $isAr     = $this->lang === 'ar';
        $gymName  = e($this->gymName());
        $date     = now()->format('Y-m-d');
        $dir      = $lbl['dir'];
        $align    = $lbl['align'];
        $alignOpp = $isAr ? 'left' : 'right';
        $font     = $isAr
            ? "'Tahoma', 'Arabic Typesetting', 'Segoe UI', Arial, sans-serif"
            : "'Segoe UI', 'Helvetica Neue', Arial, sans-serif";

        // ── Premium palette ────────────────────────────────────────────
        $navy    = '#0B1E37';
        $navyMid = '#112240';
        $gold    = '#C9A84C';
        $white   = '#FFFFFF';
        $bodyBg  = '#E8EDF4';
        $cardBg  = '#FFFFFF';
        $altRow  = '#F4F8FC';
        $text1   = '#1A2B3C';
        $text2   = '#5B7A99';
        $blueMut = '#7A9FBE';
        $success = '#1B6B43';
        $danger  = '#A62828';
        $info    = '#1B4F8A';
        $orange  = '#B86A1A';

        // ── Pill section-badge ─────────────────────────────────────────
        // Returns a small full-color label pill above each section
        $mkPill = function (string $icon, string $title, string $bg) use ($font, $white): string {
            return "<table cellpadding='0' cellspacing='0' style='margin-bottom:14px'>"
                 . "<tr><td bgcolor='{$bg}' style='border-radius:4px;padding:5px 14px'>"
                 . "<span style='color:{$white};font-family:{$font};font-size:10px;"
                 . "font-weight:700;letter-spacing:2px;text-transform:uppercase'>"
                 . "{$icon}&nbsp;&nbsp;{$title}</span>"
                 . "</td></tr></table>";
        };

        // ── Bullet list item ───────────────────────────────────────────
        $mkLi = function (string $text, string $dot) use ($font, $text1, $align, $isAr): string {
            $pl = $isAr ? '0' : '10';
            $pr = $isAr ? '10' : '0';
            return "<table cellpadding='0' cellspacing='0' style='margin-bottom:8px;width:100%'><tr>"
                 . "<td width='16' valign='top' style='padding-top:6px'>"
                 . "<table cellpadding='0' cellspacing='0'><tr>"
                 . "<td bgcolor='{$dot}' style='width:7px;height:7px;border-radius:50%;font-size:0;line-height:0'>&nbsp;</td>"
                 . "</tr></table></td>"
                 . "<td style='padding-left:{$pl}px;padding-right:{$pr}px'>"
                 . "<span style='color:{$text1};font-family:{$font};font-size:13px;"
                 . "line-height:1.75;display:block;text-align:{$align}'>{$text}</span>"
                 . "</td></tr></table>";
        };

        // ── Card wrapper (top-border accent) ────────────────────────────
        $mkCard = function (string $pill, string $content, string $topColor) use ($cardBg): string {
            return "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:24px'>"
                 . "<tr><td bgcolor='{$cardBg}' style='border-radius:8px;padding:22px 24px;"
                 . "border-top:3px solid {$topColor}'>{$pill}{$content}</td></tr></table>";
        };

        $noData = "<p style='color:#AABCCE;font-family:{$font};font-size:12px;margin:0'>{$lbl['no_data']}</p>";

        // ══════════════════════════════════════════════════════════════
        //  BUILD BODY
        // ══════════════════════════════════════════════════════════════
        $body = '';

        // ── Executive Summary ──────────────────────────────────────────
        if (!empty($r['executive_summary'])) {
            $bSide = $isAr ? 'border-right' : 'border-left';
            $text  = e((string) $r['executive_summary']);
            $body   .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:28px'>"
                     . "<tr><td bgcolor='{$cardBg}' style='border-radius:8px;padding:22px 26px;"
                     . "{$bSide}:4px solid {$gold}'>"
                     . $mkPill('📋', $lbl['executive_summary'], $navy)
                     . "<p style='margin:0;color:{$text1};font-family:{$font};font-size:14px;"
                     . "line-height:1.9;text-align:{$align}'>{$text}</p>"
                     . "</td></tr></table>";
        }

        // ── KPI Cards (2-column dark grid) ─────────────────────────────
        if (!empty($r['kpi_analysis']) && is_array($r['kpi_analysis'])) {
            $items = [];
            foreach ($r['kpi_analysis'] as $k => $v) {
                $items[] = ['label' => ucwords(str_replace('_', ' ', $k)), 'value' => (string) $v];
            }
            $kpiRows = '';
            foreach (array_chunk($items, 2) as $pair) {
                $kpiRows .= '<tr>';
                foreach ($pair as $item) {
                    $kpiRows .= "<td width='48%' valign='top' bgcolor='{$navyMid}'"
                              . " style='border-radius:8px;padding:20px 22px'>"
                              . "<p style='margin:0 0 8px;color:{$blueMut};font-family:{$font};"
                              . "font-size:10px;font-weight:700;letter-spacing:1.5px;"
                              . "text-transform:uppercase;text-align:{$align}'>"
                              . e($item['label']) . "</p>"
                              . "<p style='margin:0;color:{$gold};font-family:{$font};"
                              . "font-size:22px;font-weight:800;line-height:1.1;text-align:{$align}'>"
                              . e($item['value']) . "</p></td>";
                }
                if (count($pair) === 1) {
                    $kpiRows .= "<td width='48%'>&nbsp;</td>";
                }
                $kpiRows .= "</tr><tr><td colspan='3' height='10' style='font-size:0;line-height:0'>&nbsp;</td></tr>";
            }
            $body .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:28px'>"
                   . "<tr><td colspan='3'>" . $mkPill('📊', $lbl['kpi_analysis'], $navy) . "</td></tr>"
                   . "<tr><td colspan='3' bgcolor='{$navy}' style='border-radius:10px;padding:22px'>"
                   . "<table width='100%' cellpadding='0' cellspacing='12'>{$kpiRows}</table>"
                   . "</td></tr></table>";
        }

        // ── Attendance table ───────────────────────────────────────────
        if (!empty($r['attendance_analysis']) && is_array($r['attendance_analysis'])) {
            $attRows = '';
            $i       = 0;
            foreach ($r['attendance_analysis'] as $k => $v) {
                $rowBg    = $i % 2 === 0 ? $white : $altRow;
                $attRows .= "<tr>"
                          . "<td bgcolor='{$rowBg}' style='padding:12px 16px;"
                          . "border-bottom:1px solid #DDE9F5;text-align:{$align}'>"
                          . "<span style='color:{$text2};font-family:{$font};font-size:11px;"
                          . "font-weight:700;text-transform:uppercase;letter-spacing:0.8px'>"
                          . e(ucwords(str_replace('_', ' ', $k))) . "</span></td>"
                          . "<td bgcolor='{$rowBg}' style='padding:12px 16px;"
                          . "border-bottom:1px solid #DDE9F5;text-align:{$alignOpp}'>"
                          . "<span style='color:{$info};font-family:{$font};font-size:14px;"
                          . "font-weight:700'>" . e((string) $v) . "</span></td>"
                          . "</tr>";
                $i++;
            }
            $attTable = "<table width='100%' cellpadding='0' cellspacing='0'"
                      . " style='border-collapse:collapse;border-radius:8px;overflow:hidden;"
                      . "border:1px solid #DDE9F5'>{$attRows}</table>";
            $body    .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:28px'>"
                      . "<tr><td>" . $mkPill('🏃', $lbl['attendance'], $info) . "</td></tr>"
                      . "<tr><td>{$attTable}</td></tr></table>";
        }

        // ── Packages (top / weak side-by-side) ────────────────────────
        $topItems  = !empty($r['top_packages'])  ? (array) $r['top_packages']  : [];
        $weakItems = !empty($r['weak_packages']) ? (array) $r['weak_packages'] : [];

        $topHtml  = $topItems  ? implode('', array_map(fn ($i) => $mkLi(e((string) $i), $success), $topItems))  : $noData;
        $weakHtml = $weakItems ? implode('', array_map(fn ($i) => $mkLi(e((string) $i), $danger),  $weakItems)) : $noData;

        $body .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:28px'><tr>"
               . "<td width='48%' valign='top' bgcolor='{$cardBg}' style='border-radius:8px;"
               . "padding:20px 22px;border-top:3px solid {$success}'>"
               . $mkPill('🏆', $lbl['top_packages'], $success) . $topHtml . "</td>"
               . "<td width='4%'>&nbsp;</td>"
               . "<td width='48%' valign='top' bgcolor='{$cardBg}' style='border-radius:8px;"
               . "padding:20px 22px;border-top:3px solid {$danger}'>"
               . $mkPill('⚠️', $lbl['weak_packages'], $danger) . $weakHtml . "</td>"
               . "</tr></table>";

        // ── Sales Insights ─────────────────────────────────────────────
        if (!empty($r['sales_insights'])) {
            $items = implode('', array_map(fn ($i) => $mkLi(e((string) $i), $info), (array) $r['sales_insights']));
            $body .= $mkCard($mkPill('💡', $lbl['sales_insights'], $info), $items, $info);
        }

        // ── Risk Alerts ────────────────────────────────────────────────
        if (!empty($r['risk_alerts'])) {
            $items = implode('', array_map(fn ($i) => $mkLi(e((string) $i), $danger), (array) $r['risk_alerts']));
            $body .= $mkCard($mkPill('🚨', $lbl['risk_alerts'], $danger), $items, $danger);
        }

        // ── Strategic Recommendations ──────────────────────────────────
        if (!empty($r['strategic_recommendations'])) {
            $items = implode('', array_map(fn ($i) => $mkLi(e((string) $i), $success), (array) $r['strategic_recommendations']));
            $body .= $mkCard($mkPill('🎯', $lbl['recommendations'], $success), $items, $success);
        }

        // ── Action Plan (numbered circles) ─────────────────────────────
        if (!empty($r['next_month_action_plan'])) {
            $pl   = $isAr ? '0' : '12';
            $pr   = $isAr ? '12' : '0';
            $nums = '';
            $n    = 1;
            foreach ((array) $r['next_month_action_plan'] as $item) {
                $nums .= "<table cellpadding='0' cellspacing='0' style='margin-bottom:12px;width:100%'><tr>"
                       . "<td width='32' valign='top'>"
                       . "<table cellpadding='0' cellspacing='0'><tr>"
                       . "<td bgcolor='{$orange}' style='width:26px;height:26px;border-radius:50%;"
                       . "text-align:center;vertical-align:middle'>"
                       . "<span style='color:{$white};font-family:{$font};font-size:11px;"
                       . "font-weight:800;line-height:26px;display:block;text-align:center'>{$n}</span>"
                       . "</td></tr></table></td>"
                       . "<td valign='middle' style='padding-left:{$pl}px;padding-right:{$pr}px'>"
                       . "<span style='color:{$text1};font-family:{$font};font-size:13px;"
                       . "line-height:1.75;text-align:{$align}'>" . e((string) $item) . "</span>"
                       . "</td></tr></table>";
                $n++;
            }
            $body .= $mkCard($mkPill('📅', $lbl['action_plan'], $orange), $nums, $orange);
        }

        // ══════════════════════════════════════════════════════════════
        //  FINAL HTML
        // ══════════════════════════════════════════════════════════════
        $badgeText = $isAr
            ? '✦ تقرير الذكاء الاصطناعي التنفيذي'
            : '✦ AI EXECUTIVE REPORT';
        $poweredBy = $isAr
            ? "بتقنية Gymmawy AI &nbsp;|&nbsp; {$date}"
            : "Powered by Gymmawy AI &nbsp;|&nbsp; {$date}";

        return <<<HTML
<!DOCTYPE html>
<html lang="{$this->lang}" dir="{$dir}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$lbl['subject']}</title>
</head>
<body style="margin:0;padding:0;background:{$bodyBg}">

<table width="100%" cellpadding="0" cellspacing="0" bgcolor="{$bodyBg}">
<tr><td align="center" style="padding:40px 16px">

  <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">

    <!-- ══ HEADER ══════════════════════════════════════════════════ -->
    <tr>
      <td bgcolor="{$navy}" style="border-radius:12px 12px 0 0;padding:38px 40px 30px;direction:{$dir}">

        <!-- AI badge -->
        <table cellpadding="0" cellspacing="0" style="margin-bottom:20px">
          <tr>
            <td bgcolor="{$gold}" style="border-radius:4px;padding:5px 16px">
              <span style="color:{$navy};font-family:{$font};font-size:10px;font-weight:800;letter-spacing:2px;text-transform:uppercase">{$badgeText}</span>
            </td>
          </tr>
        </table>

        <h1 style="margin:0 0 8px;color:{$white};font-family:{$font};font-size:28px;font-weight:800;line-height:1.2;text-align:{$align}">{$gymName}</h1>
        <p style="margin:0;color:{$blueMut};font-family:{$font};font-size:13px;text-align:{$align}">{$poweredBy}</p>

        <!-- Gold rule -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px">
          <tr><td bgcolor="{$gold}" height="2" style="font-size:0;line-height:0;border-radius:1px">&nbsp;</td></tr>
        </table>
      </td>
    </tr>

    <!-- ══ BODY ════════════════════════════════════════════════════ -->
    <tr>
      <td bgcolor="{$bodyBg}" style="padding:32px 36px;direction:{$dir}">
        {$body}
      </td>
    </tr>

    <!-- ══ FOOTER ══════════════════════════════════════════════════ -->
    <tr>
      <td bgcolor="{$navy}" style="border-radius:0 0 12px 12px;padding:18px 40px;text-align:center">
        <p style="margin:0;color:{$blueMut};font-family:{$font};font-size:11px">{$lbl['footer']} &bull; {$date}</p>
      </td>
    </tr>

  </table>

</td></tr>
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
