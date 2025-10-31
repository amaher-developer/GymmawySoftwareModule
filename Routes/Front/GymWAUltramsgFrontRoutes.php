<?php


Route::prefix('wa-ultra')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.createWAUltra')
            ->get('create', 'Front\GymWAUltraFrontController@create');
        Route::name('sw.storeWAUltra')
            ->post('create', 'Front\GymWAUltraFrontController@store');

        Route::name('sw.storeWAUltraToken')
            ->post('create-token', 'Front\GymWAUltraFrontController@storeToken');

        Route::name('sw.phonesByAjaxWAUltra')
            ->get('phones-by-ajax', 'Front\GymWAUltraFrontController@phonesByAjax');

        Route::name('sw.listWAUltraLog')
            ->get('/logs', 'Front\GymWAUltraFrontController@index');

});

