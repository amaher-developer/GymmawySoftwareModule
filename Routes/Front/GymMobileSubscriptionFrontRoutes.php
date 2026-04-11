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
    ->get('mobile-payment/tamara/verify', 'Front\GymMobileSubscriptionFrontController@tamaraVerify');
Route::name('sw.tamara-mobile.cancel')
    ->get('mobile-payment/tamara/cancel', 'Front\GymMobileSubscriptionFrontController@tamaraCancel');
Route::name('sw.tamara-mobile.failure')
    ->get('mobile-payment/tamara/failure', 'Front\GymMobileSubscriptionFrontController@tamaraFailure');

// ── PayTabs ───────────────────────────────────────────────────────────────────
Route::name('sw.paytabs-mobile.verify')
    ->get('mobile-payment/paytabs/verify', 'Front\GymMobileSubscriptionFrontController@paytabsVerify');
Route::name('sw.paytabs-mobile.cancel')
    ->get('mobile-payment/paytabs/cancel', 'Front\GymMobileSubscriptionFrontController@paytabsCancel');
Route::name('sw.paytabs-mobile.failure')
    ->get('mobile-payment/paytabs/failure', 'Front\GymMobileSubscriptionFrontController@paytabsFailure');

// ── Paymob ────────────────────────────────────────────────────────────────────
Route::name('sw.paymob-mobile.verify')
    ->get('mobile-payment/paymob/verify', 'Front\GymMobileSubscriptionFrontController@paymobVerify');
Route::name('sw.paymob-mobile.cancel')
    ->get('mobile-payment/paymob/cancel', 'Front\GymMobileSubscriptionFrontController@paymobCancel');
Route::name('sw.paymob-mobile.failure')
    ->get('mobile-payment/paymob/failure', 'Front\GymMobileSubscriptionFrontController@paymobFailure');

// ── Upgrade Subscription Mobile (WebView) ────────────────────────────────────
Route::name('sw.upgrade-subscription-mobile')
    ->any('upgrade-subscription-mobile', 'Front\GymMobileSubscriptionFrontController@showUpgradeMobile');

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
