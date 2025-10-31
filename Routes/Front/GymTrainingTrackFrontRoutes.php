<?php

Route::prefix('training/track')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
       
        Route::name('sw.listTrainingTrack')
            ->get('/', 'Front\GymTrainingTrackFrontController@index');
        Route::name('sw.createTrainingTrack')
            ->get('create', 'Front\GymTrainingTrackFrontController@create');
        Route::name('sw.createTrainingTrack')
            ->post('create', 'Front\GymTrainingTrackFrontController@store');

        Route::name('sw.editTrainingTrack')
            ->get('{Track}/edit', 'Front\GymTrainingTrackFrontController@edit');
        Route::name('sw.editTrainingTrack')
            ->post('{Track}/edit', 'Front\GymTrainingTrackFrontController@update');
        Route::name('sw.deleteTrainingTrack')
            ->get('{Track}/delete', 'Front\GymTrainingTrackFrontController@destroy');



        Route::name('sw.exportTrainingTrackPDF')
            ->get('/pdf', 'Front\GymTrainingTrackFrontController@exportPDF');
        Route::name('sw.exportTrainingTrackExcel')
            ->get('/excel', 'Front\GymTrainingTrackFrontController@exportExcel');

    });
