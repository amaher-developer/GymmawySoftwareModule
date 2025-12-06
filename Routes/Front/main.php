<?php


Route::name('sw.fingerprintAttendees')
    ->any('home-fingerprint-store', 'Front\GymHomeFrontController@fingerprintAttendees');

Route::prefix('/')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.backupDB')->get('db-backup', 'Front\GymDBFrontController@backupDb');

Route::redirect('', 'home');

Route::name('sw.dashboard')
    ->get('home', 'Front\GymHomeFrontController@home');
        Route::name('sw.dashboardMini')
            ->get('home-mini', 'Front\GymHomeFrontController@home_mini');
        Route::name('sw.dashboardPTMini')
            ->get('home-pt-mini', 'Front\GymHomeFrontController@home_pt_mini');
        Route::name('sw.dashboardFingerprintMini')
            ->get('home-fingerprint-mini', 'Front\GymHomeFrontController@home_fingerprint_mini');
        Route::name('sw.statistics')
            ->get('statistics', 'Front\GymHomeFrontController@statistics');
        Route::name('sw.memberSubscriptionStatistics')
            ->get('statistics/member-subscription', 'Front\GymHomeFrontController@memberSubscriptionStatistics');
        Route::name('sw.storeStatistics')
            ->get('statistics/store', 'Front\GymHomeFrontController@storeStatistics');
        Route::name('sw.ptSubscriptionStatistics')
            ->get('statistics/pt-subscription', 'Front\GymHomeFrontController@ptSubscriptionStatistics');
        Route::name('sw.nonMemberStatistics')
            ->get('statistics/non-member', 'Front\GymHomeFrontController@nonMemberStatistics');
        Route::name('sw.subscriptionStatisticsRefresh')
            ->get('subscription-statistics-refresh', 'Front\GymHomeFrontController@subscriptionStatisticsRefresh');
        Route::name('sw.storeStatisticsRefresh')
            ->get('store-statistics-refresh', 'Front\GymHomeFrontController@storeStatisticsRefresh');
        Route::name('sw.ptSubscriptionStatisticsRefresh')
            ->get('pt-subscription-statistics-refresh', 'Front\GymHomeFrontController@ptSubscriptionStatisticsRefresh');
        Route::name('sw.nonMemberStatisticsRefresh')
            ->get('non-member-statistics-refresh', 'Front\GymHomeFrontController@nonMemberStatisticsRefresh');
        Route::name('sw.branchSwitch')
            ->get('{id}/branch-switch', 'Front\GymHomeFrontController@branchSwitch');

    });


