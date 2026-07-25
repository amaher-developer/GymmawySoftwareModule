<?php

Route::prefix('sales-report')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // Sales Report - view permission
        Route::name('sw.salesReport')
            ->get('/', 'Front\GymSalesReportFrontController@index');

        // Export Sales Report PDF - export permission
        Route::group(['defaults' => ['permission' => 'exportSalesReportPDF']], function () {
            Route::name('sw.exportSalesReportPDF')
                ->get('/pdf', 'Front\GymSalesReportFrontController@exportPDF');
        });

        // Export Sales Report Excel - export permission
        Route::group(['defaults' => ['permission' => 'exportSalesReportExcel']], function () {
            Route::name('sw.exportSalesReportExcel')
                ->get('/excel', 'Front\GymSalesReportFrontController@exportExcel');
        });

    });
