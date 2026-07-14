<?php

namespace Modules\Software\Services;

use Modules\Generic\Http\Controllers\Front\PaymobIntentionFrontController;
use Modules\Generic\Models\Setting;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymOnlinePaymentInvoice;
use Modules\Software\Classes\WAUltramsg;
use Modules\Software\Classes\SMSFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Orchestration service for the Paymob Flash / Intention API integration.
 *
 * Standalone counterpart to PaymobPaymentService (legacy) — deliberately not a subclass
 * of it, so the legacy service can keep evolving independently. Reads credentials from
 * settings.payments['paymob_intention'], entirely separate from settings.payments['paymob'].
 */
class PaymobIntentionPaymentService
{
    protected $paymobController;
    protected $isEnabled;
    protected $currency;
    protected $settings;

    public function __construct()
    {
        $this->settings = Setting::branch()->first();
        $this->isEnabled = $this->isPaymobConfigured();
        $paymob = $this->settings ? ($this->settings->payments['paymob_intention'] ?? []) : [];
        $this->currency = $paymob['currency'] ?? 'EGP';

        if ($this->isEnabled) {
            $this->paymobController = new PaymobIntentionFrontController();
        }
    }

    public function isPaymobConfigured(): bool
    {
        $paymob = $this->settings ? ($this->settings->payments['paymob_intention'] ?? []) : [];
        return !empty($paymob['secret_key'])
            && !empty($paymob['public_key'])
            && !empty($paymob['integration_id']);
    }

