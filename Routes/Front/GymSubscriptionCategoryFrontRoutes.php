<?php


Route::prefix('subscription-category')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.listSubscriptionCategory')
            ->get('/', 'Front\GymSubscriptionCategoryFrontController@index');
        Route::name('sw.exportSubscriptionCategoryPDF')
            ->get('/pdf', 'Front\GymSubscriptionCategoryFrontController@exportPDF');
        Route::name('sw.exportSubscriptionCategoryExcel')
            ->get('/excel', 'Front\GymSubscriptionCategoryFrontController@exportExcel');
        Route::name('sw.createSubscriptionCategory')
            ->get('create', 'Front\GymSubscriptionCategoryFrontController@create');
        Route::name('sw.createSubscriptionCategory')
            ->post('create', 'Front\GymSubscriptionCategoryFrontController@store');
        Route::name('sw.editSubscriptionCategory')
            ->get('{category}/edit', 'Front\GymSubscriptionCategoryFrontController@edit');
        Route::name('sw.editSubscriptionCategory')
            ->post('{category}/edit', 'Front\GymSubscriptionCategoryFrontController@update');
        Route::name('sw.deleteSubscriptionCategory')
            ->get('{category}/delete', 'Front\GymSubscriptionCategoryFrontController@destroy');
    });

