<?php


Route::prefix('store/category')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List store categories - view permission
        Route::name('sw.listStoreCategory')
            ->get('/', 'Front\GymStoreCategoryFrontController@index');

        // Export store categories - view permission
        Route::name('sw.exportStoreCategoryPDF')
            ->get('/pdf', 'Front\GymStoreCategoryFrontController@exportPDF');
        Route::name('sw.exportStoreCategoryExcel')
            ->get('/excel', 'Front\GymStoreCategoryFrontController@exportExcel');

        // Create store category - create permission
        Route::group(['defaults' => ['permission' => 'createStoreCategory']], function () {
            Route::name('sw.createStoreCategory')
                ->get('create', 'Front\GymStoreCategoryFrontController@create');
            Route::name('sw.createStoreCategory')
                ->post('create', 'Front\GymStoreCategoryFrontController@store');
        });

        // Edit store category - edit permission
        Route::group(['defaults' => ['permission' => 'editStoreCategory']], function () {
            Route::name('sw.editStoreCategory')
                ->get('{category}/edit', 'Front\GymStoreCategoryFrontController@edit');
            Route::name('sw.editStoreCategory')
                ->post('{category}/edit', 'Front\GymStoreCategoryFrontController@update');
        });

        // Delete store category - delete permission
        Route::name('sw.deleteStoreCategory')
            ->get('{category}/delete', 'Front\GymStoreCategoryFrontController@destroy');

    });
