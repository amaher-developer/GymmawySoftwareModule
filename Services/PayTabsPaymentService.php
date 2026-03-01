<?php

namespace Modules\Software\Services;

use Modules\Generic\Http\Controllers\Front\PayTabsFrontController;
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

class PayTabsPaymentService
{
    protected $paytabsController;
    protected $isEnabled;
    protected $currency;
    protected $country;
    protected $city;
    protected $address;
    protected $settings;

    public function __construct()
    {
        $this->settings  = Setting::branch()->first();
        $this->isEnabled = $this->isPayTabsConfigured();
        $paytabs         = $this->settings ? ($this->settings->payments['paytabs'] ?? []) : [];
        $this->currency  = $paytabs['currency'] ?? 'SAR';
        $this->country   = $paytabs['country']  ?? 'SA';
        $this->city      = $paytabs['city']      ?? 'Riyadh';
        $this->address   = $paytabs['address']   ?? 'N/A';

        if ($this->isEnabled) {
            $this->paytabsController = new PayTabsFrontController();
        }
    }

    /**
     * Check if PayTabs is properly configured
     */
    public function isPayTabsConfigured(): bool
    {
        $paytabs = $this->settings ? ($this->settings->payments['paytabs'] ?? []) : [];
        return !empty($paytabs['profile_id'])
            && !empty($paytabs['server_key']);
    }

    /**
     * Check if PayTabs payment should be offered
     */
    public function shouldOfferPayTabsPayment(float $amount): bool
    {
        if (!$this->isEnabled) {
            return false;
        }

        $paytabs = $this->settings ? ($this->settings->payments['paytabs'] ?? []) : [];
        $minimumAmount = (float) ($paytabs['minimum_amount'] ?? 1);

        return $amount >= $minimumAmount;
    }

