<?php

Route::prefix('pt/class')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listPTClass')
            ->get('/', 'Front\GymPTClassFrontController@index');
        Route::name('sw.createPTClass')
            ->get('create', 'Front\GymPTClassFrontController@create');
        Route::name('sw.createPTClass')
            ->post('create', 'Front\GymPTClassFrontController@store');

        Route::name('sw.editPTClass')
            ->get('{class}/edit', 'Front\GymPTClassFrontController@edit');
        Route::name('sw.editPTClass')
            ->post('{class}/edit', 'Front\GymPTClassFrontController@update');
        Route::name('sw.deletePTClass')
            ->get('{class}/delete', 'Front\GymPTClassFrontController@destroy');

    });
