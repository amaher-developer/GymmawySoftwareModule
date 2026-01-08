<?php

Route::prefix('subscription-category')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List subscription categories - view permission
        Route::name('sw.listSubscriptionCategory')
            ->get('/', 'Front\GymSubscriptionCategoryFrontController@index');

        // Export subscription categories - view permission
        Route::name('sw.exportSubscriptionCategoryPDF')
            ->get('/pdf', 'Front\GymSubscriptionCategoryFrontController@exportPDF');
        Route::name('sw.exportSubscriptionCategoryExcel')
            ->get('/excel', 'Front\GymSubscriptionCategoryFrontController@exportExcel');

        // Create subscription category - create permission
        Route::group(['defaults' => ['permission' => 'createSubscriptionCategory']], function () {
            Route::name('sw.createSubscriptionCategory')
                ->get('create', 'Front\GymSubscriptionCategoryFrontController@create');
            Route::name('sw.createSubscriptionCategory')
                ->post('create', 'Front\GymSubscriptionCategoryFrontController@store');
        });

        // Edit subscription category - edit permission
        Route::group(['defaults' => ['permission' => 'editSubscriptionCategory']], function () {
            Route::name('sw.editSubscriptionCategory')
                ->get('{category}/edit', 'Front\GymSubscriptionCategoryFrontController@edit');
            Route::name('sw.editSubscriptionCategory')
                ->post('{category}/edit', 'Front\GymSubscriptionCategoryFrontController@update');
        });

        // Delete subscription category - delete permission
        Route::name('sw.deleteSubscriptionCategory')
            ->get('{category}/delete', 'Front\GymSubscriptionCategoryFrontController@destroy');
    });

