<?php

Route::prefix('store')
    ->middleware(['api'])
    ->group(function () {
        Route::match(['get', 'post'], '', 'Api\GymStoreApiController@stores');
        Route::match(['get', 'post'], '/{id}', 'Api\GymStoreApiController@store');
});
