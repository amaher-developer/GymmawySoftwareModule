<?php


Route::prefix('user/permission')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List user permissions - view permission
        Route::group(['defaults' => ['permission' => 'listUserPermission']], function () {
            Route::name('sw.listUserPermission')
                ->get('/', 'Front\GymUserPermissionFrontController@index');
        });

        // Create user permission - create permission
        Route::group(['defaults' => ['permission' => 'createUserPermission']], function () {
            Route::name('sw.createUserPermission')
                ->get('create', 'Front\GymUserPermissionFrontController@create');
            Route::name('sw.createUserPermission')
                ->post('create', 'Front\GymUserPermissionFrontController@store');
        });

        // Edit user permission - edit permission
        Route::group(['defaults' => ['permission' => 'editUserPermission']], function () {
            Route::name('sw.editUserPermission')
                ->get('{permission}/edit', 'Front\GymUserPermissionFrontController@edit');
            Route::name('sw.editUserPermission')
                ->post('{permission}/edit', 'Front\GymUserPermissionFrontController@update');
        });

        // Delete user permission - delete permission
        Route::group(['defaults' => ['permission' => 'deleteUserPermission']], function () {
            Route::name('sw.deleteUserPermission')
                ->get('{permission}/delete', 'Front\GymUserPermissionFrontController@destroy');
        });

    });
