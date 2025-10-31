<?php


Route::prefix('category')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.listCategory')
            ->get('/', 'Front\GymCategoryFrontController@index');
        Route::name('sw.exportCategoryPDF')
            ->get('/pdf', 'Front\GymCategoryFrontController@exportPDF');
        Route::name('sw.exportCategoryExcel')
            ->get('/excel', 'Front\GymCategoryFrontController@exportExcel');
        Route::name('sw.createCategory')
            ->get('create', 'Front\GymCategoryFrontController@create');
        Route::name('sw.createCategory')
            ->post('create', 'Front\GymCategoryFrontController@store');
        Route::name('sw.editCategory')
            ->get('{category}/edit', 'Front\GymCategoryFrontController@edit');
        Route::name('sw.editCategory')
            ->post('{category}/edit', 'Front\GymCategoryFrontController@update');
        Route::name('sw.deleteCategory')
            ->get('{category}/delete', 'Front\GymCategoryFrontController@destroy');
    });
