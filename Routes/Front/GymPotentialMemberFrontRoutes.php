<?php

Route::prefix('potential-member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List potential members - view permission
        Route::group(['defaults' => ['permission' => 'listPotentialMember']], function () {
            Route::name('sw.listPotentialMember')
                ->get('/', 'Front\GymPotentialMemberFrontController@index');
            Route::name('showAllPotentialMember')
                ->get('/json/datatable', 'Front\GymPotentialMemberFrontController@showAll');
        });

        // Export potential members - view permission
        Route::group(['defaults' => ['permission' => 'listPotentialMember']], function () {
            Route::name('sw.exportPotentialMemberPDF')
                ->get('/pdf', 'Front\GymPotentialMemberFrontController@exportPDF');
            Route::name('sw.exportPotentialMemberExcel')
                ->get('/excel', 'Front\GymPotentialMemberFrontController@exportExcel');
        });

        // Create potential member - create permission
        Route::group(['defaults' => ['permission' => 'createPotentialMember']], function () {
            Route::name('sw.createPotentialMember')
                ->get('create', 'Front\GymPotentialMemberFrontController@create');
            Route::name('sw.createPotentialMember')
                ->post('create', 'Front\GymPotentialMemberFrontController@store');
        });

        // Edit potential member - edit permission
        Route::group(['defaults' => ['permission' => 'editPotentialMember']], function () {
            Route::name('sw.editPotentialMember')
                ->get('{member}/edit', 'Front\GymPotentialMemberFrontController@edit');
            Route::name('sw.editPotentialMember')
                ->post('{member}/edit', 'Front\GymPotentialMemberFrontController@update');
        });

        // Delete potential member - delete permission
        Route::group(['defaults' => ['permission' => 'deletePotentialMember']], function () {
            Route::name('sw.deletePotentialMember')
                ->get('{member}/delete', 'Front\GymPotentialMemberFrontController@destroy');
        });

        // Update potential member status - edit permission
        Route::group(['defaults' => ['permission' => 'updatePotentialMember']], function () {
            Route::name('sw.updatePotentialMember')
                ->get('/json/updatePotentialMember', 'Front\GymPotentialMemberFrontController@updatePotentialMember');
        });

    });
