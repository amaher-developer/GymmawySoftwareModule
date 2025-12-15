<?php

Route::prefix('order')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List gym orders - view permission
        Route::group(['defaults' => ['permission' => 'listUserGymOrder']], function () {
            Route::name('listUserGymOrder')
                ->get('/', 'Front\GymOrderFrontController@index');
            Route::name('showAllUserGymOrder')
                ->get('/json/datatable', 'Front\GymOrderFrontController@showAll');
        });

        // Create gym order - create permission
        Route::group(['defaults' => ['permission' => 'createUserGymOrder']], function () {
            Route::name('createUserGymOrder')
                ->get('create/{member}', 'Front\GymOrderFrontController@create');
            Route::name('storeUserGymOrder')
                ->post('create/{member}', 'Front\GymOrderFrontController@store');
        });

        // Delete gym order - delete permission
        Route::group(['defaults' => ['permission' => 'deleteUserGymOrder']], function () {
            Route::name('deleteUserGymOrder')
                ->get('{order}/delete', 'Front\GymOrderFrontController@destroy');
        });

        // Upload contract for gym order - edit permission
        Route::group(['defaults' => ['permission' => 'uploadContractGymOrder']], function () {
            Route::name('sw.uploadContractGymOrder')
                ->post('/ajax_upload/upload', 'Front\GymOrderFrontController@uploadContractGymOrder');
        });

    });
