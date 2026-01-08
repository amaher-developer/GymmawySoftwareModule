<?php

Route::prefix('customer')
    ->middleware(['sw_customer'])
    ->group(function () {

        // Customer login - authentication (no permission required)
        Route::name('sw.customerLogin')
            ->get('/login', 'Front\GymCustomerFrontController@login');
        Route::name('sw.customerLoginSubmit')
            ->post('/login', 'Front\GymCustomerFrontController@loginSubmit');

        // Customer logout - authentication (no permission required)
        Route::name('sw.customerLogout')
            ->get('/logout', 'Front\GymCustomerFrontController@logout');

        // Customer profile - view customer profile (customer authenticated routes)
        Route::name('sw.showCustomerProfile')
            ->get('/', 'Front\GymCustomerFrontController@show');

        // Customer subscriptions - view customer subscriptions
        Route::name('sw.customerSubscriptions')
            ->get('/subscriptions', 'Front\GymCustomerFrontController@subscriptions');

        // Customer activities - view customer activities
        Route::name('sw.customerActivities')
            ->get('/activities', 'Front\GymCustomerFrontController@activities');

        // Customer PT - view customer PT
        Route::name('sw.customerPT')
            ->get('/pt', 'Front\GymCustomerFrontController@pt');

        // Customer tracking - view customer tracking
        Route::name('sw.customerTracking')
            ->get('/tracking', 'Front\GymCustomerFrontController@tracking');

        // Customer training - view customer training
        Route::name('sw.customerTraining')
            ->get('/training', 'Front\GymCustomerFrontController@training');

        // Customer review - view customer review
        Route::name('sw.customerReview')
            ->get('/review', 'Front\GymCustomerFrontController@review');

    });
