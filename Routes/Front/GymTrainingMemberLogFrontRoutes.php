<?php

// Training Member Log Routes
Route::prefix('training/member-log')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // Main member logs list page
        Route::name('sw.listTrainingMemberLog')
            ->get('/', 'Front\GymTrainingMemberLogFrontController@index');
        
        // Show member training management page
        Route::name('sw.showTrainingMemberLog')
            ->get('{member}', 'Front\GymTrainingMemberLogFrontController@show');

        // Add assessment
        Route::name('sw.addTrainingAssessment')
            ->post('{member}/assessment', 'Front\GymTrainingMemberLogFrontController@addAssessment');

        // Add plan
        Route::name('sw.addMemberTrainingPlan')
            ->post('{member}/plan', 'Front\GymTrainingMemberLogFrontController@addPlan');

        // Add medicine
        Route::name('sw.addMemberTrainingMedicine')
            ->post('{member}/medicine', 'Front\GymTrainingMemberLogFrontController@addMedicine');

        // Upload file
        Route::name('sw.addMemberTrainingFile')
            ->post('{member}/file', 'Front\GymTrainingMemberLogFrontController@addFile');

        // Add track (measurement)
        Route::name('sw.addMemberTrainingTrack')
            ->post('{member}/track', 'Front\GymTrainingMemberLogFrontController@addTrack');

        // Add note
        Route::name('sw.addMemberTrainingNote')
            ->post('{member}/note', 'Front\GymTrainingMemberLogFrontController@addNote');

        // Generate AI recommendation
        Route::name('sw.generateMemberAiPlan')
            ->post('{member}/ai', 'Front\GymTrainingMemberLogFrontController@generateAi');
            
        // AI Plan Generation
        Route::name('sw.generateAiPlan')
            ->post('{member}/ai-plan/generate', 'Front\GymTrainingMemberLogFrontController@generateAiPlan');
        Route::name('sw.saveAiPlanTemplate')
            ->post('ai-plan/save-template', 'Front\GymTrainingMemberLogFrontController@saveAiPlanTemplate');
        Route::name('sw.assignAiPlanToMember')
            ->post('{member}/ai-plan/assign', 'Front\GymTrainingMemberLogFrontController@assignAiPlanToMember');
            
        // Download plan PDF
        Route::name('sw.downloadPlanPDF')
            ->get('{member}/plan/{logId}/pdf', 'Front\GymTrainingMemberLogFrontController@downloadPlanPDF');
    });
