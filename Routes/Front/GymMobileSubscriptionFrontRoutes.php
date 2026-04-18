<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile Subscription Payment Routes (WebView)
|--------------------------------------------------------------------------
|
| These routes power the mobile-app webview payment flow.
| The mobile app opens:
|   GET /{lang}/subscription-mobile/{id}?token=PUSH_TOKEN
|
| No auth:sw middleware — the member is identified via push token.
|
*/

// ── Subscription form (mobile webview entry point) ───────────────────────────
Route::name('sw.subscription-mobile')
    ->get('subscription-mobile/{id}', 'Front\GymMobileSubscriptionFrontController@showMobile');

// ── Form submission (POST) ────────────────────────────────────────────────────
Route::name('sw.invoice-mobile.submit')
    ->post('invoice-mobile/submit', 'Front\GymMobileSubscriptionFrontController@invoiceSubmit');

// ── Invoice page (shown after successful payment) ─────────────────────────────
Route::name('sw.invoice-mobile')
    ->get('invoice-mobile/{id}', 'Front\GymMobileSubscriptionFrontController@invoiceMobile');

// ── Generic payment error page ────────────────────────────────────────────────
Route::name('sw.mobile-payment.error')
    ->get('mobile-payment/error', 'Front\GymMobileSubscriptionFrontController@paymentError');

// ── Tabby ─────────────────────────────────────────────────────────────────────
Route::name('sw.tabby-mobile.verify')
    ->get('mobile-payment/tabby/verify', 'Front\GymMobileSubscriptionFrontController@tabbyVerify');
Route::name('sw.tabby-mobile.cancel')
    ->get('mobile-payment/tabby/cancel', 'Front\GymMobileSubscriptionFrontController@tabbyCancel');
Route::name('sw.tabby-mobile.failure')
    ->get('mobile-payment/tabby/failure', 'Front\GymMobileSubscriptionFrontController@tabbyFailure');

// ── Tamara ────────────────────────────────────────────────────────────────────
Route::name('sw.tamara-mobile.verify')
    ->match(['GET', 'POST'], 'mobile-payment/tamara/verify', 'Front\GymMobileSubscriptionFrontController@tamaraVerify');
Route::name('sw.tamara-mobile.cancel')
    ->match(['GET', 'POST'], 'mobile-payment/tamara/cancel', 'Front\GymMobileSubscriptionFrontController@tamaraCancel');
Route::name('sw.tamara-mobile.failure')
    ->match(['GET', 'POST'], 'mobile-payment/tamara/failure', 'Front\GymMobileSubscriptionFrontController@tamaraFailure');

// ── PayTabs ───────────────────────────────────────────────────────────────────
Route::name('sw.paytabs-mobile.verify')
    ->match(['GET', 'POST'], 'mobile-payment/paytabs/verify', 'Front\GymMobileSubscriptionFrontController@paytabsVerify');
Route::name('sw.paytabs-mobile.cancel')
    ->match(['GET', 'POST'], 'mobile-payment/paytabs/cancel', 'Front\GymMobileSubscriptionFrontController@paytabsCancel');
Route::name('sw.paytabs-mobile.failure')
    ->match(['GET', 'POST'], 'mobile-payment/paytabs/failure', 'Front\GymMobileSubscriptionFrontController@paytabsFailure');

// PayTabs member result pages with locale prefix (Lang middleware redirects /paytabs/* → /{locale}/paytabs/*)
Route::match(['GET', 'POST'], 'paytabs/member/success', '\Modules\Generic\Http\Controllers\Front\PayTabsFrontController@memberSuccess');
Route::match(['GET', 'POST'], 'paytabs/member/cancel',  '\Modules\Generic\Http\Controllers\Front\PayTabsFrontController@memberCancel');
Route::match(['GET', 'POST'], 'paytabs/member/failure', '\Modules\Generic\Http\Controllers\Front\PayTabsFrontController@memberFailure');

// ── Paymob ────────────────────────────────────────────────────────────────────
Route::name('sw.paymob-mobile.verify')
    ->get('mobile-payment/paymob/verify', 'Front\GymMobileSubscriptionFrontController@paymobVerify');
Route::name('sw.paymob-mobile.cancel')
    ->get('mobile-payment/paymob/cancel', 'Front\GymMobileSubscriptionFrontController@paymobCancel');
Route::name('sw.paymob-mobile.failure')
    ->get('mobile-payment/paymob/failure', 'Front\GymMobileSubscriptionFrontController@paymobFailure');

// ── Upgrade Subscription Mobile (WebView) ────────────────────────────────────
Route::name('sw.upgrade-subscription-mobile')
    ->get('upgrade-subscription-mobile', 'Front\GymMobileSubscriptionFrontController@showUpgradeMobile');

Route::name('sw.upgrade-invoice-mobile.submit')
    ->post('upgrade-invoice-mobile/submit', 'Front\GymMobileSubscriptionFrontController@upgradeInvoiceSubmit');

Route::name('sw.upgrade-invoice-mobile')
    ->get('upgrade-invoice-mobile/{id}', 'Front\GymMobileSubscriptionFrontController@upgradeInvoiceMobile');

// ── PT Subscription Mobile (WebView) ──────────────────────────────────────────
Route::name('sw.pt-subscription-mobile')
    ->get('pt-subscription-mobile/{id}', 'Front\GymMobileSubscriptionFrontController@showPtMobile');

Route::name('sw.pt-invoice-mobile.submit')
    ->post('pt-invoice-mobile/submit', 'Front\GymMobileSubscriptionFrontController@ptInvoiceSubmit');

Route::name('sw.pt-invoice-mobile')
    ->get('pt-invoice-mobile/{id}', 'Front\GymMobileSubscriptionFrontController@ptInvoiceMobile');

// ── Activity / Store Mobile (WebView entry points) ──────────────────────────
Route::name('sw.activity-mobile')
    ->get('activity-mobile/{id}', 'Front\GymMobileSubscriptionFrontController@showActivityMobile');

Route::name('sw.activity-invoice-mobile.submit')
    ->post('activity-invoice-mobile/submit', 'Front\GymMobileSubscriptionFrontController@activityInvoiceSubmit');

Route::name('sw.store-mobile')
    ->get('store-mobile/{id}', 'Front\GymMobileSubscriptionFrontController@showStoreMobile');

Route::name('sw.training-plan-mobile')
    ->get('training-plan-mobile/{id}', 'Front\GymMobileSubscriptionFrontController@showTrainingPlanMobile');

Route::name('sw.training-member-log-mobile')
    ->get('training-member-log-mobile', 'Front\GymMobileSubscriptionFrontController@showTrainingMemberLogMobile');

Route::name('sw.training-member-log-mobile.detail')
    ->get('training-member-log-mobile/log/{log}', 'Front\GymMobileSubscriptionFrontController@showTrainingMemberLogMobileDetail');

Route::name('sw.store-invoice-mobile.submit')
    ->post('store-invoice-mobile/submit', 'Front\GymMobileSubscriptionFrontController@storeInvoiceSubmit');

// ── Activity / Store invoice pages (shown after successful payment) ───────────
Route::name('sw.activity-invoice-mobile')
    ->get('activity-invoice-mobile', 'Front\GymMobileSubscriptionFrontController@activityInvoiceMobile');

Route::name('sw.store-order-invoice-mobile')
    ->get('store-order-invoice-mobile', 'Front\GymMobileSubscriptionFrontController@storeOrderInvoiceMobile');
