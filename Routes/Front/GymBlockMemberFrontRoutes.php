<?php

Route::prefix('block-member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List block members - view permission
        Route::group(['defaults' => ['permission' => 'listBlockMember']], function () {
            Route::name('sw.listBlockMember')
                ->get('/', 'Front\GymBlockMemberFrontController@index');
            Route::name('showAllBlockMember')
                ->get('/json/datatable', 'Front\GymBlockMemberFrontController@showAll');
        });

        // Export block members - view permission
        Route::group(['defaults' => ['permission' => 'listBlockMember']], function () {
            Route::name('sw.exportBlockMemberPDF')
                ->get('/pdf', 'Front\GymBlockMemberFrontController@exportPDF');
            Route::name('sw.exportBlockMemberExcel')
                ->get('/excel', 'Front\GymBlockMemberFrontController@exportExcel');
        });

        // Create block member - create permission
        Route::group(['defaults' => ['permission' => 'createBlockMember']], function () {
            Route::name('sw.createBlockMember')
                ->get('create', 'Front\GymBlockMemberFrontController@create');
            Route::name('sw.createBlockMember')
                ->post('create', 'Front\GymBlockMemberFrontController@store');
        });

        // Edit block member - edit permission
        Route::group(['defaults' => ['permission' => 'editBlockMember']], function () {
            Route::name('sw.editBlockMember')
                ->get('{member}/edit', 'Front\GymBlockMemberFrontController@edit');
            Route::name('sw.editBlockMember')
                ->post('{member}/edit', 'Front\GymBlockMemberFrontController@update');
        });

        // Delete block member - delete permission
        Route::group(['defaults' => ['permission' => 'deleteBlockMember']], function () {
            Route::name('sw.deleteBlockMember')
                ->get('{member}/delete', 'Front\GymBlockMemberFrontController@destroy');
        });

    });
