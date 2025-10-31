<?php

Route::prefix('block-member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listBlockMember')
            ->get('/', 'Front\GymBlockMemberFrontController@index');
        Route::name('sw.exportBlockMemberPDF')
            ->get('/pdf', 'Front\GymBlockMemberFrontController@exportPDF');
        Route::name('sw.exportBlockMemberExcel')
            ->get('/excel', 'Front\GymBlockMemberFrontController@exportExcel');
        Route::name('sw.createBlockMember')
            ->get('create', 'Front\GymBlockMemberFrontController@create');
        Route::name('sw.createBlockMember')
            ->post('create', 'Front\GymBlockMemberFrontController@store');
        Route::name('sw.editBlockMember')
            ->get('{member}/edit', 'Front\GymBlockMemberFrontController@edit');
        Route::name('sw.editBlockMember')
            ->post('{member}/edit', 'Front\GymBlockMemberFrontController@update');
        Route::name('sw.deleteBlockMember')
            ->get('{member}/delete', 'Front\GymBlockMemberFrontController@destroy');


        Route::name('showAllBlockMember')
            ->get('/json/datatable', 'Front\GymBlockMemberFrontController@showAll');

    });
