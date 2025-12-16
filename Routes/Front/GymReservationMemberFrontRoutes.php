<?php

Route::prefix('reservation-member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List reservation members - view permission
        Route::name('sw.listReservationMember')
            ->get('/', 'Front\GymReservationMemberFrontController@index');

        // Export reservation members - view permission
        Route::name('sw.exportReservationMemberPDF')
            ->get('/pdf', 'Front\GymReservationMemberFrontController@exportPDF');
        Route::name('sw.exportReservationMemberExcel')
            ->get('/excel', 'Front\GymReservationMemberFrontController@exportExcel');

        // Delete reservation member - delete permission
        Route::name('sw.deleteReservationMember')
            ->get('{member}/delete', 'Front\GymReservationMemberFrontController@destroy');

    });
