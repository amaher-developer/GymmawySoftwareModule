<?php

namespace Modules\Software\Http\Controllers\Front;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
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
        View::share('currentUser', $currentUser);
        $record = GymSubscription::where('id', $id)->first();

        if (!$record) {
            return abort(404);
        }

        $title = $record->name;
        $mainSettings = $this->mainSettings;

        return view('software::Front.subscription_mobile', compact('title', 'record', 'mainSettings'));
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
            $rcCheck = (array) $invoice->response_code;
            if (!empty($rcCheck['is_pt']))      $invoiceRoute = 'sw.pt-invoice-mobile';
            elseif (!empty($rcCheck['is_upgrade'])) $invoiceRoute = 'sw.upgrade-invoice-mobile';
            else                                $invoiceRoute = 'sw.invoice-mobile';
            return redirect()->route($invoiceRoute, ['id' => $invoice->member_subscription_id]);
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
            $rcCheck = (array) $invoice->response_code;
            if (!empty($rcCheck['is_pt']))      $invoiceRoute = 'sw.pt-invoice-mobile';
            elseif (!empty($rcCheck['is_upgrade'])) $invoiceRoute = 'sw.upgrade-invoice-mobile';
            else                                $invoiceRoute = 'sw.invoice-mobile';
            return redirect()->route($invoiceRoute, ['id' => $invoice->member_subscription_id]);
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

        // Delegate to upgrade finalizer when flagged
        if (!empty($rc['is_upgrade'])) {
            return $this->finalizeUpgradeMobileCheckout($invoice, $joiningDate);
        }

        // Delegate to PT finalizer when flagged
        if (!empty($rc['is_pt'])) {
            $ptMemberId = $this->finalizePtMobileCheckout($invoice, $joiningDate);
            if ($ptMemberId) {
                $dummy       = new GymMemberSubscription(['id' => $ptMemberId]);
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
        // 1) If guard already resolved the member from Authorization header, use it directly.
        $guardMember = $request->user('api') ?: \Auth::guard('api')->user();
        if ($guardMember instanceof GymMember) {
            return $guardMember;
        }

        // 2) Resolve from either query token or bearer token.
        $rawToken = $request->input('token') ?: $request->bearerToken();
        if (!$rawToken) {
            return null;
        }

        $rawToken = trim((string) preg_replace('/^Bearer\s+/i', '', (string) $rawToken));
        if ($rawToken === '') {
            return null;
        }

        // Build robust token candidates (url-encoded / plus-space variations).
        $decoded = urldecode($rawToken);
        $tokenCandidates = array_values(array_unique(array_filter([
            $rawToken,
            $decoded,
            str_replace(' ', '+', $rawToken),
            str_replace(' ', '+', $decoded),
        ])));

        // 3) Preferred mobile-web flow: map push token -> member.
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

        // 4) Fallback: token may be the app API bearer token (stored hashed in api_token).
        foreach ($tokenCandidates as $plainToken) {
            $member = GymMember::where('api_token', hash('sha256', $plainToken))->first();
            if ($member) {
                return $member;
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

    // ─────────────────────────────────────────────────────────────────────────
    // UPGRADE MOBILE — STEP 1: Show eligible upgrade subscriptions
    // ─────────────────────────────────────────────────────────────────────────

    public function showUpgradeMobile()
    {
        $this->currentMember = $member = $this->resolveMemberFromRequest(request());

        if (!$member) {
            return abort(403, trans('front.error_in_data'));
        }

        // Find the active subscription for this member
        $activeSub = GymMemberSubscription::with('subscription')
            ->where('member_id', $member->id)
            ->where('expire_date', '>=', Carbon::now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();

        if (!$activeSub) {
            return redirect()->route('sw.mobile-payment.error');
        }

        $currentPrice = (float) ($activeSub->subscription->price ?? 0);

        // Eligible upgrades: mobile, active, price > current, not current
        $upgrades = GymSubscription::where('is_mobile', 1)
            ->where('price', '>', $currentPrice)
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
        $errorRoute = route('sw.upgrade-subscription-mobile', ['token' => request('token')]);

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
        $errorRoute     = route('sw.upgrade-subscription-mobile', ['token' => request('token')]);

        [, , $currency] = $this->getTamaraCredentials();
        $tamara = new TamaraFrontController();
        $result = $tamara->createCheckoutSession([
            'amount'           => $member['amount'],
            'currency'         => $currency,
            'description'      => $sub['name'],
            'buyer'            => ['name' => $member['name'], 'phone' => $member['phone'], 'email' => $member['email'], 'address' => $member['address'], 'city' => env('TAMARA_CITY', 'Riyadh')],
            'order_reference'  => (string) $invoice->id,
            'success_url'      => route('sw.tamara-mobile.verify',  ['invoice_id' => $uniqueId]),
            'cancel_url'       => route('sw.tamara-mobile.cancel',  ['invoice_id' => $uniqueId]),
            'failure_url'      => route('sw.tamara-mobile.failure', ['invoice_id' => $uniqueId]),
            'notification_url' => route('tamara.webhook'),
            'payment_type'     => 'upgrade_subscription',
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
        $errorRoute = route('sw.upgrade-subscription-mobile', ['token' => request('token')]);

        $paytabs = new PayTabsFrontController();
        $result  = $paytabs->createCheckoutSession([
            'amount'          => $member['amount'],
            'description'     => $sub['name'],
            'buyer'           => ['name' => $member['name'], 'email' => $member['email'] ?: 'member@gym.com', 'phone' => $member['phone'], 'city' => '', 'address' => ''],
            'cart_id'         => (string) $invoice->id,
            'return_url'      => route('sw.paytabs-mobile.verify', ['invoice_id' => $uniqueId]),
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
        return $result['redirect_url'];
    }

    protected function initiateUpgradePaymob(array $sub, array $member): string
    {
        $uniqueId   = uniqid();
        $invoice    = $this->createUpgradeInvoice($member, TypeConstants::PAYMOB_TRANSACTION, $uniqueId);
        $errorRoute = route('sw.upgrade-subscription-mobile', ['token' => request('token')]);

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
                    'payment_type'           => TypeConstants::ONLINE_PAYMENT,
                ]);

                $invoice->status                 = TypeConstants::SUCCESS;
                $invoice->member_subscription_id = $newMemberSub->id;
                $invoice->save();

                $this->createMoneyBoxEntry($invoice, $member, TypeConstants::RenewMember, $newMemberSub->id);

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
        $this->currentMember = $currentUser = $this->resolveMemberFromRequest(request());

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
            'title', 'ptSubscription', 'mainSettings', 'currentUser'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT MOBILE — STEP 2: Process form & redirect to gateway
    // ─────────────────────────────────────────────────────────────────────────

    public function ptInvoiceSubmit(Request $request)
    {
        $this->currentMember = $this->resolveMemberFromRequest($request);

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
        $errorRoute  = route('sw.pt-subscription-mobile', ['id' => $ptSub['id'], 'token' => request('token')]);

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
        $errorRoute     = route('sw.pt-subscription-mobile', ['id' => $ptSub['id'], 'token' => request('token')]);

        [, , $tamaraCurrency] = $this->getTamaraCredentials();
        $tamara = new TamaraFrontController();
        $result = $tamara->createCheckoutSession([
            'amount'           => $totalAmount,
            'currency'         => $tamaraCurrency,
            'description'      => $ptSub['name'],
            'buyer'            => ['name' => $member['name'], 'phone' => $member['phone'], 'email' => $member['email'] ?? '', 'address' => $member['address'] ?? '', 'city' => env('TAMARA_CITY', 'Riyadh')],
            'order_reference'  => (string) $invoice->id,
            'success_url'      => route('sw.tamara-mobile.verify',  ['invoice_id' => $uniqueId]),
            'cancel_url'       => route('sw.tamara-mobile.cancel',  ['invoice_id' => $uniqueId]),
            'failure_url'      => route('sw.tamara-mobile.failure', ['invoice_id' => $uniqueId]),
            'notification_url' => route('tamara.webhook'),
            'payment_type'     => 'pt_subscription',
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
        $errorRoute  = route('sw.pt-subscription-mobile', ['id' => $ptSub['id'], 'token' => request('token')]);

        $paytabs = new PayTabsFrontController();
        $result  = $paytabs->createHppSession([
            'amount'          => $totalAmount,
            'description'     => $ptSub['name'],
            'customer_name'   => $member['name'],
            'customer_email'  => $member['email'] ?? 'member@gym.com',
            'customer_phone'  => $member['phone'],
            'return_url'      => route('sw.paytabs-mobile.verify', ['invoice_id' => $uniqueId]),
            'callback_url'    => route('sw.paytabs-mobile.verify', ['invoice_id' => $uniqueId]),
            'cart_id'         => (string) $invoice->id,
        ]);

        if (!$result['success']) {
            \Session::flash('error', $result['error'] ?? trans('front.error_in_data'));
            return $errorRoute;
        }

        $invoice->transaction_id = $result['tran_ref'] ?? '';
        $invoice->save();
        return $result['redirect_url'];
    }

    protected function initiatePtPaymob(array $ptSub, array $member): string
    {
        $totalAmount = round($member['amount'], 2);
        $uniqueId    = uniqid();
        $invoice     = $this->createPtInvoice($member, TypeConstants::PAYMOB_TRANSACTION, $uniqueId);
        $errorRoute  = route('sw.pt-subscription-mobile', ['id' => $ptSub['id'], 'token' => request('token')]);

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

        $ptClass = \Modules\Software\Models\GymPTClass::find($ptClassId);
        if (!$ptClass) {
            Log::error('PT Mobile Finalize: class not found', ['invoice_id' => $invoice->id]);
            return null;
        }

        $lockKey = 'pt_mobile_finalize_' . $invoice->id;
        DB::selectOne('SELECT GET_LOCK(?, 30) AS locked', [$lockKey]);

        try {
            return DB::transaction(function () use ($invoice, $joiningDate, $ptClass, $ptClassId, $ptClassTrainerId, $ptSubscriptionId, $totalSessions) {
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

                $ptMember = \Modules\Software\Models\GymPTMember::create([
                    'member_id'          => $member->id,
                    'pt_subscription_id' => $ptSubscriptionId,
                    'class_id'           => $ptClassId,
                    'class_trainer_id'   => $ptClassTrainerId,
                    'total_sessions'     => $totalSessions,
                    'remaining_sessions' => $totalSessions,
                    'start_date'         => $joining->toDateString(),
                    'expire_date'        => $expireDate->toDateString(),
                    'joining_date'       => $joining->toDateString(),
                    'paid_amount'        => $invoice->amount,
                    'is_active'          => 1,
                    'branch_setting_id'  => $member->branch_setting_id ?? null,
                ]);

                // Store pt_member_id for idempotency; reuse member_subscription_id column
                $invoice->member_subscription_id = $ptMember->id;
                $invoice->status                 = TypeConstants::SUCCESS;
                $invoice->response_code          = array_merge($rc, ['pt_member_id' => $ptMember->id]);
                $invoice->save();

                // Money box
                $this->createMoneyBoxEntry($invoice, $member, TypeConstants::CreateMember, $ptMember->id);

                return $ptMember->id;
            });
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?)', [$lockKey]);
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
}
