<?php

Route::prefix('user/finance/employee-transaction')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.listUserTransaction')
            ->get('/', 'Front\GymUserTransactionFrontController@index');
        Route::name('sw.createUserTransaction')
            ->get('create', 'Front\GymUserTransactionFrontController@create');
        Route::name('sw.storeUserTransaction')
            ->post('store', 'Front\GymUserTransactionFrontController@store');
        Route::name('sw.editUserTransaction')
            ->get('{id}/edit', 'Front\GymUserTransactionFrontController@edit');
        Route::name('sw.updateUserTransaction')
            ->post('{id}/update', 'Front\GymUserTransactionFrontController@update');
        Route::name('sw.deleteUserTransaction')
            ->get('{id}/delete', 'Front\GymUserTransactionFrontController@destroy');
    });

