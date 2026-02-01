<?php

namespace Modules\Software\Services;

use Modules\Generic\Http\Controllers\Front\TabbyFrontController;
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

class TabbyPaymentService
{
    protected $tabbyController;
    protected $isEnabled;
    protected $currency;

    public function __construct()
    {
        $this->isEnabled = $this->isTabbyConfigured();
        $this->currency = env('TABBY_CURRENCY', 'SAR');

        if ($this->isEnabled) {
            $this->tabbyController = new TabbyFrontController();
        }
    }

    /**
     * Check if Tabby is properly configured
     *
     * @return bool
     */
    public function isTabbyConfigured(): bool
    {
        return !empty(env('TABBY_PUBLIC_KEY'))
            && !empty(env('TABBY_SECRET_KEY'))
            && !empty(env('TABBY_MERCHANT_CODE'));
    }

    /**
     * Check if Tabby payment should be offered
     * (configured + member has remaining amount)
     *
     * @param float $amountPaid
     * @return bool
     */
    public function shouldOfferTabbyPayment(float $amountPaid): bool
    {
        if (!$this->isEnabled) {
            return false;
        }

        // Tabby minimum is usually 1 SAR/AED
        $minimumAmount = (float) env('TABBY_MINIMUM_AMOUNT', 1);

        return $amountPaid >= $minimumAmount;
    }

