<?php


Route::prefix('store/category')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.listStoreCategory')
            ->get('/', 'Front\GymStoreCategoryFrontController@index');
        Route::name('sw.exportStoreCategoryPDF')
            ->get('/pdf', 'Front\GymStoreCategoryFrontController@exportPDF');
        Route::name('sw.exportStoreCategoryExcel')
            ->get('/excel', 'Front\GymStoreCategoryFrontController@exportExcel');
        Route::name('sw.createStoreCategory')
            ->get('create', 'Front\GymStoreCategoryFrontController@create');
        Route::name('sw.createStoreCategory')
            ->post('create', 'Front\GymStoreCategoryFrontController@store');
        Route::name('sw.editStoreCategory')
            ->get('{category}/edit', 'Front\GymStoreCategoryFrontController@edit');
        Route::name('sw.editStoreCategory')
            ->post('{category}/edit', 'Front\GymStoreCategoryFrontController@update');
        Route::name('sw.deleteStoreCategory')
            ->get('{category}/delete', 'Front\GymStoreCategoryFrontController@destroy');
    });

