<?php

Route::prefix('potential-member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listPotentialMember')
            ->get('/', 'Front\GymPotentialMemberFrontController@index');
        Route::name('sw.exportPotentialMemberPDF')
            ->get('/pdf', 'Front\GymPotentialMemberFrontController@exportPDF');
        Route::name('sw.exportPotentialMemberExcel')
            ->get('/excel', 'Front\GymPotentialMemberFrontController@exportExcel');
        Route::name('sw.createPotentialMember')
            ->get('create', 'Front\GymPotentialMemberFrontController@create');
        Route::name('sw.createPotentialMember')
            ->post('create', 'Front\GymPotentialMemberFrontController@store');
        Route::name('sw.editPotentialMember')
            ->get('{member}/edit', 'Front\GymPotentialMemberFrontController@edit');
        Route::name('sw.editPotentialMember')
            ->post('{member}/edit', 'Front\GymPotentialMemberFrontController@update');
        Route::name('sw.deletePotentialMember')
            ->get('{member}/delete', 'Front\GymPotentialMemberFrontController@destroy');


        Route::name('showAllPotentialMember')
            ->get('/json/datatable', 'Front\GymPotentialMemberFrontController@showAll');

        Route::name('sw.updatePotentialMember')
            ->get('/json/updatePotentialMember', 'Front\GymPotentialMemberFrontController@updatePotentialMember');

    });
