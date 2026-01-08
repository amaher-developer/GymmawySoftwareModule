<?php

Route::prefix('pt/subscription')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List PT subscriptions - view permission
        Route::name('sw.listPTSubscription')
            ->get('/', 'Front\GymPTSubscriptionFrontController@index');

        // Create PT subscription - create permission
        Route::group(['defaults' => ['permission' => 'createPTSubscription']], function () {
            Route::name('sw.createPTSubscription')
                ->get('create', 'Front\GymPTSubscriptionFrontController@create');
            Route::name('sw.storePTSubscription')
                ->post('create', 'Front\GymPTSubscriptionFrontController@store');
        });

        // Edit PT subscription - edit permission
        Route::group(['defaults' => ['permission' => 'editPTSubscription']], function () {
            Route::name('sw.editPTSubscription')
                ->get('{subscription}/edit', 'Front\GymPTSubscriptionFrontController@edit');
            Route::name('sw.editPTSubscription')
                ->post('{subscription}/edit', 'Front\GymPTSubscriptionFrontController@update');
        });

        // Delete PT subscription - delete permission
        Route::name('sw.deletePTSubscription')
            ->get('{subscription}/delete', 'Front\GymPTSubscriptionFrontController@destroy');

    });
