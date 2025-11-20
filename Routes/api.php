<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// website route
Route::match(['get', 'post'], '/settings', 'Api\WebsiteApiController@settings')->middleware(['api']);
Route::post('/member-subscription-info', 'Api\WebsiteApiController@memberSubscriptionInfo')->middleware(['api']);
Route::post('/member-subscription-invoice-info', 'Api\WebsiteApiController@memberSubscriptionInvoiceInfo')->middleware(['api']);
Route::post('/member-subscription-info-by-phone', 'Api\WebsiteApiController@memberSubscriptionInfoByPhone')->middleware(['api']);
Route::post('/create-member-subscription', 'Api\WebsiteApiController@createMemberSubscription')->middleware(['api']);
Route::post('/member-attendance-info', 'Api\WebsiteApiController@memberAttendanceInfo')->middleware(['api']);


Route::get('/web/home', 'Api\WebsiteApiController@home')->middleware(['api']);
Route::get('/web/about-us', 'Api\WebsiteApiController@aboutUs')->middleware(['api']);
Route::get('/web/gallery', 'Api\WebsiteApiController@gallery')->middleware(['api']);
Route::get('/web/subscriptions', 'Api\WebsiteApiController@subscriptions')->middleware(['api']);
Route::get('/web/activities', 'Api\WebsiteApiController@activities')->middleware(['api']);
Route::get('/web/notifications', 'Api\WebsiteApiController@notifications')->middleware(['api']);

Route::get('/send-member-to-gymmawy', 'Api\GymMemberApiController@sendMemberToGymmawy')->middleware(['api']);
Route::get('/send-one-member-to-gymmawy', 'Api\GymMemberApiController@sendOneMemberToGymmawy')->middleware(['api']);
Route::get('/send-sw-my-app-notifications', 'Api\GymMemberApiController@sendSwMyAppNotifications')->middleware(['api']);

Route::name('sw.successfulPayment')->get('/sw-payment/s', 'Api\GymSwPaymentApiController@successfulPayment')->middleware(['api']);

Route::name('sw.migration')->get('/gym-migrate', 'Api\GymSettingApiController@migrate')->middleware(['api']);
Route::name('sw.lastMigration')->get('/gym-last-migrate', 'Api\GymSettingApiController@lastMigrate')->middleware(['api']);

Route::prefix('zk')
    ->group(function () {
        Route::get('login',  'Front\GymZKFrontController@login');
        Route::get('member', 'Front\GymZKFrontController@member');
        Route::get('member/{member}', 'Front\GymZKFrontController@member');
        Route::post('member/create', 'Front\GymZKFrontController@memberStore');
        Route::post('member/{member}/delete', 'Front\GymZKFrontController@memberDelete');
    });

// fingerprint

Route::any('/gym-member/fingerprint-store', 'Api\GymMemberApiController@fingerprintMemberAttendees');
Route::any('/gym-member/fingerprint-zk-attendees-member', 'Api\GymMemberApiController@fingerprintZKMemberAttendees');

Route::any('/gym-member/fingerprint-zk-member-getter', 'Api\GymMemberApiController@fingerprintZKMemberGetter')->middleware(['api']);
Route::any('/gym-member/fingerprint-zk-member-setter', 'Api\GymMemberApiController@fingerprintZKMemberSetter')->middleware(['api']);


Route::name('sw.ptClassActiveMemberAjax')
    ->get('pt-class-active-member-ajax', 'Front\GymPTMemberFrontController@classActiveMemberAjax');

foreach (File::allFiles(__DIR__ . '/Api') as $route) {
require_once $route->getPathname();
}