    public function shouldOfferPaymobPayment(float $amountPaid): bool
    {
        if (!$this->isEnabled) {
            return false;
        }

        $paymob = $this->settings ? ($this->settings->payments['paymob_intention'] ?? []) : [];
        $minimumAmount = (float) ($paymob['minimum_amount'] ?? 1);

        return $amountPaid >= $minimumAmount;
    }

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
                'error' => 'Paymob Intention is not configured',
            ];
        }

        try {
            $invoice = GymOnlinePaymentInvoice::create([
                'branch_setting_id' => $branchSettingId ?? $memberSubscription->branch_setting_id,
                'member_id' => $member->id,
                'subscription_id' => $subscription->id,
                'member_subscription_id' => $memberSubscription->id,
                'payment_id' => null,
                'transaction_id' => null,
                'status' => TypeConstants::PENDING,
                'payment_method' => TypeConstants::PAYMOB_INTENTION_TRANSACTION,
                'payment_channel' => TypeConstants::CHANNEL_SYSTEM,
                'amount' => $amountPaid,
                'name' => $member->name,
                'phone' => $member->phone,
                'email' => $member->email,
                'address' => $member->address,
                'gender' => $member->gender,
                'dob' => $member->dob,
                'response_code' => json_encode(['pending' => true]),
            ]);

            $token = $this->paymobController->generateHmacToken($invoice->id);
            $paymentUrl = url("/paymob-intention/pay/{$invoice->id}?token={$token}");

            Log::info('Paymob Intention payment link generated', [
                'member_id' => $member->id,
                'subscription_id' => $subscription->id,
                'amount' => $amountPaid,
                'payment_url' => $paymentUrl,
                'invoice_id' => $invoice->id,
            ]);

            return [
                'success' => true,
                'payment_url' => $paymentUrl,
                'invoice_id' => $invoice->id,
                'error' => null,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate Paymob Intention payment link', [
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

        if (!empty($member->phone) && @$mainSettings->active_wa && @env('WA_GATEWAY') == 'ULTRA') {
            $phone = trim($member->phone);
            try {
                $wa = new WAUltramsg();
                $wa->sendText($phone, $message);
                $results['whatsapp'] = true;
            } catch (\Exception $e) {
                Log::error('Failed to send Paymob Intention payment link via WhatsApp', [
                    'member_id' => $member->id,
                    'phone' => $phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($member->phone) && @$mainSettings->active_sms && @env('SMS_GATEWAY')) {
            $phone = trim($member->phone);
            try {
                $sms = new SMSFactory(@env('SMS_GATEWAY'));
                $sms->send($phone, $message);
                $results['sms'] = true;
            } catch (\Exception $e) {
                Log::error('Failed to send Paymob Intention payment link via SMS', [
                    'member_id' => $member->id,
                    'phone' => $phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($member->email)) {
            try {
                $emailData = $this->buildPaymentEmailData(
                    $member,
                    $subscriptionName,
                    $amountPaid,
                    $paymentUrl,
                    $mainSettings
                );

                Mail::send('software::emails.paymob_payment', $emailData, function ($mail) use ($member, $mainSettings, $emailData) {
                    $mail->from(
                        $mainSettings->email ?? 'noreply@gymmawy.com',
                        $mainSettings->name_en ?? $mainSettings->name_ar ?? 'Gym'
                    );
                    $mail->to($member->email, $member->name);
                    $mail->subject($emailData['subject']);
                });

                $results['email'] = true;
            } catch (\Exception $e) {
                Log::error('Failed to send Paymob Intention payment link via Email', [
                    'member_id' => $member->id,
                    'email' => $member->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

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

    public function generateLinkWithoutSubscription(
        $member,
        GymSubscription $subscription,
        float $amount,
        $mainSettings,
        ?int $branchSettingId = null
    ): array {
        if (!$this->isEnabled) {
            return ['success' => false, 'invoice_id' => null, 'payment_url' => null, 'error' => 'Paymob Intention not configured'];
        }
        try {
            $invoice = GymOnlinePaymentInvoice::create([
                'branch_setting_id'      => $branchSettingId,
                'member_id'              => $member->id,
                'subscription_id'        => $subscription->id,
                'member_subscription_id' => null,
                'payment_id'             => null,
                'status'                 => TypeConstants::PENDING,
                'payment_method'         => TypeConstants::PAYMOB_INTENTION_TRANSACTION,
                'payment_channel'        => TypeConstants::CHANNEL_SYSTEM,
                'amount'                 => $amount,
                'name'                   => $member->name,
                'phone'                  => $member->phone,
                'email'                  => $member->email,
                'response_code'          => json_encode(['pending' => true]),
            ]);

            $token      = $this->paymobController->generateHmacToken($invoice->id);
            $paymentUrl = url("/paymob-intention/pay/{$invoice->id}?token={$token}");
            $sent       = $this->sendPaymentLinkToMember($member, $paymentUrl, $subscription, $amount, $mainSettings);

            return ['success' => true, 'invoice_id' => $invoice->id, 'payment_url' => $paymentUrl, 'sent_via' => $sent];
        } catch (\Exception $e) {
            Log::error('Paymob Intention generateLinkWithoutSubscription failed', ['member_id' => $member->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'invoice_id' => null, 'payment_url' => null, 'error' => $e->getMessage()];
        }
    }

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
            ? ($mainSettings->currency_ar ?? 'ج.م')
            : ($mainSettings->currency_en ?? 'EGP');

        if (app()->getLocale() === 'ar') {
            return "مرحباً {$memberName}،\n\n"
                . "شكراً لاشتراكك في {$gymName}.\n"
                . "الاشتراك: {$subscriptionName}\n"
                . "المبلغ المطلوب: {$amount} {$currency}\n\n"
                . "يمكنك إتمام الدفع عبر الرابط التالي:\n"
                . "{$paymentUrl}\n\n"
                . "شكراً لك!";
        }

        return "Hello {$memberName},\n\n"
            . "Thank you for subscribing at {$gymName}.\n"
            . "Subscription: {$subscriptionName}\n"
            . "Amount Due: {$amount} {$currency}\n\n"
            . "Complete your payment using the link below:\n"
            . "{$paymentUrl}\n\n"
            . "Thank you!";
    }

    protected function buildPaymentEmailData(
        $member,
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
            ? ($mainSettings->currency_ar ?? 'ج.م')
            : ($mainSettings->currency_en ?? 'EGP');

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
}
