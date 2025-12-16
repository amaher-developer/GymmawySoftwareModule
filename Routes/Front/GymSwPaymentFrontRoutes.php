<?php

Route::prefix('sw-payment')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

    // List payment transactions - view permission
    Route::name('sw.listSwPayment')
        ->get('', 'Front\GymSwPaymentFrontController@show');
    Route::name('sw.showPaymentOrder')
        ->get('/order/{id}', 'Front\GymSwPaymentFrontController@showPaymentOrder');

    });
