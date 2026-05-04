<?php

Route::prefix('gym-sw-invoices')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.gymSwInvoices.index')
            ->get('/', 'Front\GymSwInvoiceFrontController@index');

        Route::name('sw.gymSwInvoices.show')
            ->get('{id}', 'Front\GymSwInvoiceFrontController@show');

        Route::name('sw.gymSwInvoices.cancel')
            ->post('{id}/cancel', 'Front\GymSwInvoiceFrontController@cancel');

        Route::name('sw.gymSwInvoices.pdf')
            ->get('{id}/pdf', 'Front\GymSwInvoiceFrontController@pdf');

        Route::name('sw.gymSwInvoices.submitZatca')
            ->post('{id}/zatca', 'Front\GymSwInvoiceFrontController@submitZatca');

        Route::name('sw.gymSwInvoices.bulkSubmitZatca')
            ->post('bulk-zatca', 'Front\GymSwInvoiceFrontController@bulkSubmitZatca');
    });
