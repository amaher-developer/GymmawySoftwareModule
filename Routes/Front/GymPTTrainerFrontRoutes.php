<?php

Route::prefix('pt/trainer')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listPTTrainer')
            ->get('/', 'Front\GymPTTrainerFrontController@index');
        Route::name('sw.createPTTrainer')
            ->get('create', 'Front\GymPTTrainerFrontController@create');
        Route::name('sw.storePTTrainer')
            ->post('create', 'Front\GymPTTrainerFrontController@store');

        Route::name('sw.editPTTrainer')
            ->get('{trainer}/edit', 'Front\GymPTTrainerFrontController@edit');
        Route::name('sw.updatePTTrainer')
            ->post('{trainer}/edit', 'Front\GymPTTrainerFrontController@update');
        Route::name('sw.deletePTTrainer')
            ->get('{trainer}/delete', 'Front\GymPTTrainerFrontController@destroy');

        Route::name('sw.pendingPTTrainerCommissions')
            ->get('{trainer}/pending-commissions', 'Front\GymPTTrainerFrontController@pendingCommissions');

        // PT Trainer Subscription routes
        Route::name('sw.createPTTrainerSubscription')
            ->get('subscription/create', 'Front\GymPTTrainerFrontController@createSubscription');
        Route::name('sw.storePTTrainerSubscription')
            ->post('subscription/create', 'Front\GymPTTrainerFrontController@storeSubscription');
        Route::name('sw.showPTTrainerSubscription')
            ->get('subscription/{subscription}/show', 'Front\GymPTTrainerFrontController@showSubscription');
        Route::name('sw.editPTTrainerSubscription')
            ->get('subscription/{trainer}/edit', 'Front\GymPTTrainerFrontController@editSubscription');
        Route::name('sw.updatePTTrainerSubscription')
            ->post('subscription/{trainer}/edit', 'Front\GymPTTrainerFrontController@updateSubscription');
        Route::name('sw.deletePTTrainerSubscription')
            ->get('subscription/{subscription}/delete', 'Front\GymPTTrainerFrontController@destroySubscription');

        Route::name('sw.listPTTrainerReport')
            ->get('/reports', 'Front\GymPTTrainerFrontController@reports');

        Route::name('sw.createTrainerPayPercentageAmountForm')
            ->post('create-pt-trainer-pay-percentage-amount-ajax', 'Front\GymPTTrainerFrontController@createTrainerPayPercentageAmountForm');

    });
