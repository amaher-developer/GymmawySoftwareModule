<?php

Route::prefix('reservation-member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listReservationMember')
            ->get('/', 'Front\GymReservationMemberFrontController@index');
        Route::name('sw.exportReservationMemberPDF')
            ->get('/pdf', 'Front\GymReservationMemberFrontController@exportPDF');
        Route::name('sw.exportReservationMemberExcel')
            ->get('/excel', 'Front\GymReservationMemberFrontController@exportExcel');

        Route::name('sw.deleteReservationMember')
            ->get('{member}/delete', 'Front\GymReservationMemberFrontController@destroy');


    });
