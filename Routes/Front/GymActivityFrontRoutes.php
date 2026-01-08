<?php

Route::prefix('activity')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List activities - view permission
        Route::name('sw.listActivity')
            ->get('/', 'Front\GymActivityFrontController@index');

        // Export activities - view permission
        Route::name('sw.exportActivityPDF')
            ->get('/pdf', 'Front\GymActivityFrontController@exportPDF');
        Route::name('sw.exportActivityExcel')
            ->get('/excel', 'Front\GymActivityFrontController@exportExcel');

        // Create activity - create permission
        Route::group(['defaults' => ['permission' => 'createActivity']], function () {
            Route::name('sw.createActivity')
                ->get('create', 'Front\GymActivityFrontController@create');
            Route::name('sw.createActivity')
                ->post('create', 'Front\GymActivityFrontController@store');
        });

        // Edit activity - edit permission
        Route::group(['defaults' => ['permission' => 'editActivity']], function () {
            Route::name('sw.editActivity')
                ->get('{activity}/edit', 'Front\GymActivityFrontController@edit');
            Route::name('sw.editActivity')
                ->post('{activity}/edit', 'Front\GymActivityFrontController@update');
        });

        // Delete activity - delete permission
        Route::name('sw.deleteActivity')
            ->get('{activity}/delete', 'Front\GymActivityFrontController@destroy');
    });
