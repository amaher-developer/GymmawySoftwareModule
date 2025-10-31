<?php


Route::prefix('wa')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.createWA')
            ->get('create', 'Front\GymWAFrontController@create');
        Route::name('sw.storeWA')
            ->post('create', 'Front\GymWAFrontController@store');

        Route::name('sw.phonesByAjaxWA')
            ->get('phones-by-ajax', 'Front\GymWAFrontController@phonesByAjax');

        Route::name('sw.listWALog')
            ->get('/logs', 'Front\GymWAFrontController@index');

});

