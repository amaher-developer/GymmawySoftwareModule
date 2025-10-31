<?php

Route::prefix('training/member-log')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listTrainingMemberLog')
            ->get('/', 'Front\GymTrainingMemberLogFrontController@index');
        Route::name('sw.createTrainingMemberLog')
            ->get('create', 'Front\GymTrainingMemberLogFrontController@create');
        Route::name('sw.createTrainingMemberLog')
            ->post('create', 'Front\GymTrainingMemberLogFrontController@store');

        Route::name('sw.editTrainingMemberLog')
            ->get('{MemberLog}/edit', 'Front\GymTrainingMemberLogFrontController@edit');
        Route::name('sw.editTrainingMemberLog')
            ->post('{MemberLog}/edit', 'Front\GymTrainingMemberLogFrontController@update');
        Route::name('sw.deleteTrainingMemberLog')
            ->get('{MemberLog}/delete', 'Front\GymTrainingMemberLogFrontController@destroy');



        Route::name('sw.exportTrainingMemberLogPDF')
            ->get('/pdf', 'Front\GymTrainingMemberLogFrontController@exportPDF');
        Route::name('sw.exportTrainingMemberLogExcel')
            ->get('/excel', 'Front\GymTrainingMemberLogFrontController@exportExcel');

    });
