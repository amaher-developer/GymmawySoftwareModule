<?php


Route::prefix('sms')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.createSMS')
            ->get('create', 'Front\GymSMSFrontController@create');
        Route::name('sw.storeSMS')
            ->post('create', 'Front\GymSMSFrontController@store');
        Route::name('sw.phonesByAjax')
            ->get('phones-by-ajax', 'Front\GymSMSFrontController@phonesByAjax');

        Route::name('sw.listSMSLog')
            ->get('/logs', 'Front\GymSMSFrontController@index');

});

