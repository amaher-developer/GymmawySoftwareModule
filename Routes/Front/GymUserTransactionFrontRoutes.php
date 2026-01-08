<?php

Route::prefix('user/finance/employee-transaction')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List user transactions - view permission
        Route::name('sw.listUserTransaction')
            ->get('/', 'Front\GymUserTransactionFrontController@index');

        // Create user transaction - create permission
        Route::group(['defaults' => ['permission' => 'createUserTransaction']], function () {
            Route::name('sw.createUserTransaction')
                ->get('create', 'Front\GymUserTransactionFrontController@create');
            Route::name('sw.storeUserTransaction')
                ->post('store', 'Front\GymUserTransactionFrontController@store');
        });

        // Edit user transaction - edit permission
        Route::group(['defaults' => ['permission' => 'editUserTransaction']], function () {
            Route::name('sw.editUserTransaction')
                ->get('{id}/edit', 'Front\GymUserTransactionFrontController@edit');
            Route::name('sw.updateUserTransaction')
                ->post('{id}/update', 'Front\GymUserTransactionFrontController@update');
        });

        // Delete user transaction - delete permission
        Route::name('sw.deleteUserTransaction')
            ->get('{id}/delete', 'Front\GymUserTransactionFrontController@destroy');

    });
