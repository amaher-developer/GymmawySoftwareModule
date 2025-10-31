<?php

Route::prefix('store')
    ->middleware(['api'])
    ->group(function () {
        Route::post('', 'Api\GymStoreApiController@stores');
        Route::post('/{id}', 'Api\GymStoreApiController@store');
});
