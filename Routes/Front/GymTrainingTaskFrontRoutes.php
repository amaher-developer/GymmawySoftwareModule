<?php

Route::prefix('training/task')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listTrainingTask')
            ->get('/', 'Front\GymTrainingTaskFrontController@index');
        Route::name('sw.createTrainingTask')
            ->get('create', 'Front\GymTrainingTaskFrontController@create');
        Route::name('sw.createTrainingTask')
            ->post('create', 'Front\GymTrainingTaskFrontController@store');

        Route::name('sw.editTrainingTask')
            ->get('{Task}/edit', 'Front\GymTrainingTaskFrontController@edit');
        Route::name('sw.editTrainingTask')
            ->post('{Task}/edit', 'Front\GymTrainingTaskFrontController@update');
        Route::name('sw.deleteTrainingTask')
            ->get('{Task}/delete', 'Front\GymTrainingTaskFrontController@destroy');



        Route::name('sw.exportTrainingTaskPDF')
            ->get('/pdf', 'Front\GymTrainingTaskFrontController@exportPDF');
        Route::name('sw.exportTrainingTaskExcel')
            ->get('/excel', 'Front\GymTrainingTaskFrontController@exportExcel');

    });
