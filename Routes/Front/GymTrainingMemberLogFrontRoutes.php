<?php

// Training Member Log Routes
Route::prefix('training/member-log')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List training member logs - view permission
        Route::group(['defaults' => ['permission' => 'listTrainingMemberLog']], function () {
            Route::name('sw.listTrainingMemberLog')
                ->get('/', 'Front\GymTrainingMemberLogFrontController@index');
        });

        // Show training member log - view permission
        Route::group(['defaults' => ['permission' => 'showTrainingMemberLog']], function () {
            Route::name('sw.showTrainingMemberLog')
                ->get('{member}', 'Front\GymTrainingMemberLogFrontController@show');
            Route::name('sw.downloadPlanPDF')
                ->get('{member}/plan/{logId}/pdf', 'Front\GymTrainingMemberLogFrontController@downloadPlanPDF');
        });

        // Add training assessment - create permission
        Route::group(['defaults' => ['permission' => 'addTrainingAssessment']], function () {
            Route::name('sw.addTrainingAssessment')
                ->post('{member}/assessment', 'Front\GymTrainingMemberLogFrontController@addAssessment');
        });

        // Add member training plan - create permission
        Route::group(['defaults' => ['permission' => 'addMemberTrainingPlan']], function () {
            Route::name('sw.addMemberTrainingPlan')
                ->post('{member}/plan', 'Front\GymTrainingMemberLogFrontController@addPlan');
        });

        // Add member training medicine - create permission
        Route::group(['defaults' => ['permission' => 'addMemberTrainingMedicine']], function () {
            Route::name('sw.addMemberTrainingMedicine')
                ->post('{member}/medicine', 'Front\GymTrainingMemberLogFrontController@addMedicine');
        });

        // Add member training file - create permission
        Route::group(['defaults' => ['permission' => 'addMemberTrainingFile']], function () {
            Route::name('sw.addMemberTrainingFile')
                ->post('{member}/file', 'Front\GymTrainingMemberLogFrontController@addFile');
        });

        // Add member training track - create permission
        Route::group(['defaults' => ['permission' => 'addMemberTrainingTrack']], function () {
            Route::name('sw.addMemberTrainingTrack')
                ->post('{member}/track', 'Front\GymTrainingMemberLogFrontController@addTrack');
        });

        // Add member training note - create permission
        Route::group(['defaults' => ['permission' => 'addMemberTrainingNote']], function () {
            Route::name('sw.addMemberTrainingNote')
                ->post('{member}/note', 'Front\GymTrainingMemberLogFrontController@addNote');
        });

        // Generate AI recommendations - create permission
        Route::group(['defaults' => ['permission' => 'generateMemberAiPlan']], function () {
            Route::name('sw.generateMemberAiPlan')
                ->post('{member}/ai', 'Front\GymTrainingMemberLogFrontController@generateAi');
            Route::name('sw.generateAiPlan')
                ->post('{member}/ai-plan/generate', 'Front\GymTrainingMemberLogFrontController@generateAiPlan');
            Route::name('sw.saveAiPlanTemplate')
                ->post('ai-plan/save-template', 'Front\GymTrainingMemberLogFrontController@saveAiPlanTemplate');
            Route::name('sw.assignAiPlanToMember')
                ->post('{member}/ai-plan/assign', 'Front\GymTrainingMemberLogFrontController@assignAiPlanToMember');
        });

    });
