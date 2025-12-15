<?php

Route::prefix('subscription')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List subscriptions - view permission
        Route::group(['defaults' => ['permission' => 'listSubscription']], function () {
            Route::name('sw.listSubscription')
                ->get('/', 'Front\GymSubscriptionFrontController@index');
            Route::name('showAllUserSubscription')
                ->get('/json/datatable', 'Front\GymSubscriptionFrontController@showAll');
        });

        // Export subscriptions - view permission
        Route::group(['defaults' => ['permission' => 'listSubscription']], function () {
            Route::name('sw.exportSubscriptionPDF')
                ->get('/pdf', 'Front\GymSubscriptionFrontController@exportPDF');
            Route::name('sw.exportSubscriptionExcel')
                ->get('/excel', 'Front\GymSubscriptionFrontController@exportExcel');
        });

        // Create subscription - create permission
        Route::group(['defaults' => ['permission' => 'createSubscription']], function () {
            Route::name('sw.createSubscription')
                ->get('create', 'Front\GymSubscriptionFrontController@create');
            Route::name('sw.createSubscription')
                ->post('create', 'Front\GymSubscriptionFrontController@store');
        });

        // Edit subscription - edit permission
        Route::group(['defaults' => ['permission' => 'editSubscription']], function () {
            Route::name('sw.editSubscription')
                ->get('{subscription}/edit', 'Front\GymSubscriptionFrontController@edit');
            Route::name('sw.editSubscription')
                ->post('{subscription}/edit', 'Front\GymSubscriptionFrontController@update');
        });

        // Delete subscription - delete permission
        Route::group(['defaults' => ['permission' => 'deleteSubscription']], function () {
            Route::name('sw.deleteSubscription')
                ->get('{subscription}/delete', 'Front\GymSubscriptionFrontController@destroy');
        });

    });
