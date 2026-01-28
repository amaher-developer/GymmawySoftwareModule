<?php

namespace Modules\Software\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Generic\Models\Setting;
use Modules\Software\Classes\SMSFactory;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Classes\WA;
use Modules\Software\Classes\WAUltramsg;
use Modules\Software\Models\GymEventNotification;
use Modules\Software\Models\GymMemberSubscription;

class NotificationService
{
    protected $mainSettings;

    public function __construct()
    {
        $this->mainSettings = Setting::first();
    }

    /**
     * Send notification for a specific event
     *
     * @param string $eventCode The event code (new_member, renew_member, before_expired_member, expired_member, etc.)
     * @param GymMemberSubscription $membership The member subscription
     * @param string|null $phone Override phone number (optional)
     * @param int|null $branchSettingId Branch setting ID (optional)
     * @return array Result with success status and messages
     */
    public function sendEventNotification(string $eventCode, GymMemberSubscription $membership, ?string $phone = null, ?int $branchSettingId = null): array
    {
        $result = [
            'success' => false,
            'sms_sent' => false,
            'wa_sent' => false,
            'message' => '',
        ];

        // Get event notification settings
        $query = GymEventNotification::where('event_code', $eventCode)->where('status', 1);
        if ($branchSettingId) {
            $query->where('branch_setting_id', $branchSettingId);
        }
        $eventNotification = $query->first();

        if (!$eventNotification) {
            $result['message'] = "Event notification '{$eventCode}' not found or disabled";
            return $result;
        }

        // Get member phone
        $memberPhone = $phone ?? $membership->member->phone ?? null;
        if (!$memberPhone) {
            $result['message'] = 'No phone number available';
            return $result;
        }

        // Build the message
        $msg = $this->dynamicMsg($eventNotification->message, $membership, $this->mainSettings);

        // Send SMS
        if ($this->mainSettings->active_sms && env('SMS_GATEWAY')) {
            $result['sms_sent'] = $this->sendSMS($memberPhone, $msg, $membership->member->id ?? null);
        }

        // Send WhatsApp via Ultramsg
        if ($this->mainSettings->active_wa && env('WA_GATEWAY') == 'ULTRA') {
            $result['wa_sent'] = $this->sendWhatsAppUltra($memberPhone, $msg, $membership->member->id ?? null);
        }

        // Send WhatsApp via WA Token
        if ($this->mainSettings->active_wa && env('WA_USER_TOKEN')) {
            $result['wa_sent'] = $this->sendWhatsAppToken($memberPhone, $msg, $membership->member->id ?? null);
        }

        $result['success'] = $result['sms_sent'] || $result['wa_sent'];
        $result['message'] = $result['success'] ? 'Notification sent successfully' : 'Failed to send notification';

        return $result;
    }

