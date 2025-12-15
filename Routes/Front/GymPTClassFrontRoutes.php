<?php

Route::prefix('pt/class')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List PT classes - view permission
        Route::group(['defaults' => ['permission' => 'listPTClass']], function () {
            Route::name('sw.listPTClass')
                ->get('/', 'Front\GymPTClassFrontController@index');
        });

        // Create PT class - create permission
        Route::group(['defaults' => ['permission' => 'createPTClass']], function () {
            Route::name('sw.createPTClass')
                ->get('create', 'Front\GymPTClassFrontController@create');
            Route::name('sw.storePTClass')
                ->post('create', 'Front\GymPTClassFrontController@store');
        });

        // Edit PT class - edit permission
        Route::group(['defaults' => ['permission' => 'editPTClass']], function () {
            Route::name('sw.editPTClass')
                ->get('{class}/edit', 'Front\GymPTClassFrontController@edit');
            Route::name('sw.updatePTClass')
                ->match(['put', 'post'], '{class}/edit', 'Front\GymPTClassFrontController@update');
        });

        // Delete PT class - delete permission
        Route::group(['defaults' => ['permission' => 'deletePTClass']], function () {
            Route::name('sw.deletePTClass')
                ->get('{class}/delete', 'Front\GymPTClassFrontController@destroy');
        });

    });
