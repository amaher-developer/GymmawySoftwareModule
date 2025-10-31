<?php

Route::prefix('activity')
    ->middleware(['api'])
    ->group(function () {
        Route::post('', 'Api\GymActivityApiController@activities');
        Route::post('/{id}', 'Api\GymActivityApiController@activity');
        Route::post('/reservation/{id}', 'Api\GymActivityApiController@activityReservation');

    });
