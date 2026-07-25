<?php


Route::prefix('notification')
    ->middleware(['auth:sw'])
    ->group(function () {

        Route::name('sw.sendToUsers')
            ->get('/send-to-users', 'Front\GymNotificationFrontController@sendToUsers');

        Route::name('sw.markAsRead')
            ->get('/mark-as-read', 'Front\GymNotificationFrontController@markAsRead');

        // ── TEST ONLY — fires a Pusher event to the currently logged-in user ──
        Route::get('/test-pusher', 'Front\GymNotificationFrontController@testPusher')
            ->name('sw.testPusher');

        // ── TEST: simulate mobile-app payment notification ──────────────────
        Route::get('/test-app-payment', 'Front\GymNotificationFrontController@testAppPayment')
            ->name('sw.testAppPayment');

        // ── TEST: simulate mobile-app reservation notification ──────────────
        Route::get('/test-app-reservation', 'Front\GymNotificationFrontController@testAppReservation')
            ->name('sw.testAppReservation');

    });
