<?php

// Training Medicine Routes
Route::prefix('training/medicine')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listTrainingMedicine')
            ->get('/', 'Front\GymTrainingMedicineFrontController@index');
        
        Route::name('sw.createTrainingMedicine')
            ->get('create', 'Front\GymTrainingMedicineFrontController@create');
        
        Route::name('sw.createTrainingMedicine')
            ->post('create', 'Front\GymTrainingMedicineFrontController@store');
        
        Route::name('sw.editTrainingMedicine')
            ->get('{medicine}/edit', 'Front\GymTrainingMedicineFrontController@edit');
        
        Route::name('sw.editTrainingMedicine')
            ->post('{medicine}/edit', 'Front\GymTrainingMedicineFrontController@update');
        
        Route::name('sw.deleteTrainingMedicine')
            ->get('{medicine}/delete', 'Front\GymTrainingMedicineFrontController@destroy');
    });
