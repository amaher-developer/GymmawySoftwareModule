<?php


Route::prefix('m-notification')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.createNotification')
            ->get('create', 'Front\GymMemberNotificationFrontController@create');
        Route::name('sw.storeNotification')
            ->post('create', 'Front\GymMemberNotificationFrontController@store');
        Route::name('sw.phonesByAjax')
            ->get('phones-by-ajax', 'Front\GymMemberNotificationFrontController@phonesByAjax');

        Route::name('sw.listNotificationLog')
            ->get('/logs', 'Front\GymMemberNotificationFrontController@index');

    });


Route::prefix('my-notification')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.createMyNotification')
            ->get('create', 'Front\GymMemberMyNotificationFrontController@create');
        Route::name('sw.storeMyNotification')
            ->post('create', 'Front\GymMemberMyNotificationFrontController@store');

        Route::name('sw.listMyNotificationLog')
            ->get('/logs', 'Front\GymMemberMyNotificationFrontController@index');

    });

