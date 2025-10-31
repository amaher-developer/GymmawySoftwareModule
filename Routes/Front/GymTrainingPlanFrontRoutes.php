<?php

Route::prefix('training/plan')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listTrainingPlan')
            ->get('/', 'Front\GymTrainingPlanFrontController@index');
        Route::name('sw.createTrainingPlan')
            ->get('create', 'Front\GymTrainingPlanFrontController@create');
        Route::name('sw.createTrainingPlan')
            ->post('create', 'Front\GymTrainingPlanFrontController@store');

        Route::name('sw.editTrainingPlan')
            ->get('{plan}/edit', 'Front\GymTrainingPlanFrontController@edit');
        Route::name('sw.editTrainingPlan')
            ->post('{plan}/edit', 'Front\GymTrainingPlanFrontController@update');
        Route::name('sw.deleteTrainingPlan')
            ->get('{plan}/delete', 'Front\GymTrainingPlanFrontController@destroy');



        Route::name('sw.exportTrainingPlanPDF')
            ->get('/pdf', 'Front\GymTrainingPlanFrontController@exportPDF');
        Route::name('sw.exportTrainingPlanExcel')
            ->get('/excel', 'Front\GymTrainingPlanFrontController@exportExcel');

    });
