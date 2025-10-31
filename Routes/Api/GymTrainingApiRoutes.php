<?php

Route::prefix('training-plan')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::post('', 'Api\GymTrainingApiController@plans');
        Route::post('/{id}', 'Api\GymTrainingApiController@plan');
});

Route::prefix('training-track')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::post('', 'Api\GymTrainingApiController@tracks');
        Route::post('/{id}', 'Api\GymTrainingApiController@track');
});
