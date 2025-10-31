<?php

Route::prefix('customer')
    ->middleware(['sw_customer'])
    ->group(function () {

        Route::name('sw.showCustomerProfile')
            ->get('/', 'Front\GymCustomerFrontController@show');
        Route::name('sw.customerSubscriptions')
            ->get('/subscriptions', 'Front\GymCustomerFrontController@subscriptions');
        Route::name('sw.customerActivities')
            ->get('/activities', 'Front\GymCustomerFrontController@activities');
        Route::name('sw.customerPT')
            ->get('/pt', 'Front\GymCustomerFrontController@pt');
        Route::name('sw.customerTracking')
            ->get('/tracking', 'Front\GymCustomerFrontController@tracking');
        Route::name('sw.customerTraining')
            ->get('/training', 'Front\GymCustomerFrontController@training');
        Route::name('sw.customerReview')
            ->get('/review', 'Front\GymCustomerFrontController@review');

        Route::name('sw.customerLogin')
            ->get('/login', 'Front\GymCustomerFrontController@login');
        Route::name('sw.customerLoginSubmit')
            ->post('/login', 'Front\GymCustomerFrontController@loginSubmit');

        Route::name('sw.customerLogout')
            ->get('/logout', 'Front\GymCustomerFrontController@logout');


    });
