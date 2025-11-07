<?php

Route::prefix('store/product')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listStoreProducts')
            ->get('/', 'Front\GymStoreProductFrontController@index');
        Route::name('sw.createStoreProduct')
            ->get('create', 'Front\GymStoreProductFrontController@create');
        Route::name('sw.createStoreProduct')
            ->post('create', 'Front\GymStoreProductFrontController@store');

        Route::name('sw.downloadStoreProductBarcode')
            ->get('download-barcode/{product}', 'Front\GymStoreProductFrontController@downloadBarcode');


        Route::name('sw.editStoreProduct')
            ->get('{product}/edit', 'Front\GymStoreProductFrontController@edit');
        Route::name('sw.editStoreProduct')
            ->post('{product}/edit', 'Front\GymStoreProductFrontController@update');
        Route::name('sw.deleteStoreProduct')
            ->get('{product}/delete', 'Front\GymStoreProductFrontController@destroy');


        Route::name('sw.storePurchasesBill')
            ->post('create-purchases-bill', 'Front\GymStoreProductFrontController@storePurchasesBill');



//        Route::name('sw.listPTTrainer')
//            ->get('/', 'Front\GymStoreFrontController@index');
//        Route::name('sw.createPTTrainer')
//            ->get('create', 'Front\GymStoreFrontController@create');
//        Route::name('sw.storePTTrainer')
//            ->post('create', 'Front\GymStoreFrontController@store');




    });
