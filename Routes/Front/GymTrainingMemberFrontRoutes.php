<?php

Route::name('sw.listTrainingMemberPublicPlans')
    ->get('training/member-plans/{code}', 'Front\GymTrainingMemberFrontController@memberPlans');

Route::prefix('training/member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listTrainingMember')
            ->get('/', 'Front\GymTrainingMemberFrontController@index');

        Route::name('sw.createTrainingTrainMember')
            ->get('workout/create', 'Front\GymTrainingMemberFrontController@createTrain');
        Route::name('sw.createTrainingTrainMember')
            ->post('workout/create', 'Front\GymTrainingMemberFrontController@storeTrain');

        Route::name('sw.createTrainingDietMember')
            ->get('diet/create', 'Front\GymTrainingMemberFrontController@createDiet');
        Route::name('sw.createTrainingDietMember')
            ->post('diet/create', 'Front\GymTrainingMemberFrontController@storeDiet');

        Route::name('sw.editTrainingMember')
            ->get('{member}/edit', 'Front\GymTrainingMemberFrontController@edit');
        Route::name('sw.editTrainingMember')
            ->post('{member}/edit', 'Front\GymTrainingMemberFrontController@update');
        Route::name('sw.deleteTrainingMember')
            ->get('{member}/delete', 'Front\GymTrainingMemberFrontController@destroy');




        Route::name('sw.exportTrainingMemberPDF')
            ->get('/pdf', 'Front\GymTrainingMemberFrontController@exportPDF');
        Route::name('sw.exportTrainingMemberExcel')
            ->get('/excel', 'Front\GymTrainingMemberFrontController@exportExcel');

//        Route::name('sw.getPTTrainerAjax')
//            ->get('get-pt-trainers-ajax', 'Front\GymPTMemberFrontController@getPTTrainerAjax');
//        Route::name('sw.getPTMemberAjax')
//            ->get('get-pt-member-ajax', 'Front\GymPTMemberFrontController@getPTMemberAjax');

    });
