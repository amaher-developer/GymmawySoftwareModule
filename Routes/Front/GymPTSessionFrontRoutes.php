<?php

Route::prefix('pt/sessions')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List PT sessions - view permission
        Route::name('sw.listPTSessions')
            ->get('/', 'Front\GymPTSessionFrontController@index');

        // Show PT session - view permission
        Route::name('sw.showPTSession')
            ->get('{virtualSession}', 'Front\GymPTSessionFrontController@show');

    });

