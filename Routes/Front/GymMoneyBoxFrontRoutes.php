<?php

Route::prefix('moneybox')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List moneybox - view permission
        Route::group(['defaults' => ['permission' => 'listMoneyBox']], function () {
            Route::name('sw.listMoneyBox')
                ->get('/', 'Front\GymMoneyBoxFrontController@index');
        });

        
        // List moneybox - view permission
        Route::group(['defaults' => ['permission' => 'listMoneyBoxDaily']], function () {
            Route::name('sw.listMoneyBoxDaily')
                ->get('/daily', 'Front\GymMoneyBoxFrontController@indexDaily');
        });
        // Export moneybox - view permission
        Route::group(['defaults' => ['permission' => 'listMoneyBox']], function () {
            Route::name('sw.exportMoneyBoxPDF')
                ->get('/pdf', 'Front\GymMoneyBoxFrontController@exportPDF');
            Route::name('sw.exportMoneyBoxExcel')
                ->get('/excel', 'Front\GymMoneyBoxFrontController@exportExcel');
        });

        // Show orders - view order permission
        Route::group(['defaults' => ['permission' => 'showOrder']], function () {
            Route::name('sw.showOrder')
                ->get('order/show/{id}', 'Front\GymOrderFrontController@show');
            Route::name('sw.showOrderPOS')
                ->get('order/show/pos/{id}', 'Front\GymOrderFrontController@showPOS');

            Route::name('sw.showOrderSubscription')
                ->get('order/subscription/show/{id}', 'Front\GymOrderFrontController@showSubscription');
            Route::name('sw.showOrderSubscriptionPOS')
                ->get('order/subscription/show/pos/{id}', 'Front\GymOrderFrontController@showSubscriptionPOS');

            Route::name('sw.showOrderPTSubscription')
                ->get('order/pt-subscription/show/{id}', 'Front\GymOrderFrontController@showPTSubscription');
            Route::name('sw.showOrderPTSubscriptionPOS')
                ->get('order/pt-subscription/show/pos/{id}', 'Front\GymOrderFrontController@showPTSubscriptionPOS');

            Route::name('sw.showOrderSubscriptionNonMember')
                ->get('order/subscription/non-member/show/{id}', 'Front\GymOrderFrontController@showSubscriptionNonMember');
            Route::name('sw.showOrderSubscriptionPOSNonMember')
                ->get('order/subscription/non-member/show/pos/{id}', 'Front\GymOrderFrontController@showSubscriptionPOSNonMember');
        });

        // Sign order subscription - edit permission
        Route::group(['defaults' => ['permission' => 'signOrderSubscription']], function () {
            Route::name('sw.signOrderSubscription')
                ->post('order/subscription/sign/{id}', 'Front\GymOrderFrontController@signSubscription');
        });

        // Create moneybox add - create permission
        Route::group(['defaults' => ['permission' => 'createMoneyBoxAdd']], function () {
            Route::name('sw.createMoneyBoxAdd')
                ->get('add', 'Front\GymMoneyBoxFrontController@create');
            Route::name('sw.createMoneyBoxAdd')
                ->post('add', 'Front\GymMoneyBoxFrontController@store');
        });

        // Create moneybox withdraw - create permission
        Route::group(['defaults' => ['permission' => 'createMoneyBoxWithdraw']], function () {
            Route::name('sw.createMoneyBoxWithdraw')
                ->get('withdraw', 'Front\GymMoneyBoxFrontController@createWithdraw');
            Route::name('sw.createMoneyBoxWithdraw')
                ->post('withdraw', 'Front\GymMoneyBoxFrontController@storeWithdraw');
        });

        // Create moneybox withdraw earnings - create permission
        Route::group(['defaults' => ['permission' => 'createMoneyBoxWithdrawEarnings']], function () {
            Route::name('sw.createMoneyBoxWithdrawEarnings')
                ->get('withdraw-earnings', 'Front\GymMoneyBoxFrontController@createWithdrawEarnings');
            Route::name('sw.createMoneyBoxWithdrawEarnings')
                ->post('withdraw-earnings', 'Front\GymMoneyBoxFrontController@storeWithdrawEarnings');
        });

        // Rebuild moneybox script - administrative permission
        Route::group(['defaults' => ['permission' => 'scriptForRebuildMoneybox']], function () {
            Route::name('sw.scriptForRebuildMoneybox')
                ->get('rebuild', 'Front\GymMoneyBoxFrontController@scriptForRebuildMoneybox');
        });

        // Edit payment type order moneybox - edit permission
        Route::group(['defaults' => ['permission' => 'editPaymentTypeOrderMoneybox']], function () {
            Route::name('sw.editPaymentTypeOrderMoneybox')
                ->get('edit-payment-type-order-moneybox', 'Front\GymMoneyBoxFrontController@editPaymentTypeOrder');
        });

    });
