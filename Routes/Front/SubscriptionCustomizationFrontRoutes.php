<?php

Route::prefix('sw/subscriptions/{subscriptionId}')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // Products — mapped to editSubscription permission
        Route::group(['defaults' => ['permission' => 'editSubscription']], function () {
            Route::name('sw.subscriptionProducts.index')->get('/products', 'Front\GymSubscriptionProductFrontController@index');
            Route::name('sw.subscriptionProducts.store')->post('/products', 'Front\GymSubscriptionProductFrontController@store');
            Route::name('sw.subscriptionProducts.update')->put('/products/{id}', 'Front\GymSubscriptionProductFrontController@update');
            Route::name('sw.subscriptionProducts.destroy')->delete('/products/{id}', 'Front\GymSubscriptionProductFrontController@destroy');
            Route::name('sw.subscriptionProducts.reorder')->post('/products/reorder', 'Front\GymSubscriptionProductFrontController@reorder');
        });

        // Option Groups — mapped to editSubscription permission
        Route::group(['defaults' => ['permission' => 'editSubscription']], function () {
            Route::name('sw.subscriptionOptionGroups.index')->get('/option-groups', 'Front\GymSubscriptionOptionGroupFrontController@index');
            Route::name('sw.subscriptionOptionGroups.store')->post('/option-groups', 'Front\GymSubscriptionOptionGroupFrontController@store');
            Route::name('sw.subscriptionOptionGroups.update')->put('/option-groups/{id}', 'Front\GymSubscriptionOptionGroupFrontController@update');
            Route::name('sw.subscriptionOptionGroups.destroy')->delete('/option-groups/{id}', 'Front\GymSubscriptionOptionGroupFrontController@destroy');
        });

        // Options inside a group — mapped to editSubscription permission
        Route::group(['defaults' => ['permission' => 'editSubscription']], function () {
            Route::name('sw.subscriptionOptions.store')->post('/option-groups/{groupId}/options', 'Front\GymSubscriptionOptionFrontController@store');
            Route::name('sw.subscriptionOptions.update')->put('/option-groups/{groupId}/options/{id}', 'Front\GymSubscriptionOptionFrontController@update');
            Route::name('sw.subscriptionOptions.destroy')->delete('/option-groups/{groupId}/options/{id}', 'Front\GymSubscriptionOptionFrontController@destroy');
        });

        // Live Pricing — mapped to editSubscription permission
        Route::group(['defaults' => ['permission' => 'editSubscription']], function () {
            Route::name('sw.subscription.calculatePrice')->post('/calculate-price', 'Front\GymSubscriptionPricingFrontController@calculate');
            Route::name('sw.subscription.options')->get('/options', 'Front\GymSubscriptionPricingFrontController@options');
            Route::name('sw.subscription.memberActivities')->get('/member-activities', 'Front\GymSubscriptionPricingFrontController@memberActivities');
        });
    });
