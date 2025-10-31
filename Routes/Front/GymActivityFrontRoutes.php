<?php


Route::prefix('activity')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.listActivity')
            ->get('/', 'Front\GymActivityFrontController@index');
        Route::name('sw.exportActivityPDF')
            ->get('/pdf', 'Front\GymActivityFrontController@exportPDF');
        Route::name('sw.exportActivityExcel')
            ->get('/excel', 'Front\GymActivityFrontController@exportExcel');
        Route::name('sw.createActivity')
            ->get('create', 'Front\GymActivityFrontController@create');
        Route::name('sw.createActivity')
            ->post('create', 'Front\GymActivityFrontController@store');
        Route::name('sw.editActivity')
            ->get('{activity}/edit', 'Front\GymActivityFrontController@edit');
        Route::name('sw.editActivity')
            ->post('{activity}/edit', 'Front\GymActivityFrontController@update');
        Route::name('sw.deleteActivity')
            ->get('{activity}/delete', 'Front\GymActivityFrontController@destroy');
    });
