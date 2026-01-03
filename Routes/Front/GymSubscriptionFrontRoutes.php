<?php

Route::prefix('subscription')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listSubscription')
            ->get('/', 'Front\GymSubscriptionFrontController@index');

        Route::name('sw.exportSubscriptionPDF')
            ->get('/pdf', 'Front\GymSubscriptionFrontController@exportPDF');
        Route::name('sw.exportSubscriptionExcel')
            ->get('/excel', 'Front\GymSubscriptionFrontController@exportExcel');

        Route::name('sw.createSubscription')
            ->get('create', 'Front\GymSubscriptionFrontController@create');
        Route::name('sw.createSubscription')
            ->post('create', 'Front\GymSubscriptionFrontController@store');
        Route::name('sw.editSubscription')
            ->get('{subscription}/edit', 'Front\GymSubscriptionFrontController@edit');
        Route::name('sw.editSubscription')
            ->post('{subscription}/edit', 'Front\GymSubscriptionFrontController@update');
        Route::name('sw.deleteSubscription')
            ->get('{subscription}/delete', 'Front\GymSubscriptionFrontController@destroy');

        Route::name('showAllUserSubscription')
            ->get('/json/datatable', 'Front\GymSubscriptionFrontController@showAll');

    });