    /**
     * Generate Tabby payment link for member subscription
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
                'success' => false,
                'payment_url' => null,
                'error' => 'Tabby is not configured',
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

            $result = $this->tabbyController->createMemberPaymentLink([
                'amount' => $amountPaid,
                'currency' => $this->currency,
                'subscription_name' => $subscriptionName,
                'duration' => $duration,
                'member_id' => $member->id,
                'subscription_id' => $subscription->id,
                'member_subscription_id' => $memberSubscription->id,
                'branch_setting_id' => $branchSettingId ?? $memberSubscription->branch_setting_id,
                'member_name' => $member->name,
                'member_phone' => $this->formatPhoneNumber($member->phone),
                'member_email' => $member->email ?? 'member@gym.com',
                'city' => $member->city ?? 'Riyadh',
                'address' => $member->address ?? '',
                // Use member-facing return URLs
                'success_url' => route('tabby.member.success'),
                'cancel_url' => route('tabby.member.cancel'),
                'failure_url' => route('tabby.member.failure'),
            ]);

            if ($result['success']) {
                // Create pending invoice in online_payment_invoices table
                $invoice = GymOnlinePaymentInvoice::create([
                    'branch_setting_id' => $branchSettingId ?? $memberSubscription->branch_setting_id,
                    'member_id' => $member->id,
                    'subscription_id' => $subscription->id,
                    'member_subscription_id' => $memberSubscription->id,
                    'payment_id' => $result['checkout_id'] ?? null,
                    'transaction_id' => null,
                    'status' => TypeConstants::PENDING,
                    'payment_method' => TypeConstants::TABBY_TRANSACTION,
                    'amount' => $amountPaid,
                    'name' => $member->name,
                    'phone' => $member->phone,
                    'email' => $member->email,
                    'address' => $member->address,
                    'gender' => $member->gender,
                    'dob' => $member->dob,
                    'response_code' => json_encode([
                        'payment_url' => $result['payment_url'],
                        'checkout_id' => $result['checkout_id'] ?? null,
                    ]),
                ]);

                $result['invoice_id'] = $invoice->id;

                Log::info('Tabby payment link generated', [
                    'member_id' => $member->id,
                    'subscription_id' => $subscription->id,
                    'amount' => $amountPaid,
                    'payment_url' => $result['payment_url'],
                    'invoice_id' => $invoice->id,
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to generate Tabby payment link', [
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
     * Send Tabby payment link to member via WhatsApp/SMS
     *
     * @param GymMember $member
     * @param string $paymentUrl
     * @param GymSubscription $subscription
     * @param float $amountPaid
     * @param object $mainSettings
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

        // Prepare message for SMS/WhatsApp
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

                Log::info('Tabby payment link sent via WhatsApp', [
                    'member_id' => $member->id,
                    'phone' => $phone,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send Tabby payment link via WhatsApp', [
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

                Log::info('Tabby payment link sent via SMS', [
                    'member_id' => $member->id,
                    'phone' => $phone,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send Tabby payment link via SMS', [
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

                Mail::send('software::emails.tabby_payment', $emailData, function ($mail) use ($member, $mainSettings, $emailData) {
                    $mail->from(
                        $mainSettings->email ?? env('MAIL_FROM_ADDRESS', 'noreply@gymmawy.com'),
                        $mainSettings->name_en ?? $mainSettings->name_ar ?? 'Gym'
                    );
                    $mail->to($member->email, $member->name);
                    $mail->subject($emailData['subject']);
                });

                $results['email'] = true;

                Log::info('Tabby payment link sent via Email', [
                    'member_id' => $member->id,
                    'email' => $member->email,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send Tabby payment link via Email', [
                    'member_id' => $member->id,
                    'email' => $member->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Generate and send Tabby payment link for new member registration
     *
     * @param GymMember $member
     * @param int $memberSubscriptionId
     * @param GymSubscription $subscription
     * @param float $amountPaid
     * @param object $mainSettings
     * @param int|null $branchSettingId
     * @return array
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
     * Generate and send Tabby payment link for membership renewal
     *
     * @param GymMember $member
     * @param int $memberSubscriptionId
     * @param GymSubscription $subscription
     * @param float $amountPaid
     * @param object $mainSettings
     * @param int|null $branchSettingId
     * @return array
     */
    public function processRenewalPayment(
        GymMember $member,
        int $memberSubscriptionId,
        GymSubscription $subscription,
        float $amountPaid,
        $mainSettings,
        ?int $branchSettingId = null
    ): array {
        // Same logic as new member, just with different context
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
     *
     * @param string $memberName
     * @param string $subscriptionName
     * @param float $amount
     * @param string $paymentUrl
     * @param object $mainSettings
     * @return string
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
            ? ($mainSettings->currency_ar ?? env('APP_CURRENCY_AR', 'ر.س'))
            : ($mainSettings->currency_en ?? env('APP_CURRENCY_EN', 'SAR'));

        if (app()->getLocale() === 'ar') {
            return "مرحباً {$memberName}،\n\n"
                . "شكراً لاشتراكك في {$gymName}.\n"
                . "الاشتراك: {$subscriptionName}\n"
                . "المبلغ المتبقي: {$amount} {$currency}\n\n"
                . "يمكنك إتمام الدفع عبر تابي (قسّطها على 4 دفعات بدون فوائد):\n"
                . "{$paymentUrl}\n\n"
                . "شكراً لك!";
        }

        return "Hello {$memberName},\n\n"
            . "Thank you for subscribing at {$gymName}.\n"
            . "Subscription: {$subscriptionName}\n"
            . "Remaining Amount: {$amount} {$currency}\n\n"
            . "Complete your payment with Tabby (Split into 4 interest-free payments):\n"
            . "{$paymentUrl}\n\n"
            . "Thank you!";
    }

    /**
     * Build payment email data
     *
     * @param GymMember $member
     * @param string $subscriptionName
     * @param float $amount
     * @param string $paymentUrl
     * @param object $mainSettings
     * @return array
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
            ? ($mainSettings->currency_ar ?? env('APP_CURRENCY_AR', 'ر.س'))
            : ($mainSettings->currency_en ?? env('APP_CURRENCY_EN', 'SAR'));

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
     *
     * @param string $phone
     * @return string
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If already has + or 00, return as is
        if (str_starts_with($phone, '+') || str_starts_with($phone, '00')) {
            return $phone;
        }

        // Add country code
        $countryCode = env('APP_COUNTRY_CODE', '966');

        // Remove leading zero if present
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        return '+' . $countryCode . $phone;
    }
}
