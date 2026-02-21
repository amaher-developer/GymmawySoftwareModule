<?php

namespace Modules\Software\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * GymAiReport — Generic AI report log model.
 *
 * Stores any type of AI-generated report (executive, sales, members, …)
 * from any AI method (chatgpt, n8n, …) with full delivery tracking.
 *
 * Uses plain Model (no SoftDeletes) — sw_ai_reports is a log table.
 *
 * @property int         $id
 * @property int         $branch_setting_id
 * @property string      $type          executive | sales | members | attendance
 * @property string      $method        chatgpt | n8n | …
 * @property string|null $model_used    gpt-4o | …
 * @property string      $lang          ar | en
 * @property string|null $from_date
 * @property string|null $to_date
 * @property array|null  $gym_data      Raw KPI data sent to AI
 * @property array|null  $report        Structured AI JSON report
 * @property bool        $email_sent
 * @property string|null $email_sent_to
 * @property string|null $email_sent_at
 * @property bool        $sms_sent
 * @property string|null $sms_sent_to
 * @property string|null $sms_sent_at
 */
class GymAiReport extends Model
{
    protected $table    = 'sw_ai_reports';
    protected $guarded  = ['id'];

    protected $casts = [
        'gym_data'       => 'array',
        'report'         => 'array',
        'email_sent'     => 'boolean',
        'sms_sent'       => 'boolean',
        'email_sent_at'  => 'datetime',
        'sms_sent_at'    => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', auth()->user()->branch_setting_id ?? 1);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDelivered($query)
    {
        return $query->where('email_sent', true)->orWhere('sms_sent', true);
    }

    public function scopeUndelivered($query)
    {
        return $query->where('email_sent', false)->where('sms_sent', false);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function markEmailSent(string $email): void
    {
        $this->update([
            'email_sent'    => true,
            'email_sent_to' => $email,
            'email_sent_at' => now(),
        ]);
    }

    public function markSmsSent(string $phone): void
    {
        $this->update([
            'sms_sent'    => true,
            'sms_sent_to' => $phone,
            'sms_sent_at' => now(),
        ]);
    }
}
