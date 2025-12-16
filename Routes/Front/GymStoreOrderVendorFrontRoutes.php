<?php

Route::prefix('store/vendor/order')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List store vendor orders - view permission
        Route::name('sw.listStoreOrderVendor')
            ->get('/', 'Front\GymStoreOrderVendorFrontController@index');
        Route::name('sw.showStoreOrderVendor')
            ->get('show/{id}', 'Front\GymStoreOrderVendorFrontController@show');
        Route::name('sw.showStoreOrderVendorPOS')
            ->get('show/pos/{id}', 'Front\GymStoreOrderVendorFrontController@showPOS');

        // Export store vendor orders - view permission
        Route::name('sw.exportStoreOrderVendorPDF')
            ->get('/store-order-vendor-pdf', 'Front\GymStoreOrderVendorFrontController@exportStoreOrderVendorPDF');
        Route::name('sw.exportStoreOrderVendorExcel')
            ->get('/store-order-vendor-excel', 'Front\GymStoreOrderVendorFrontController@exportStoreOrderVendorExcel');

        // Delete store vendor order - delete permission
        Route::name('sw.deleteStoreOrderVendor')
            ->get('{order}/delete', 'Front\GymStoreOrderVendorFrontController@destroy');

    });
