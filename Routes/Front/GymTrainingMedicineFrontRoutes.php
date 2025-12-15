<?php

// Training Medicine Routes
Route::prefix('training/medicine')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List training medicines - view permission
        Route::group(['defaults' => ['permission' => 'listTrainingMedicine']], function () {
            Route::name('sw.listTrainingMedicine')
                ->get('/', 'Front\GymTrainingMedicineFrontController@index');
        });

        // Create training medicine - create permission
        Route::group(['defaults' => ['permission' => 'createTrainingMedicine']], function () {
            Route::name('sw.createTrainingMedicine')
                ->get('create', 'Front\GymTrainingMedicineFrontController@create');
            Route::name('sw.createTrainingMedicine')
                ->post('create', 'Front\GymTrainingMedicineFrontController@store');
        });

        // Edit training medicine - edit permission
        Route::group(['defaults' => ['permission' => 'editTrainingMedicine']], function () {
            Route::name('sw.editTrainingMedicine')
                ->get('{medicine}/edit', 'Front\GymTrainingMedicineFrontController@edit');
            Route::name('sw.editTrainingMedicine')
                ->post('{medicine}/edit', 'Front\GymTrainingMedicineFrontController@update');
        });

        // Delete training medicine - delete permission
        Route::group(['defaults' => ['permission' => 'deleteTrainingMedicine']], function () {
            Route::name('sw.deleteTrainingMedicine')
                ->get('{medicine}/delete', 'Front\GymTrainingMedicineFrontController@destroy');
        });

    });
