<?php

Route::prefix('gym-sw-invoices')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::group(['defaults' => ['permission' => 'listSwInvoices']], function () {
            Route::name('sw.gymSwInvoices.index')
                ->get('/', 'Front\GymSwInvoiceFrontController@index');
        });

        Route::group(['defaults' => ['permission' => 'exportInvoicesReportExcel']], function () {
            Route::name('sw.gymSwInvoices.exportExcel')
                ->get('/excel', 'Front\GymSwInvoiceFrontController@exportExcel');
        });

        Route::group(['defaults' => ['permission' => 'exportInvoicesReportPDF']], function () {
            Route::name('sw.gymSwInvoices.exportReportPDF')
                ->get('/report-pdf', 'Front\GymSwInvoiceFrontController@exportReportPDF');
        });

        Route::name('sw.gymSwInvoices.show')
            ->get('{id}', 'Front\GymSwInvoiceFrontController@show');

        Route::name('sw.gymSwInvoices.cancel')
            ->post('{id}/cancel', 'Front\GymSwInvoiceFrontController@cancel');

        Route::name('sw.gymSwInvoices.pdf')
            ->get('{id}/pdf', 'Front\GymSwInvoiceFrontController@pdf');

        Route::group(['defaults' => ['permission' => 'submitZatcaInvoice']], function () {
            Route::name('sw.gymSwInvoices.submitZatca')
                ->post('{id}/zatca', 'Front\GymSwInvoiceFrontController@submitZatca');

            Route::name('sw.gymSwInvoices.bulkSubmitZatca')
                ->post('bulk-zatca', 'Front\GymSwInvoiceFrontController@bulkSubmitZatca');
        });
    });
