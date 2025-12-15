<?php

Route::prefix('category')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List categories - view permission
        Route::group(['defaults' => ['permission' => 'listCategory']], function () {
            Route::name('sw.listCategory')
                ->get('/', 'Front\GymCategoryFrontController@index');
        });

        // Export categories - view permission
        Route::group(['defaults' => ['permission' => 'listCategory']], function () {
            Route::name('sw.exportCategoryPDF')
                ->get('/pdf', 'Front\GymCategoryFrontController@exportPDF');
            Route::name('sw.exportCategoryExcel')
                ->get('/excel', 'Front\GymCategoryFrontController@exportExcel');
        });

        // Create category - create permission
        Route::group(['defaults' => ['permission' => 'createCategory']], function () {
            Route::name('sw.createCategory')
                ->get('create', 'Front\GymCategoryFrontController@create');
            Route::name('sw.createCategory')
                ->post('create', 'Front\GymCategoryFrontController@store');
        });

        // Edit category - edit permission
        Route::group(['defaults' => ['permission' => 'editCategory']], function () {
            Route::name('sw.editCategory')
                ->get('{category}/edit', 'Front\GymCategoryFrontController@edit');
            Route::name('sw.editCategory')
                ->post('{category}/edit', 'Front\GymCategoryFrontController@update');
        });

        // Delete category - delete permission
        Route::group(['defaults' => ['permission' => 'deleteCategory']], function () {
            Route::name('sw.deleteCategory')
                ->get('{category}/delete', 'Front\GymCategoryFrontController@destroy');
        });
    });
