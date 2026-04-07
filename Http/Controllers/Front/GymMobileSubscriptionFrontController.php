<?php

namespace Modules\Software\Http\Controllers\Front;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Generic\Http\Controllers\Front\PaymobFrontController;
use Modules\Generic\Http\Controllers\Front\PayTabsFrontController;
use Modules\Generic\Http\Controllers\Front\TabbyFrontController;
use Modules\Generic\Http\Controllers\Front\TamaraFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymOnlinePaymentInvoice;
use Modules\Software\Models\GymSubscription;

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
        $this->currentMember = $currentUser = $this->resolveMemberFromRequest(request());

        $record = GymSubscription::where('id', $id)->first();

        if (!$record) {
            return abort(404);
        }

        $title = $record->name;
        $mainSettings = $this->mainSettings;

        return view('software::Front.subscription_mobile', compact('title', 'record', 'mainSettings', 'currentUser'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 2 — Process form & redirect to gateway
    // ─────────────────────────────────────────────────────────────────────────

    public function invoiceSubmit(Request $request)
    {
        $this->currentMember = $this->resolveMemberFromRequest($request);

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

        $errorRoute = route('sw.subscription-mobile', ['id' => $subscription['id'], 'token' => request('token')]);

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
            'success_url'        => route('sw.tamara-mobile.verify',  ['invoice_id' => $uniqueId]),
            'cancel_url'         => route('sw.tamara-mobile.cancel',  ['invoice_id' => $uniqueId]),
            'failure_url'        => route('sw.tamara-mobile.failure', ['invoice_id' => $uniqueId]),
            'notification_url'   => route('tamara.webhook'),
            'payment_type'       => 'member_subscription',
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

        $errorRoute = route('sw.subscription-mobile', ['id' => $subscription['id'], 'token' => request('token')]);

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
            'success_url'     => route('sw.paytabs-mobile.verify',  ['invoice_id' => $uniqueId]),
            'cancel_url'      => route('sw.paytabs-mobile.cancel',  ['invoice_id' => $uniqueId]),
            'failure_url'     => route('sw.paytabs-mobile.failure', ['invoice_id' => $uniqueId]),
            'callback_url'    => route('paytabs.callback'),
            'payment_type'    => 'member_subscription',
            'member_id'       => optional($this->currentMember)->id,
            'subscription_id' => $member['subscription_id'],
        ]);

        $errorRoute = route('sw.subscription-mobile', ['id' => $subscription['id'], 'token' => request('token')]);

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
            return redirect()->route('sw.invoice-mobile', ['id' => $invoice->member_subscription_id]);
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

        $joiningDate = $invoice->response_code['joining_date'] ?? Carbon::now()->toDateString();
        $sub = $this->finalizeMobileCheckout($invoice, $joiningDate);

        if ($sub) {
            return redirect()->route('sw.invoice-mobile', ['id' => $sub->id]);
        }

        return redirect()->route('sw.mobile-payment.error');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 4b — Tamara: verify, authorise & capture
    // ─────────────────────────────────────────────────────────────────────────

    public function tamaraVerify(Request $request)
    {
        $invoiceId    = $request->invoice_id;
        $paymentStatus = $request->paymentStatus; // Tamara redirect param
        $orderId       = $request->orderId;

        $invoice = GymOnlinePaymentInvoice::with(['subscription' => fn($q) => $q->withTrashed()])
            ->where('payment_id', $invoiceId)->first();

        if (!$invoice) {
            return redirect()->route('sw.mobile-payment.error');
        }

        if ($invoice->member_subscription_id) {
            return redirect()->route('sw.invoice-mobile', ['id' => $invoice->member_subscription_id]);
        }

        if ($paymentStatus !== 'approved') {
            $invoice->status = TypeConstants::FAILURE;
            $invoice->save();
            return redirect()->route('sw.mobile-payment.error');
        }

        $tamaraOrderId = $invoice->transaction_id;
        $joiningDate   = $invoice->response_code['joining_date'] ?? Carbon::now()->toDateString();

        // Authorise order
        $authorise       = $this->tamaraAuthoriseOrder($tamaraOrderId);
        $authoriseStatus = $authorise['status'] ?? null;
        $autoCaptured    = $authorise['auto_captured'] ?? false;

        Log::info('Tamara Mobile authorise', ['status' => $authoriseStatus, 'auto_captured' => $autoCaptured]);

        if (!in_array($authoriseStatus, ['authorised', 'fully_captured']) && !$autoCaptured) {
            $invoice->status = TypeConstants::FAILURE;
            $invoice->response_code = array_merge((array) $invoice->response_code, ['tamara_authorise' => $authorise]);
            $invoice->save();
            return redirect()->route('sw.mobile-payment.error');
        }

        // Capture if not auto-captured
        if ($authoriseStatus === 'authorised' && !$autoCaptured) {
            $capture = $this->tamaraCapturePayment(
                $tamaraOrderId,
                number_format((float) $invoice->amount, 2, '.', ''),
                [['title' => optional($invoice->subscription)->name, 'quantity' => 1,
                  'unit_price' => $invoice->amount, 'total_amount' => $invoice->amount,
                  'reference_id' => (string) $invoice->id]]
            );
            Log::info('Tamara Mobile capture', ['capture' => $capture]);

            if (!($capture['capture_id'] ?? null) && !in_array($capture['status'] ?? '', ['fully_captured', 'partially_captured'])) {
                $invoice->status = TypeConstants::FAILURE;
                $invoice->response_code = array_merge((array) $invoice->response_code, ['tamara_capture' => $capture]);
                $invoice->save();
                return redirect()->route('sw.mobile-payment.error');
            }
        }

        $invoice->status = TypeConstants::SUCCESS;
        $invoice->response_code = array_merge(
            (array) $invoice->response_code,
            ['tamara_authorise' => $authorise]
        );
        $invoice->save();

        $sub = $this->finalizeMobileCheckout($invoice, $joiningDate);

        if ($sub) {
            return redirect()->route('sw.invoice-mobile', ['id' => $sub->id]);
        }

        return redirect()->route('sw.mobile-payment.error');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 4c — PayTabs: verify payment status
    // ─────────────────────────────────────────────────────────────────────────

    public function paytabsVerify(Request $request)
    {
        $invoiceId = $request->invoice_id;

        $invoice = GymOnlinePaymentInvoice::with(['subscription' => fn($q) => $q->withTrashed()])
            ->where('payment_id', $invoiceId)->first();

        if (!$invoice) {
            return redirect()->route('sw.mobile-payment.error');
        }

        if ($invoice->member_subscription_id) {
            return redirect()->route('sw.invoice-mobile', ['id' => $invoice->member_subscription_id]);
        }

        $joiningDate = $invoice->response_code['joining_date'] ?? Carbon::now()->toDateString();
        $tranRef     = $invoice->transaction_id;

        if (!$tranRef) {
            Log::error('PayTabs Mobile verify: no tran_ref', ['invoice_id' => $invoiceId]);
            $invoice->status = TypeConstants::FAILURE;
            $invoice->save();
            return redirect()->route('sw.mobile-payment.error');
        }

        $paytabs = new PayTabsFrontController();
        $payment = $paytabs->queryTransaction($tranRef);

        Log::info('PayTabs Mobile verify', ['tran_ref' => $tranRef, 'response' => $payment]);

        if (!$payment) {
            Log::error('PayTabs Mobile verify: queryTransaction returned null', ['tran_ref' => $tranRef]);
            $invoice->status = TypeConstants::FAILURE;
            $invoice->save();
            return redirect()->route('sw.mobile-payment.error');
        }

        $responseStatus = $payment['payment_result']['response_status'] ?? null;

        Log::info('PayTabs Mobile verify status', ['tran_ref' => $tranRef, 'status' => $responseStatus]);

        if ($responseStatus !== 'A') {
            $invoice->status = TypeConstants::FAILURE;
            $invoice->response_code = array_merge((array) $invoice->response_code, ['paytabs_verify' => $payment]);
            $invoice->save();
            return redirect()->route('sw.mobile-payment.error');
        }

        $invoice->status = TypeConstants::SUCCESS;
        $invoice->response_code = array_merge(
            (array) $invoice->response_code,
            ['paytabs_verify' => $payment]
        );
        $invoice->save();

        $sub = $this->finalizeMobileCheckout($invoice, $joiningDate);

        if ($sub) {
            return redirect()->route('sw.invoice-mobile', ['id' => $sub->id]);
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

        $errorRoute = route('sw.subscription-mobile', ['id' => $subscription['id'], 'token' => request('token')]);

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
            return redirect()->route('sw.invoice-mobile', ['id' => $invoice->member_subscription_id]);
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

        Log::info('Paymob Mobile verify success', ['invoice_id' => $invoiceId, 'transaction_id' => $transactionId]);

        $sub = $this->finalizeMobileCheckout($invoice, $joiningDate);

        if ($sub) {
            return redirect()->route('sw.invoice-mobile', ['id' => $sub->id]);
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
        $subscription = $invoice->subscription
            ?? GymSubscription::withTrashed()->find($invoice->subscription_id);

        if (!$subscription) {
            Log::error('Mobile Finalize: subscription not found', ['invoice_id' => $invoice->id]);
            return null;
        }

        $lockKey = 'mobile_finalize_' . $invoice->id;
        DB::selectOne('SELECT GET_LOCK(?, 30) AS locked', [$lockKey]);

        try {
            return DB::transaction(function () use ($invoice, $joiningDate, $subscription) {
                // Re-read with exclusive row lock
                $invoice = GymOnlinePaymentInvoice::where('id', $invoice->id)->lockForUpdate()->first();

                // Idempotency
                if ($invoice->member_subscription_id) {
                    return GymMemberSubscription::find($invoice->member_subscription_id);
                }

                // ── Resolve or create member ───────────────────────────────
                $member        = null;
                $typeOfPayment = TypeConstants::RenewMember;

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
                    $typeOfPayment = TypeConstants::CreateMember;
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
                    'payment_type'           => TypeConstants::ONLINE_PAYMENT,
                ]);

                // ── Update invoice ─────────────────────────────────────────
                $invoice->status                 = TypeConstants::SUCCESS;
                $invoice->member_subscription_id = $memberSub->id;
                $invoice->save();

                // ── MoneyBox entry ─────────────────────────────────────────
                $this->createMoneyBoxEntry($invoice, $member, $typeOfPayment, $memberSub->id);

                return $memberSub;
            });
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

    protected function resolveMemberFromRequest(Request $request): ?GymMember
    {
        $token = $request->input('token') ?: $request->bearerToken();
        if ($token) {
            $token    = str_replace('Bearer ', '', $token);
            $pushToken = DB::table('sw_gym_push_tokens')->where('token', $token)->first();
            if ($pushToken && $pushToken->member_id) {
                return GymMember::find($pushToken->member_id);
            }
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
                $params = ['id' => $inv->subscription_id];
                if ($token) $params['token'] = $token;
                return redirect()->route('sw.subscription-mobile', $params);
            }
        }
        return redirect()->route('sw.mobile-payment.error');
    }

    protected function createMoneyBoxEntry(GymOnlinePaymentInvoice $invoice, GymMember $member, int $type, int $memberSubId): void
    {
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
            'payment_type'            => TypeConstants::ONLINE_PAYMENT,
            'member_subscription_id'  => $memberSubId,
            'online_subscription_id'  => $invoice->id,
        ]);
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
}