    /**
     * Send SMS message
     */
    public function sendSMS(string $phone, string $message, ?int $memberId = null): bool
    {
        try {
            $sms = new SMSFactory(env('SMS_GATEWAY'));
            $sms->send(trim($phone), $message);
            Log::info('SMS sent successfully', [
                'member_id' => $memberId,
                'phone' => $phone,
                'message' => $message
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'member_id' => $memberId,
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send WhatsApp message via Ultramsg
     */
    public function sendWhatsAppUltra(string $phone, string $message, ?int $memberId = null): bool
    {
        try {
            $wa = new WAUltramsg();
            $wa->sendText(trim($phone), $message);
            Log::info('WhatsApp message sent successfully (Ultra)', [
                'member_id' => $memberId,
                'phone' => $phone,
                'message' => $message
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message (Ultra)', [
                'member_id' => $memberId,
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send WhatsApp message via WA Token
     */
    public function sendWhatsAppToken(string $phone, string $message, ?int $memberId = null): bool
    {
        try {
            $wa = new WA();
            $wa->sendText(trim($phone), $message);
            Log::info('WhatsApp message sent successfully (Token)', [
                'member_id' => $memberId,
                'phone' => $phone,
                'message' => $message
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message (Token)', [
                'member_id' => $memberId,
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Replace dynamic variables in message
     */
    public function dynamicMsg(string $msg = '', ?GymMemberSubscription $membership = null, ?Setting $setting = null): string
    {
        if (!$membership) {
            return $msg;
        }

        $dynamic_variables = [
            '#member_name' => $membership->member->name ?? '',
            '#member_code' => (int)($membership->member->code ?? 0),
            '#member_phone' => $membership->member->phone ?? '',
            '#membership_start_date' => $membership->joining_date ? Carbon::parse($membership->joining_date)->addHours(12)->toDateString() : '',
            '#membership_expire_date' => $membership->expire_date ? Carbon::parse($membership->expire_date)->toDateString() : '',
            '#membership_amount_paid' => $membership->amount_paid ?? 0,
            '#membership_amount_remaining' => $membership->amount_remaining ?? 0,
            '#membership_name' => $membership->subscription->name ?? '',
            '#setting_phone' => $setting->phone ?? '',
            '#setting_name' => $setting->name ?? '',
            '#days_remaining' => $membership->expire_date ? Carbon::now()->diffInDays(Carbon::parse($membership->expire_date), false) : 0,
        ];

        foreach ($dynamic_variables as $key => $value) {
            $msg = str_replace($key, $value, $msg);
        }

        return $msg;
    }

    /**
     * Get members with expiring memberships within X days
     */
    public function getExpiringMemberships(int $days = 3, ?int $branchSettingId = null)
    {
        $query = GymMemberSubscription::with(['member', 'subscription'])
            ->whereDate('expire_date', '=', Carbon::now()->addDays($days)->toDateString())
            ->where('status', TypeConstants::Active); // Active memberships only

        if ($branchSettingId) {
            $query->where('branch_setting_id', $branchSettingId);
        }

        return $query->get();
    }

    /**
     * Get members with expired memberships (expired today)
     */
    public function getExpiredMemberships(?int $branchSettingId = null)
    {
        $query = GymMemberSubscription::with(['member', 'subscription'])
            ->whereDate('expire_date', '=', Carbon::now()->subDay()->toDateString())
            ->where('status', TypeConstants::Active);

        if ($branchSettingId) {
            $query->where('branch_setting_id', $branchSettingId);
        }

        return $query->get();
    }

    /**
     * Send notifications to all expiring memberships
     */
    public function sendExpiringNotifications(int $days = 3, ?int $branchSettingId = null): array
    {
        $memberships = $this->getExpiringMemberships($days, $branchSettingId);
        $results = [
            'total' => $memberships->count(),
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($memberships as $membership) {
            $result = $this->sendEventNotification('before_expired_member', $membership, null, $branchSettingId);
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
            $results['details'][] = [
                'member_id' => $membership->member->id ?? null,
                'member_name' => $membership->member->name ?? null,
                'expire_date' => $membership->expire_date,
                'result' => $result
            ];
        }

        return $results;
    }

    /**
     * Send notifications to all expired memberships and update status to Expired
     */
    public function sendExpiredNotifications(?int $branchSettingId = null): array
    {
        $memberships = $this->getExpiredMemberships($branchSettingId);
        $results = [
            'total' => $memberships->count(),
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($memberships as $membership) {
            $result = $this->sendEventNotification('expired_member', $membership, null, $branchSettingId);
            if ($result['success']) {
                $results['success']++;
                // Update membership status to Expired after successful notification
                $membership->status = TypeConstants::Expired;
                $membership->save();
            } else {
                $results['failed']++;
            }
            $results['details'][] = [
                'member_id' => $membership->member->id ?? null,
                'member_name' => $membership->member->name ?? null,
                'expire_date' => $membership->expire_date,
                'result' => $result
            ];
        }

        return $results;
    }

    /**
     * Get members with active freeze that ends today (to be unfrozen)
     */
    public function getUnfreezingMemberships(?int $branchSettingId = null)
    {
        $query = GymMemberSubscription::with(['member', 'subscription'])
            ->whereDate('end_freeze_date', '=', Carbon::now()->toDateString())
            ->where('status', TypeConstants::Freeze);

        if ($branchSettingId) {
            $query->where('branch_setting_id', $branchSettingId);
        }

        return $query->get();
    }

    /**
     * Send notifications to all unfreezing memberships and update status
     */
    public function sendUnfreezeNotifications(?int $branchSettingId = null): array
    {
        $memberships = $this->getUnfreezingMemberships($branchSettingId);
        $results = [
            'total' => $memberships->count(),
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($memberships as $membership) {
            $result = $this->sendEventNotification('unfreeze_member', $membership, null, $branchSettingId);

            // Update freeze status regardless of notification result
            // The freeze period has ended, so we need to update the status
            $membership->end_freeze_date = Carbon::now();

            // Update the active freeze record
            $activeFreeze = \Modules\Software\Models\GymMemberSubscriptionFreeze::where('member_subscription_id', $membership->id)
                ->whereIn('status', ['active', 'approved'])
                ->orderBy('id', 'desc')
                ->first();

            if ($activeFreeze) {
                $activeFreeze->end_date = Carbon::now()->toDateString();
                $activeFreeze->status = 'completed';
                $activeFreeze->save();
            }

            // Update membership status based on expire date
            $expireDate = Carbon::parse($membership->expire_date)->toDateString();
            $joiningDate = Carbon::parse($membership->joining_date)->toDateString();
            $currentDate = Carbon::now()->toDateString();

            if ($expireDate < $currentDate) {
                $membership->status = TypeConstants::Expired;
            } elseif ($expireDate >= $currentDate && $joiningDate > $currentDate) {
                $membership->status = TypeConstants::Coming;
            } else {
                $membership->status = TypeConstants::Active;
            }

            $membership->save();

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'member_id' => $membership->member->id ?? null,
                'member_name' => $membership->member->name ?? null,
                'expire_date' => $membership->expire_date,
                'end_freeze_date' => $membership->end_freeze_date,
                'new_status' => $membership->status,
                'result' => $result
            ];
        }

        return $results;
    }
}
