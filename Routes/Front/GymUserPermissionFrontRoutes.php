<?php


Route::prefix('user/permission')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.listUserPermission')
            ->get('/', 'Front\GymUserPermissionFrontController@index');
        Route::name('sw.createUserPermission')
            ->get('create', 'Front\GymUserPermissionFrontController@create');
        Route::name('sw.createUserPermission')
            ->post('create', 'Front\GymUserPermissionFrontController@store');
        Route::name('sw.editUserPermission')
            ->get('{permission}/edit', 'Front\GymUserPermissionFrontController@edit');
        Route::name('sw.editUserPermission')
            ->post('{permission}/edit', 'Front\GymUserPermissionFrontController@update');
        Route::name('sw.deleteUserPermission')
            ->get('{permission}/delete', 'Front\GymUserPermissionFrontController@destroy');
    });

