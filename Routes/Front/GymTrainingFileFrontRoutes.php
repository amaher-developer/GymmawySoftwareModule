<?php

Route::prefix('training/file')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listTrainingFile')
            ->get('/', 'Front\GymTrainingFileFrontController@index');
        Route::name('sw.createTrainingFile')
            ->get('create', 'Front\GymTrainingFileFrontController@create');
        Route::name('sw.createTrainingFile')
            ->post('create', 'Front\GymTrainingFileFrontController@store');

        Route::name('sw.editTrainingFile')
            ->get('{File}/edit', 'Front\GymTrainingFileFrontController@edit');
        Route::name('sw.editTrainingFile')
            ->post('{File}/edit', 'Front\GymTrainingFileFrontController@update');
        Route::name('sw.deleteTrainingFile')
            ->get('{File}/delete', 'Front\GymTrainingFileFrontController@destroy');



        Route::name('sw.exportTrainingFilePDF')
            ->get('/pdf', 'Front\GymTrainingFileFrontController@exportPDF');
        Route::name('sw.exportTrainingFileExcel')
            ->get('/excel', 'Front\GymTrainingFileFrontController@exportExcel');

    });
