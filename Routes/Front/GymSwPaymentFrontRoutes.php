<?php

use Modules\Generic\Http\Controllers\Front\SubscriptionPaymentController;

Route::prefix('sw-payment')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::get('', [SubscriptionPaymentController::class, 'showPlans'])->name('sw.listSwPayment');
    // Route::name('sw.listSwPayment')
    //     ->get('', 'Front\GymSwPaymentFrontController@show');
    // Route::name('sw.showPaymentOrder')
    //     ->get('/order/{id}', 'Front\GymSwPaymentFrontController@showPaymentOrder');





    });
