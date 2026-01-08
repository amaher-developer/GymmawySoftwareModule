<?php

Route::prefix('pt/member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List PT members - view permission
        Route::name('sw.listPTMember')
            ->get('/', 'Front\GymPTMemberFrontController@index');

        // Create PT member - create permission
        Route::group(['defaults' => ['permission' => 'createPTMember']], function () {
            Route::name('sw.createPTMember')
                ->get('create', 'Front\GymPTMemberFrontController@create');
            Route::name('sw.storePTMember')
                ->post('create', 'Front\GymPTMemberFrontController@store');
        });

        // Edit PT member - edit permission
        Route::group(['defaults' => ['permission' => 'editPTMember']], function () {
            Route::name('sw.editPTMember')
                ->get('{member}/edit', 'Front\GymPTMemberFrontController@edit');
            Route::name('sw.updatePTMember')
                ->match(['put', 'post'], '{member}/edit', 'Front\GymPTMemberFrontController@update');
        });

        // Delete PT member - delete permission
        Route::name('sw.deletePTMember')
            ->get('{member}/delete', 'Front\GymPTMemberFrontController@destroy');

        // PT member calendar - view permission
        Route::name('sw.listPTMemberCalendar')
            ->get('{member}/calendar', 'Front\GymPTMemberFrontController@listPTMemberCalendar');
        Route::name('sw.listPTMemberInClassCalendar')
            ->get('/in-class/{pt_class_id}/{pt_trainer_id}/calendar', 'Front\GymPTMemberFrontController@listPTMemberInClassCalendar');

        // PT member helper endpoints - view permission
        Route::name('sw.getPTTrainerAjax')
            ->get('get-pt-trainers-ajax', 'Front\GymPTMemberFrontController@getPTTrainerAjax');
        Route::name('sw.getPTMemberAjax')
            ->get('get-pt-member-ajax', 'Front\GymPTMemberFrontController@getPTMemberAjax');

        // Pay amount remaining - create permission
        Route::group(['defaults' => ['permission' => 'createPTMemberPayAmountRemainingForm']], function () {
            Route::name('sw.createPTMemberPayAmountRemainingForm')
                ->get('/pt-pay-amount-remaining', 'Front\GymPTMemberFrontController@payAmountRemaining');
        });

        // Refresh PT members - helper endpoint
        Route::name('sw.membersPTRefresh')
            ->get('/members-pt-refresh', 'Front\GymPTMemberFrontController@membersPTRefresh');

    });
