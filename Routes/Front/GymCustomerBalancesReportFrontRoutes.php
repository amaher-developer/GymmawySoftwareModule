<?php

Route::prefix('customer-balances')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // Customer Balances Report - view permission
        Route::name('sw.customerBalancesReport')
            ->get('/', 'Front\GymCustomerBalancesReportFrontController@index');

        // Export Customer Balances PDF - export permission
        Route::group(['defaults' => ['permission' => 'exportCustomerBalancesPDF']], function () {
            Route::name('sw.exportCustomerBalancesPDF')
                ->get('/pdf', 'Front\GymCustomerBalancesReportFrontController@exportPDF');
        });

        // Export Customer Balances Excel - export permission
        Route::group(['defaults' => ['permission' => 'exportCustomerBalancesExcel']], function () {
            Route::name('sw.exportCustomerBalancesExcel')
                ->get('/excel', 'Front\GymCustomerBalancesReportFrontController@exportExcel');
        });

    });
