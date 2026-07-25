<?php

Route::prefix('pt-training')
    ->middleware(['api'])
    ->group(function () {
        Route::match(['get', 'post'], '', 'Api\GymPTApiController@trainings');
        Route::match(['get', 'post'], '/{id}', 'Api\GymPTApiController@training');
        Route::post('/reservation/{id}', 'Api\GymPTApiController@trainingReservation');
});
