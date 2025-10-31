<?php

Route::prefix('sw-payment')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
    Route::name('sw.listSwPayment')
        ->get('', 'Front\GymSwPaymentFrontController@show');
    Route::name('sw.showPaymentOrder')
        ->get('/order/{id}', 'Front\GymSwPaymentFrontController@showPaymentOrder');





    });
