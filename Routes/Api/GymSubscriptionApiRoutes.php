<?php

Route::prefix('subscription')
    ->middleware(['api'])
    ->group(function () {
        Route::post('', 'Api\GymSubscriptionApiController@subscriptions');
        Route::post('/{id}', 'Api\GymSubscriptionApiController@subscription');
        Route::post('/reservation/{id}', 'Api\GymSubscriptionApiController@subscriptionReservation');
});
