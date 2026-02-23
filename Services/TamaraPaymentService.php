<?php

namespace Modules\Software\Services;

use Modules\Generic\Http\Controllers\Front\TamaraFrontController;
use Modules\Generic\Models\Setting;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymOnlinePaymentInvoice;
use Modules\Software\Classes\WAUltramsg;
use Modules\Software\Classes\SMSFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TamaraPaymentService
{
    protected $tamaraController;
    protected $isEnabled;
    protected $currency;
    protected $settings;

    public function __construct()
    {
        $this->settings = Setting::branch()->first();
        $this->isEnabled = $this->isTamaraConfigured();
        $tamara = $this->settings ? ($this->settings->payments['tamara'] ?? []) : [];
        $this->currency = $tamara['currency'] ?? 'SAR';

        if ($this->isEnabled) {
            $this->tamaraController = new TamaraFrontController();
        }
    }

    /**
     * Check if Tamara is properly configured
     */
    public function isTamaraConfigured(): bool
    {
        $tamara = $this->settings ? ($this->settings->payments['tamara'] ?? []) : [];
        return !empty($tamara['token']);
    }

    /**
     * Check if Tamara payment should be offered
     */
    public function shouldOfferTamaraPayment(float $amountPaid): bool
    {
        if (!$this->isEnabled) {
            return false;
        }

        $tamara = $this->settings ? ($this->settings->payments['tamara'] ?? []) : [];
        $minimumAmount = (float) ($tamara['minimum_amount'] ?? 1);

        return $amountPaid >= $minimumAmount;
    }

    /**
     * Generate Tamara payment link for member subscription
     */
    public function generatePaymentLink(
        GymMember $member,
        GymMemberSubscription $memberSubscription,
        GymSubscription $subscription,
        float $amountPaid,
        ?int $branchSettingId = null
    ): array {
        if (!$this->isEnabled) {
            return [
                'success' => false,
                'payment_url' => null,
                'error' => 'Tamara is not configured',
            ];
        }

        try {
            $subscriptionName = app()->getLocale() === 'ar'
                ? ($subscription->name_ar ?? $subscription->name_en)
                : ($subscription->name_en ?? $subscription->name_ar);

            $duration = $subscription->period ?? 30;
            $expireDate = $memberSubscription->expire_date
                ? Carbon::parse($memberSubscription->expire_date)
                : Carbon::now()->addDays($duration);

            $result = $this->tamaraController->createMemberPaymentLink([
                'amount' => $amountPaid,
                'currency' => $this->currency,
                'subscription_name' => $subscriptionName,
                'duration' => $duration,
                'member_id' => $member->id,
                'subscription_id' => $subscription->id,
                'member_subscription_id' => $memberSubscription->id,
                'branch_setting_id' => $branchSettingId ?? $memberSubscription->branch_setting_id,
                'member_name' => $member->name,
                'member_phone' => $this->formatPhoneNumber($member->phone ?? ''),
                'member_email' => $member->email ?? 'member@gym.com',
                'city' => $member->city ?? 'Riyadh',
                'address' => $member->address ?? '',
                'success_url' => route('tamara.member.success'),
                'cancel_url' => route('tamara.member.cancel'),
                'failure_url' => route('tamara.member.failure'),
            ]);

            if ($result['success']) {
                $invoice = GymOnlinePaymentInvoice::create([
                    'branch_setting_id' => $branchSettingId ?? $memberSubscription->branch_setting_id,
                    'member_id' => $member->id,
                    'subscription_id' => $subscription->id,
                    'member_subscription_id' => $memberSubscription->id,
                    'payment_id' => $result['order_id'] ?? null,
                    'transaction_id' => null,
                    'status' => TypeConstants::PENDING,
                    'payment_method' => TypeConstants::TAMARA_TRANSACTION,
                    'payment_channel' => TypeConstants::CHANNEL_SYSTEM,
                    'amount' => $amountPaid,
                    'name' => $member->name,
                    'phone' => $member->phone,
                    'email' => $member->email,
                    'address' => $member->address,
                    'gender' => $member->gender,
                    'dob' => $member->dob,
                    'response_code' => json_encode([
                        'payment_url' => $result['payment_url'],
                        'order_id' => $result['order_id'] ?? null,
                    ]),
                ]);

                $result['invoice_id'] = $invoice->id;

                Log::info('Tamara payment link generated', [
                    'member_id' => $member->id,
                    'subscription_id' => $subscription->id,
                    'amount' => $amountPaid,
                    'payment_url' => $result['payment_url'],
                    'invoice_id' => $invoice->id,
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to generate Tamara payment link', [
                'member_id' => $member->id,
                'subscription_id' => $subscription->id,
                'amount' => $amountPaid,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'payment_url' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send Tamara payment link to member via WhatsApp/SMS
     */
    public function sendPaymentLinkToMember(
        GymMember $member,
        string $paymentUrl,
        GymSubscription $subscription,
        float $amountPaid,
        $mainSettings
    ): array {
        $results = ['whatsapp' => false, 'sms' => false, 'email' => false];

        $subscriptionName = app()->getLocale() === 'ar'
            ? ($subscription->name_ar ?? $subscription->name_en)
            : ($subscription->name_en ?? $subscription->name_ar);

        $message = $this->buildPaymentMessage(
            $member->name,
            $subscriptionName,
            $amountPaid,
            $paymentUrl,
            $mainSettings
        );

        // Send via WhatsApp
        if (!empty($member->phone) && @$mainSettings->active_wa && @env('WA_GATEWAY') == 'ULTRA') {
            $phone = trim($member->phone);
            try {
                $wa = new WAUltramsg();
                $wa->sendText($phone, $message);
                $results['whatsapp'] = true;

                Log::info('Tamara payment link sent via WhatsApp', [
                    'member_id' => $member->id,
                    'phone' => $phone,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send Tamara payment link via WhatsApp', [
                    'member_id' => $member->id,
                    'phone' => $phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send via SMS
        if (!empty($member->phone) && @$mainSettings->active_sms && @env('SMS_GATEWAY')) {
            $phone = trim($member->phone);
            try {
                $sms = new SMSFactory(@env('SMS_GATEWAY'));
                $sms->send($phone, $message);
                $results['sms'] = true;

                Log::info('Tamara payment link sent via SMS', [
                    'member_id' => $member->id,
                    'phone' => $phone,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send Tamara payment link via SMS', [
                    'member_id' => $member->id,
                    'phone' => $phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send via Email
        if (!empty($member->email)) {
            try {
                $emailData = $this->buildPaymentEmailData(
                    $member,
                    $subscriptionName,
                    $amountPaid,
                    $paymentUrl,
                    $mainSettings
                );

                Mail::send('software::emails.tamara_payment', $emailData, function ($mail) use ($member, $mainSettings, $emailData) {
                    $mail->from(
                        $mainSettings->email ?? 'noreply@gymmawy.com',
                        $mainSettings->name_en ?? $mainSettings->name_ar ?? 'Gym'
                    );
                    $mail->to($member->email, $member->name);
                    $mail->subject($emailData['subject']);
                });

                $results['email'] = true;

                Log::info('Tamara payment link sent via Email', [
                    'member_id' => $member->id,
                    'email' => $member->email,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send Tamara payment link via Email', [
                    'member_id' => $member->id,
                    'email' => $member->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Generate and send Tamara payment link for new member registration
     */
    public function processNewMemberPayment(
        GymMember $member,
        int $memberSubscriptionId,
        GymSubscription $subscription,
        float $amountPaid,
        $mainSettings,
        ?int $branchSettingId = null
    ): array {
        $memberSubscription = GymMemberSubscription::find($memberSubscriptionId);

        if (!$memberSubscription) {
            return ['success' => false, 'reason' => 'subscription_not_found'];
        }

        $linkResult = $this->generatePaymentLink(
            $member,
            $memberSubscription,
            $subscription,
            $amountPaid,
            $branchSettingId
        );

        if (!$linkResult['success']) {
            return [
                'success' => false,
                'reason' => 'link_generation_failed',
                'error' => $linkResult['error'],
            ];
        }

        $sendResult = $this->sendPaymentLinkToMember(
            $member,
            $linkResult['payment_url'],
            $subscription,
            $amountPaid,
            $mainSettings
        );

        return [
            'success' => true,
            'payment_url' => $linkResult['payment_url'],
            'sent_whatsapp' => $sendResult['whatsapp'],
            'sent_sms' => $sendResult['sms'],
            'sent_email' => $sendResult['email'],
        ];
    }

    /**
     * Generate and send Tamara payment link for membership renewal
     */
    public function processRenewalPayment(
        $member,
        $memberSubscriptionId,
        $subscription,
        $amountPaid,
        $mainSettings,
        $branchSettingId
    ): array {
        return $this->processNewMemberPayment(
            $member,
            $memberSubscriptionId,
            $subscription,
            $amountPaid,
            $mainSettings,
            $branchSettingId
        );
    }

    /**
     * Build payment message for WhatsApp/SMS
     */
    protected function buildPaymentMessage(
        string $memberName,
        string $subscriptionName,
        float $amount,
        string $paymentUrl,
        $mainSettings
    ): string {
        $gymName = app()->getLocale() === 'ar'
            ? ($mainSettings->name_ar ?? $mainSettings->name_en ?? 'Gym')
            : ($mainSettings->name_en ?? $mainSettings->name_ar ?? 'Gym');

        $currency = app()->getLocale() === 'ar'
            ? ($mainSettings->currency_ar ?? 'ر.س')
            : ($mainSettings->currency_en ?? 'SAR');

        if (app()->getLocale() === 'ar') {
            return "مرحباً {$memberName}،\n\n"
                . "شكراً لاشتراكك في {$gymName}.\n"
                . "الاشتراك: {$subscriptionName}\n"
                . "المبلغ المدفوع: {$amount} {$currency}\n\n"
                . "يمكنك إتمام الدفع عبر تمارا (اشتر الآن وادفع لاحقاً):\n"
                . "{$paymentUrl}\n\n"
                . "شكراً لك!";
        }

        return "Hello {$memberName},\n\n"
            . "Thank you for subscribing at {$gymName}.\n"
            . "Subscription: {$subscriptionName}\n"
            . "Paid Amount: {$amount} {$currency}\n\n"
            . "Complete your payment with Tamara (Buy now, pay later):\n"
            . "{$paymentUrl}\n\n"
            . "Thank you!";
    }

    /**
     * Build payment email data
     */
    protected function buildPaymentEmailData(
        GymMember $member,
        string $subscriptionName,
        float $amount,
        string $paymentUrl,
        $mainSettings
    ): array {
        $isArabic = app()->getLocale() === 'ar';

        $gymName = $isArabic
            ? ($mainSettings->name_ar ?? $mainSettings->name_en ?? 'Gym')
            : ($mainSettings->name_en ?? $mainSettings->name_ar ?? 'Gym');

        $currency = $isArabic
            ? ($mainSettings->currency_ar ?? 'ر.س')
            : ($mainSettings->currency_en ?? 'SAR');

        $subject = $isArabic
            ? "إتمام الدفع - {$gymName}"
            : "Complete Your Payment - {$gymName}";

        return [
            'subject' => $subject,
            'member_name' => $member->name,
            'gym_name' => $gymName,
            'gym_logo' => $mainSettings->logo ?? null,
            'gym_phone' => $mainSettings->phone ?? '',
            'gym_email' => $mainSettings->email ?? '',
            'subscription_name' => $subscriptionName,
            'amount' => number_format($amount, 2),
            'currency' => $currency,
            'payment_url' => $paymentUrl,
            'is_arabic' => $isArabic,
        ];
    }

    /**
     * Format phone number for international format
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '+') || str_starts_with($phone, '00')) {
            return $phone;
        }

        $appConfig = $this->settings ? ($this->settings->app_config ?? []) : [];
        $countryCode = $appConfig['country_code'] ?? '966';

        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        return '+' . $countryCode . $phone;
    }
}
