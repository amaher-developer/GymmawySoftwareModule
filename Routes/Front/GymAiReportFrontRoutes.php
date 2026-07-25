<?php

Route::prefix('ai-reports')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List all AI reports
        Route::name('sw.aiReports.index')
            ->get('/', 'Front\GymAiReportFrontController@index');

        // Generate a new AI report (POST with from/to dates)
        Route::name('sw.aiReports.generate')
            ->post('/generate', 'Front\GymAiReportFrontController@generate');

        // View a saved AI report
        Route::name('sw.aiReports.show')
            ->get('/{id}', 'Front\GymAiReportFrontController@show');

        // Send a saved AI report via email/SMS
        Route::name('sw.aiReports.send')
            ->post('/{id}/send', 'Front\GymAiReportFrontController@send');

    });
