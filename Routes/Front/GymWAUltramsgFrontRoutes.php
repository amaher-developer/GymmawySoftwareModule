<?php


Route::prefix('wa-ultra')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // Create WhatsApp Ultra message - create permission
        Route::group(['defaults' => ['permission' => 'createWAUltra']], function () {
            Route::name('sw.createWAUltra')
                ->get('create', 'Front\GymWAUltraFrontController@create');
            Route::name('sw.storeWAUltra')
                ->post('create', 'Front\GymWAUltraFrontController@store');
            Route::name('sw.phonesByAjaxWAUltra')
                ->get('phones-by-ajax', 'Front\GymWAUltraFrontController@phonesByAjax');
        });

        // Store WhatsApp Ultra token - create permission
        Route::name('sw.storeWAUltraToken')
            ->post('create-token', 'Front\GymWAUltraFrontController@storeToken');

        // List WhatsApp Ultra logs - view permission
        Route::name('sw.listWAUltraLog')
            ->get('/logs', 'Front\GymWAUltraFrontController@index');

});
