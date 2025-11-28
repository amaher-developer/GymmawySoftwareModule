<?php

Route::prefix('store/order')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listStoreOrders')
            ->get('/', 'Front\GymStoreOrderFrontController@index');


        Route::name('sw.exportStoreOrderPDF')
            ->get('/store-order-pdf', 'Front\GymStoreOrderFrontController@exportStoreOrderPDF');
        Route::name('sw.exportStoreOrderExcel')
            ->get('/store-order-excel', 'Front\GymStoreOrderFrontController@exportStoreOrderExcel');


        Route::name('sw.showStoreOrder')
            ->get('show/{id}', 'Front\GymStoreOrderFrontController@show');
        Route::name('sw.showStoreOrderPOS')
            ->get('show/pos/{id}', 'Front\GymStoreOrderFrontController@showPOS');


        Route::name('sw.listStorePurchaseOrders')
            ->get('/purchases', 'Front\GymStoreOrderFrontController@indexPurchases');
        Route::name('sw.showStorePurchaseOrder')
            ->get('show/purchase/{id}', 'Front\GymStoreOrderFrontController@showPurchase');
        Route::name('sw.showStorePurchaseOrderPOS')
            ->get('show/purchase/pos/{id}', 'Front\GymStoreOrderFrontController@showPurchasePOS');


        Route::name('sw.createStoreOrder')
            ->get('create', 'Front\GymStoreOrderFrontController@create');
        Route::name('sw.createStoreOrder')
            ->post('create', 'Front\GymStoreOrderFrontController@store');
        
        Route::name('sw.createStoreOrderPOS')
            ->get('create-pos', 'Front\GymStoreOrderFrontController@createPOS');
        Route::name('sw.createStoreOrderPOS')
            ->post('store-pos', 'Front\GymStoreOrderFrontController@storePOS');


        Route::name('sw.editStoreOrder')
            ->get('{order}/edit', 'Front\GymStoreOrderFrontController@edit');
        Route::name('sw.editStoreOrder')
            ->post('{order}/edit', 'Front\GymStoreOrderFrontController@update');
        Route::name('sw.deleteStoreOrder')
            ->get('{order}/delete', 'Front\GymStoreOrderFrontController@destroy');

        Route::name('sw.getStoreMemberAjax')
            ->get('get-store-member-ajax', 'Front\GymStoreOrderFrontController@getStoreMemberAjax');
        
        Route::name('sw.getMemberLoyaltyInfo')
            ->get('get-member-loyalty-info', 'Front\GymStoreOrderFrontController@getMemberLoyaltyInfo');

        Route::name('sw.deleteStoreOrder')
            ->get('{order}/delete', 'Front\GymStoreOrderFrontController@destroy');
//        Route::name('sw.listPTTrainer')
//            ->get('/', 'Front\GymStoreFrontController@index');
//        Route::name('sw.createPTTrainer')
//            ->get('create', 'Front\GymStoreFrontController@create');
//        Route::name('sw.storePTTrainer')
//            ->post('create', 'Front\GymStoreFrontController@store');




    });
