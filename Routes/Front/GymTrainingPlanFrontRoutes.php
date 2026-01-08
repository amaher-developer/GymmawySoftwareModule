<?php

use Illuminate\Support\Facades\Route;

Route::prefix('training/plan')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List training plans - view permission
        Route::name('sw.listTrainingPlan')
            ->get('/', 'Front\GymTrainingPlanFrontController@index');

        // Create training plan - create permission
        Route::group(['defaults' => ['permission' => 'createTrainingPlan']], function () {
            Route::name('sw.createTrainingPlan')
                ->get('create', 'Front\GymTrainingPlanFrontController@create');
            Route::name('sw.storeTrainingPlan')
                ->post('create', 'Front\GymTrainingPlanFrontController@store');
        });

        // Edit training plan - edit permission
        Route::group(['defaults' => ['permission' => 'editTrainingPlan']], function () {
            Route::name('sw.editTrainingPlan')
                ->get('{plan}/edit', 'Front\GymTrainingPlanFrontController@edit');
            Route::name('sw.updateTrainingPlan')
                ->post('{plan}/edit', 'Front\GymTrainingPlanFrontController@update');
        });

        // Delete training plan - delete permission
        Route::name('sw.deleteTrainingPlan')
            ->get('{plan}/delete', 'Front\GymTrainingPlanFrontController@destroy');

    });
