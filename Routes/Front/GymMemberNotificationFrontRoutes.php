<?php


Route::prefix('m-notification')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // Create member notification - create permission
        Route::group(['defaults' => ['permission' => 'createNotification']], function () {
            Route::name('sw.createNotification')
                ->get('create', 'Front\GymMemberNotificationFrontController@create');
            Route::name('sw.storeNotification')
                ->post('create', 'Front\GymMemberNotificationFrontController@store');
        });

        Route::name('sw.phonesByAjax')
            ->get('phones-by-ajax', 'Front\GymMemberNotificationFrontController@phonesByAjax');
            
        // List notification logs - view permission
        Route::group(['defaults' => ['permission' => 'listNotificationLog']], function () {
            Route::name('sw.listNotificationLog')
                ->get('/logs', 'Front\GymMemberNotificationFrontController@index');
        });

    });


Route::prefix('my-notification')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // Create my notification - create permission
        Route::group(['defaults' => ['permission' => 'createMyNotification']], function () {
            Route::name('sw.createMyNotification')
                ->get('create', 'Front\GymMemberMyNotificationFrontController@create');
            Route::name('sw.storeMyNotification')
                ->post('create', 'Front\GymMemberMyNotificationFrontController@store');
        });

        // List my notification logs - view permission
        Route::group(['defaults' => ['permission' => 'listMyNotificationLog']], function () {
            Route::name('sw.listMyNotificationLog')
                ->get('/logs', 'Front\GymMemberMyNotificationFrontController@index');
        });

    });