    /**
     * Generate PayTabs payment link for member subscription
     *
     * @param GymMember $member
     * @param GymMemberSubscription $memberSubscription
     * @param GymSubscription $subscription
     * @param float $amountPaid
     * @param int|null $branchSettingId
     * @return array ['success' => bool, 'payment_url' => string|null, 'error' => string|null]
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
                'success'     => false,
                'payment_url' => null,
                'error'       => 'PayTabs is not configured',
            ];
        }

        try {
            $subscriptionName = app()->getLocale() === 'ar'
                ? ($subscription->name_ar ?? $subscription->name_en)
                : ($subscription->name_en ?? $subscription->name_ar);

            $duration = $subscription->period ?? 30;

            $result = $this->paytabsController->createMemberPaymentLink([
                'amount'                 => $amountPaid,
                'currency'               => $this->currency,
                'subscription_name'      => $subscriptionName,
                'duration'               => $duration,
                'member_id'              => $member->id,
                'subscription_id'        => $subscription->id,
                'member_subscription_id' => $memberSubscription->id,
                'branch_setting_id'      => $branchSettingId ?? $memberSubscription->branch_setting_id,
                'member_name'            => $member->name,
                'member_phone'           => $this->formatPhoneNumber($member->phone ?? ''),
                'member_email'           => $member->email ?? 'member@gym.com',
                'city'                   => $this->city,
                'country'                => $this->country,
                'address'                => $member->address ?? $this->address,
                'success_url'            => route('paytabs.member.success'),
                'cancel_url'             => route('paytabs.member.cancel'),
                'failure_url'            => route('paytabs.member.failure'),
            ]);

            if ($result['success']) {
                // Create pending invoice record
                $invoice = GymOnlinePaymentInvoice::create([
                    'branch_setting_id'      => $branchSettingId ?? $memberSubscription->branch_setting_id,
                    'member_id'              => $member->id,
                    'subscription_id'        => $subscription->id,
                    'member_subscription_id' => $memberSubscription->id,
                    'payment_id'             => $result['tran_ref'] ?? null,
                    'transaction_id'         => $result['cart_id']  ?? null,
                    'status'                 => TypeConstants::PENDING,
                    'payment_method'         => TypeConstants::PAYTABS_TRANSACTION,
                    'payment_channel'        => TypeConstants::CHANNEL_SYSTEM,
                    'amount'                 => $amountPaid,
                    'name'                   => $member->name,
                    'phone'                  => $member->phone,
                    'email'                  => $member->email,
                    'address'                => $member->address,
                    'gender'                 => $member->gender,
                    'dob'                    => $member->dob,
                    'response_code'          => json_encode([
                        'payment_url' => $result['payment_url'],
                        'tran_ref'    => $result['tran_ref'] ?? null,
                        'cart_id'     => $result['cart_id']  ?? null,
                    ]),
                ]);

                $result['invoice_id'] = $invoice->id;

                Log::info('PayTabs payment link generated', [
                    'member_id'       => $member->id,
                    'subscription_id' => $subscription->id,
                    'amount'          => $amountPaid,
                    'payment_url'     => $result['payment_url'],
                    'invoice_id'      => $invoice->id,
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to generate PayTabs payment link', [
                'member_id'       => $member->id,
                'subscription_id' => $subscription->id,
                'amount'          => $amountPaid,
                'error'           => $e->getMessage(),
            ]);

            return [
                'success'     => false,
                'payment_url' => null,
                'error'       => $e->getMessage(),
            ];
        }
    }

    /**
     * Send PayTabs payment link to member via WhatsApp/SMS/Email
     *
     * @return array ['whatsapp' => bool, 'sms' => bool, 'email' => bool]
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
            try {
                $wa = new WAUltramsg();
                $wa->sendText(trim($member->phone), $message);
                $results['whatsapp'] = true;

                Log::info('PayTabs payment link sent via WhatsApp', ['member_id' => $member->id]);
            } catch (\Exception $e) {
                Log::error('Failed to send PayTabs payment link via WhatsApp', [
                    'member_id' => $member->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        // Send via SMS
        if (!empty($member->phone) && @$mainSettings->active_sms && @env('SMS_GATEWAY')) {
            try {
                $sms = new SMSFactory(@env('SMS_GATEWAY'));
                $sms->send(trim($member->phone), $message);
                $results['sms'] = true;

                Log::info('PayTabs payment link sent via SMS', ['member_id' => $member->id]);
            } catch (\Exception $e) {
                Log::error('Failed to send PayTabs payment link via SMS', [
                    'member_id' => $member->id,
                    'error'     => $e->getMessage(),
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

                Mail::send('software::emails.paytabs_payment', $emailData, function ($mail) use ($member, $mainSettings, $emailData) {
                    $mail->from(
                        $mainSettings->email ?? env('MAIL_FROM_ADDRESS', 'noreply@gymmawy.com'),
                        $mainSettings->name_en ?? $mainSettings->name_ar ?? 'Gym'
                    );
                    $mail->to($member->email, $member->name);
                    $mail->subject($emailData['subject']);
                });

                $results['email'] = true;

                Log::info('PayTabs payment link sent via Email', ['member_id' => $member->id]);
            } catch (\Exception $e) {
                Log::error('Failed to send PayTabs payment link via Email', [
                    'member_id' => $member->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }
        return $results;
    }

    /**
     * Generate and send PayTabs payment link for new member registration
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
                'reason'  => 'link_generation_failed',
                'error'   => $linkResult['error'],
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
            'success'      => true,
            'payment_url'  => $linkResult['payment_url'],
            'sent_whatsapp' => $sendResult['whatsapp'],
            'sent_sms'     => $sendResult['sms'],
            'sent_email'   => $sendResult['email'],
        ];
    }

    /**
     * Generate and send PayTabs payment link for membership renewal
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
                . "المبلغ: {$amount} {$currency}\n\n"
                . "يمكنك إتمام الدفع الآمن عبر PayTabs:\n"
                . "{$paymentUrl}\n\n"
                . "شكراً لك!";
        }

        return "Hello {$memberName},\n\n"
            . "Thank you for subscribing at {$gymName}.\n"
            . "Subscription: {$subscriptionName}\n"
            . "Amount: {$amount} {$currency}\n\n"
            . "Complete your secure payment via PayTabs:\n"
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
            'subject'           => $subject,
            'member_name'       => $member->name,
            'gym_name'          => $gymName,
            'gym_logo'          => $mainSettings->logo ?? null,
            'gym_phone'         => $mainSettings->phone ?? '',
            'gym_email'         => $mainSettings->email ?? '',
            'subscription_name' => $subscriptionName,
            'amount'            => number_format($amount, 2),
            'currency'          => $currency,
            'payment_url'       => $paymentUrl,
            'is_arabic'         => $isArabic,
        ];
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '+') || str_starts_with($phone, '00')) {
            return $phone;
        }

        $appConfig   = $this->settings ? ($this->settings->app_config ?? []) : [];
        $countryCode = $appConfig['country_code'] ?? '966';

        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        return '+' . $countryCode . $phone;
    }
}
