<?php

Route::prefix('pt-training')
    ->middleware(['api'])
    ->group(function () {
        Route::post('', 'Api\GymPTApiController@trainings');
        Route::post('/{id}', 'Api\GymPTApiController@training');
        Route::post('/reservation/{id}', 'Api\GymPTApiController@trainingReservation');
});
