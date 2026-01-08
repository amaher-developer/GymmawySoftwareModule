<?php

Route::prefix('pt/trainer')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List PT trainers - view permission
        Route::name('sw.listPTTrainer')
            ->get('/', 'Front\GymPTTrainerFrontController@index');

        // Create PT trainer - create permission
        Route::group(['defaults' => ['permission' => 'createPTTrainer']], function () {
            Route::name('sw.createPTTrainer')
                ->get('create', 'Front\GymPTTrainerFrontController@create');
            Route::name('sw.storePTTrainer')
                ->post('create', 'Front\GymPTTrainerFrontController@store');
        });

        // Edit PT trainer - edit permission
        Route::group(['defaults' => ['permission' => 'editPTTrainer']], function () {
            Route::name('sw.editPTTrainer')
                ->get('{trainer}/edit', 'Front\GymPTTrainerFrontController@edit');
            Route::name('sw.updatePTTrainer')
                ->post('{trainer}/edit', 'Front\GymPTTrainerFrontController@update');
        });

        // Delete PT trainer - delete permission
        Route::name('sw.deletePTTrainer')
            ->get('{trainer}/delete', 'Front\GymPTTrainerFrontController@destroy');

        // PT trainer pending commissions - view permission
        Route::name('sw.pendingPTTrainerCommissions')
            ->get('{trainer}/pending-commissions', 'Front\GymPTTrainerFrontController@pendingCommissions');

        // Create PT trainer subscription - create permission
        Route::group(['defaults' => ['permission' => 'createPTTrainerSubscription']], function () {
            Route::name('sw.createPTTrainerSubscription')
                ->get('subscription/create', 'Front\GymPTTrainerFrontController@createSubscription');
            Route::name('sw.storePTTrainerSubscription')
                ->post('subscription/create', 'Front\GymPTTrainerFrontController@storeSubscription');
        });

        // Show PT trainer subscription - view permission
        Route::name('sw.showPTTrainerSubscription')
            ->get('subscription/{subscription}/show', 'Front\GymPTTrainerFrontController@showSubscription');

        // Edit PT trainer subscription - edit permission
        Route::group(['defaults' => ['permission' => 'editPTTrainerSubscription']], function () {
            Route::name('sw.editPTTrainerSubscription')
                ->get('subscription/{trainer}/edit', 'Front\GymPTTrainerFrontController@editSubscription');
            Route::name('sw.updatePTTrainerSubscription')
                ->post('subscription/{trainer}/edit', 'Front\GymPTTrainerFrontController@updateSubscription');
        });

        // Delete PT trainer subscription - delete permission
        Route::name('sw.deletePTTrainerSubscription')
            ->get('subscription/{subscription}/delete', 'Front\GymPTTrainerFrontController@destroySubscription');

        // PT trainer reports - view permission
        Route::name('sw.listPTTrainerReport')
            ->get('/reports', 'Front\GymPTTrainerFrontController@reports');

        // Create trainer pay percentage amount - create permission
        Route::group(['defaults' => ['permission' => 'createTrainerPayPercentageAmountForm']], function () {
            Route::name('sw.createTrainerPayPercentageAmountForm')
                ->post('create-pt-trainer-pay-percentage-amount-ajax', 'Front\GymPTTrainerFrontController@createTrainerPayPercentageAmountForm');
        });

    });
