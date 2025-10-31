<?php

Route::prefix('store/vendor/order')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listStoreOrderVendor')
            ->get('/', 'Front\GymStoreOrderVendorFrontController@index');
        Route::name('sw.showStoreOrderVendor')
            ->get('show/{id}', 'Front\GymStoreOrderVendorFrontController@show');
        Route::name('sw.showStoreOrderVendorPOS')
            ->get('show/pos/{id}', 'Front\GymStoreOrderVendorFrontController@showPOS');


        Route::name('sw.exportStoreOrderVendorPDF')
            ->get('/store-order-vendor-pdf', 'Front\GymStoreOrderVendorFrontController@exportStoreOrderVendorPDF');
        Route::name('sw.exportStoreOrderVendorExcel')
            ->get('/store-order-vendor-excel', 'Front\GymStoreOrderVendorFrontController@exportStoreOrderVendorExcel');


        Route::name('sw.deleteStoreOrderVendor')
            ->get('{order}/delete', 'Front\GymStoreOrderVendorFrontController@destroy');
//        Route::name('sw.listPTTrainer')
//            ->get('/', 'Front\GymStoreFrontController@index');
//        Route::name('sw.createPTTrainer')
//            ->get('create', 'Front\GymStoreFrontController@create');
//        Route::name('sw.storePTTrainer')
//            ->post('create', 'Front\GymStoreFrontController@store');




    });
