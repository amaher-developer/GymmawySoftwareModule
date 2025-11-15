<?php

Route::prefix('pt/sessions')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.listPTSessions')
            ->get('/', 'Front\GymPTSessionFrontController@index');

        Route::name('sw.showPTSession')
            ->get('{virtualSession}', 'Front\GymPTSessionFrontController@show');
    });


