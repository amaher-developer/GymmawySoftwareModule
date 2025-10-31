<?php

Route::prefix('moneybox')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listMoneyBox')
            ->get('/', 'Front\GymMoneyBoxFrontController@index');
        Route::name('sw.listMoneyBoxDaily')
            ->get('/daily', 'Front\GymMoneyBoxFrontController@indexDaily');

        Route::name('sw.showOrder')
            ->get('order/show/{id}', 'Front\GymOrderFrontController@show');
        Route::name('sw.showOrderPOS')
            ->get('order/show/pos/{id}', 'Front\GymOrderFrontController@showPOS');


        Route::name('sw.showOrderSubscription')
            ->get('order/subscription/show/{id}', 'Front\GymOrderFrontController@showSubscription');
        Route::name('sw.showOrderSubscriptionPOS')
            ->get('order/subscription/show/pos/{id}', 'Front\GymOrderFrontController@showSubscriptionPOS');

        Route::name('sw.signOrderSubscription')
            ->post('order/subscription/sign/{id}', 'Front\GymOrderFrontController@signSubscription');

        Route::name('sw.showOrderPTSubscription')
            ->get('order/pt-subscription/show/{id}', 'Front\GymOrderFrontController@showPTSubscription');
        Route::name('sw.showOrderPTSubscriptionPOS')
            ->get('order/pt-subscription/show/pos/{id}', 'Front\GymOrderFrontController@showPTSubscriptionPOS');

        Route::name('sw.showOrderSubscriptionNonMember')
            ->get('order/subscription/non-member/show/{id}', 'Front\GymOrderFrontController@showSubscriptionNonMember');
        Route::name('sw.showOrderSubscriptionPOSNonMember')
            ->get('order/subscription/non-member/show/pos/{id}', 'Front\GymOrderFrontController@showSubscriptionPOSNonMember');

        Route::name('sw.exportMoneyBoxPDF')
            ->get('/pdf', 'Front\GymMoneyBoxFrontController@exportPDF');
        Route::name('sw.exportMoneyBoxExcel')
            ->get('/excel', 'Front\GymMoneyBoxFrontController@exportExcel');

        Route::name('sw.createMoneyBoxAdd')
            ->get('add', 'Front\GymMoneyBoxFrontController@create');
        Route::name('sw.createMoneyBoxAdd')
            ->post('add', 'Front\GymMoneyBoxFrontController@store');

        Route::name('sw.createMoneyBoxWithdraw')
            ->get('withdraw', 'Front\GymMoneyBoxFrontController@createWithdraw');
        Route::name('sw.createMoneyBoxWithdraw')
            ->post('withdraw', 'Front\GymMoneyBoxFrontController@storeWithdraw');

        Route::name('sw.createMoneyBoxWithdrawEarnings')
            ->get('withdraw-earnings', 'Front\GymMoneyBoxFrontController@createWithdrawEarnings');
        Route::name('sw.createMoneyBoxWithdrawEarnings')
            ->post('withdraw-earnings', 'Front\GymMoneyBoxFrontController@storeWithdrawEarnings');


        Route::name('sw.scriptForRebuildMoneybox')
            ->get('rebuild', 'Front\GymMoneyBoxFrontController@scriptForRebuildMoneybox');

        Route::name('sw.editPaymentTypeOrderMoneybox')
            ->get('edit-payment-type-order-moneybox', 'Front\GymMoneyBoxFrontController@editPaymentTypeOrder');


    });
