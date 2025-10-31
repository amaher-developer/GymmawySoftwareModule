<?php

Route::prefix('order')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('listUserGymOrder')
            ->get('/', 'Front\GymOrderFrontController@index');

        Route::name('createUserGymOrder')
            ->get('create/{member}', 'Front\GymOrderFrontController@create');
        Route::name('storeUserGymOrder')
            ->post('create/{member}', 'Front\GymOrderFrontController@store');
        Route::name('showAllUserGymOrder')
            ->get('/json/datatable', 'Front\GymOrderFrontController@showAll');
//        Route::name('editUserGymOrder')
//            ->get('{order}/edit', 'Front\GymOrderFrontController@edit');
//        Route::name('editUserGymOrder')
//            ->post('{order}/edit', 'Front\GymOrderFrontController@update');
        Route::name('deleteUserGymOrder')
            ->get('{order}/delete', 'Front\GymOrderFrontController@destroy');


        Route::name('sw.uploadContractGymOrder')
            ->post('/ajax_upload/upload', 'Front\GymOrderFrontController@uploadContractGymOrder');

    });
