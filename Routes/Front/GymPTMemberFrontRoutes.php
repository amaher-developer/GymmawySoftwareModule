<?php

Route::prefix('pt/member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listPTMember')
            ->get('/', 'Front\GymPTMemberFrontController@index');
        Route::name('sw.createPTMember')
            ->get('create', 'Front\GymPTMemberFrontController@create');
        Route::name('sw.storePTMember')
            ->post('create', 'Front\GymPTMemberFrontController@store');

        Route::name('sw.editPTMember')
            ->get('{member}/edit', 'Front\GymPTMemberFrontController@edit');
        Route::name('sw.updatePTMember')
            ->match(['put', 'post'], '{member}/edit', 'Front\GymPTMemberFrontController@update');
        Route::name('sw.deletePTMember')
            ->get('{member}/delete', 'Front\GymPTMemberFrontController@destroy');


        Route::name('sw.getPTTrainerAjax')
            ->get('get-pt-trainers-ajax', 'Front\GymPTMemberFrontController@getPTTrainerAjax');
        Route::name('sw.getPTMemberAjax')
            ->get('get-pt-member-ajax', 'Front\GymPTMemberFrontController@getPTMemberAjax');

        Route::name('sw.createPTMemberPayAmountRemainingForm')
            ->get('/pt-pay-amount-remaining', 'Front\GymPTMemberFrontController@payAmountRemaining');

        Route::name('sw.listPTMemberCalendar')
            ->get('{member}/calendar', 'Front\GymPTMemberFrontController@listPTMemberCalendar');
        Route::name('sw.listPTMemberInClassCalendar')
            ->get('/in-class/{pt_class_id}/{pt_trainer_id}/calendar', 'Front\GymPTMemberFrontController@listPTMemberInClassCalendar');



        Route::name('sw.membersPTRefresh')
            ->get('/members-pt-refresh', 'Front\GymPTMemberFrontController@membersPTRefresh');
    });
