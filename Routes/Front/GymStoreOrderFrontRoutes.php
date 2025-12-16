<?php

Route::prefix('store/order')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List store orders - view permission
        Route::name('sw.listStoreOrders')
            ->get('/', 'Front\GymStoreOrderFrontController@index');
        Route::name('sw.showStoreOrder')
            ->get('show/{id}', 'Front\GymStoreOrderFrontController@show');
        Route::name('sw.showStoreOrderPOS')
            ->get('show/pos/{id}', 'Front\GymStoreOrderFrontController@showPOS');

        // Export store orders - view permission
        Route::name('sw.exportStoreOrderPDF')
            ->get('/store-order-pdf', 'Front\GymStoreOrderFrontController@exportStoreOrderPDF');
        Route::name('sw.exportStoreOrderExcel')
            ->get('/store-order-excel', 'Front\GymStoreOrderFrontController@exportStoreOrderExcel');

        // List store purchase orders - view permission
        Route::name('sw.listStorePurchaseOrders')
            ->get('/purchases', 'Front\GymStoreOrderFrontController@indexPurchases');
        Route::name('sw.showStorePurchaseOrder')
            ->get('show/purchase/{id}', 'Front\GymStoreOrderFrontController@showPurchase');
        Route::name('sw.showStorePurchaseOrderPOS')
            ->get('show/purchase/pos/{id}', 'Front\GymStoreOrderFrontController@showPurchasePOS');

        // Create store order - create permission
        Route::group(['defaults' => ['permission' => 'createStoreOrder']], function () {
            Route::name('sw.createStoreOrder')
                ->get('create', 'Front\GymStoreOrderFrontController@create');
            Route::name('sw.createStoreOrder')
                ->post('create', 'Front\GymStoreOrderFrontController@store');
        });

        // Create store order POS - create permission
        Route::group(['defaults' => ['permission' => 'createStoreOrderPOS']], function () {
            Route::name('sw.createStoreOrderPOS')
                ->get('create-pos', 'Front\GymStoreOrderFrontController@createPOS');
            Route::name('sw.storeStoreOrderPOS')
                ->post('store-pos', 'Front\GymStoreOrderFrontController@storePOS');
        });

        // Edit store order - edit permission
        Route::group(['defaults' => ['permission' => 'editStoreOrder']], function () {
            Route::name('sw.editStoreOrder')
                ->get('{order}/edit', 'Front\GymStoreOrderFrontController@edit');
            Route::name('sw.editStoreOrder')
                ->post('{order}/edit', 'Front\GymStoreOrderFrontController@update');
        });

        // Delete store order - delete permission
        Route::name('sw.deleteStoreOrder')
            ->get('{order}/delete', 'Front\GymStoreOrderFrontController@destroy');

        // Get store member and loyalty info - helper endpoints
        Route::name('sw.getStoreMemberAjax')
            ->get('get-store-member-ajax', 'Front\GymStoreOrderFrontController@getStoreMemberAjax');
        Route::name('sw.getMemberLoyaltyInfo')
            ->get('get-member-loyalty-info', 'Front\GymStoreOrderFrontController@getMemberLoyaltyInfo');

    });
