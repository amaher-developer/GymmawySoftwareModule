<?php

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
            ->get('{Medicine}/edit', 'Front\GymTrainingMedicineFrontController@edit');
        Route::name('sw.editTrainingMedicine')
            ->post('{Medicine}/edit', 'Front\GymTrainingMedicineFrontController@update');
        Route::name('sw.deleteTrainingMedicine')
            ->get('{Medicine}/delete', 'Front\GymTrainingMedicineFrontController@destroy');



        Route::name('sw.exportTrainingMedicinePDF')
            ->get('/pdf', 'Front\GymTrainingMedicineFrontController@exportPDF');
        Route::name('sw.exportTrainingMedicineExcel')
            ->get('/excel', 'Front\GymTrainingMedicineFrontController@exportExcel');

    });
