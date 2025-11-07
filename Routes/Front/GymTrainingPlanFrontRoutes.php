<?php

use Illuminate\Support\Facades\Route;

Route::prefix('training/plan')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.listTrainingPlan')
            ->get('/', 'Front\GymTrainingPlanFrontController@index');
        
        Route::name('sw.createTrainingPlan')
            ->get('create', 'Front\GymTrainingPlanFrontController@create');
        
        Route::name('sw.storeTrainingPlan')
            ->post('create', 'Front\GymTrainingPlanFrontController@store');
        
        Route::name('sw.editTrainingPlan')
            ->get('{plan}/edit', 'Front\GymTrainingPlanFrontController@edit');
        
        Route::name('sw.updateTrainingPlan')
            ->post('{plan}/edit', 'Front\GymTrainingPlanFrontController@update');
        
        Route::name('sw.deleteTrainingPlan')
            ->get('{plan}/delete', 'Front\GymTrainingPlanFrontController@destroy');
    });

