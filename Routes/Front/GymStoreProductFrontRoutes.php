<?php

Route::prefix('store/product')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List store products - view permission
        Route::name('sw.listStoreProducts')
            ->get('/', 'Front\GymStoreProductFrontController@index');
        Route::name('sw.downloadStoreProductBarcode')
            ->get('download-barcode/{product}', 'Front\GymStoreProductFrontController@downloadBarcode');

        // Create store product - create permission
        Route::group(['defaults' => ['permission' => 'createStoreProduct']], function () {
            Route::name('sw.createStoreProduct')
                ->get('create', 'Front\GymStoreProductFrontController@create');
            Route::name('sw.createStoreProduct')
                ->post('create', 'Front\GymStoreProductFrontController@store');
            Route::name('sw.storePurchasesBill')
                ->post('create-purchases-bill', 'Front\GymStoreProductFrontController@storePurchasesBill');
        });

        // Edit store product - edit permission
        Route::group(['defaults' => ['permission' => 'editStoreProduct']], function () {
            Route::name('sw.editStoreProduct')
                ->get('{product}/edit', 'Front\GymStoreProductFrontController@edit');
            Route::name('sw.editStoreProduct')
                ->post('{product}/edit', 'Front\GymStoreProductFrontController@update');
        });

        // Delete store product - delete permission
        Route::name('sw.deleteStoreProduct')
            ->get('{product}/delete', 'Front\GymStoreProductFrontController@destroy');

    });
