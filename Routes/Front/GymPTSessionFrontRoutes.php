<?php

Route::prefix('pt/sessions')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List PT sessions - view permission
        Route::group(['defaults' => ['permission' => 'listPTSessions']], function () {
            Route::name('sw.listPTSessions')
                ->get('/', 'Front\GymPTSessionFrontController@index');
        });

        // Show PT session - view permission
        Route::group(['defaults' => ['permission' => 'showPTSession']], function () {
            Route::name('sw.showPTSession')
                ->get('{virtualSession}', 'Front\GymPTSessionFrontController@show');
        });

    });

