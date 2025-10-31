<?php

Route::prefix('pt/subscription')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {


        Route::name('sw.listPTSubscription')
            ->get('/', 'Front\GymPTSubscriptionFrontController@index');
        Route::name('sw.createPTSubscription')
            ->get('create', 'Front\GymPTSubscriptionFrontController@create');
        Route::name('sw.storePTSubscription')
            ->post('create', 'Front\GymPTSubscriptionFrontController@store');

        Route::name('sw.editPTSubscription')
            ->get('{subscription}/edit', 'Front\GymPTSubscriptionFrontController@edit');
        Route::name('sw.editPTSubscription')
            ->post('{subscription}/edit', 'Front\GymPTSubscriptionFrontController@update');
        Route::name('sw.deletePTSubscription')
            ->get('{subscription}/delete', 'Front\GymPTSubscriptionFrontController@destroy');

    });
