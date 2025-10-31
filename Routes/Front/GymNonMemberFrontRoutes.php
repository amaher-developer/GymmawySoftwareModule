<?php

Route::prefix('non-member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listNonMember')
            ->get('/', 'Front\GymNonMemberFrontController@index');
        Route::name('sw.exportNonMemberPDF')
            ->get('/pdf', 'Front\GymNonMemberFrontController@exportPDF');
        Route::name('sw.exportNonMemberExcel')
            ->get('/excel', 'Front\GymNonMemberFrontController@exportExcel');
        Route::name('sw.createNonMember')
            ->get('create', 'Front\GymNonMemberFrontController@create');
        Route::name('sw.createNonMember')
            ->post('create', 'Front\GymNonMemberFrontController@store');
        Route::name('sw.editNonMember')
            ->get('{member}/edit', 'Front\GymNonMemberFrontController@edit');
        Route::name('sw.editNonMember')
            ->post('{member}/edit', 'Front\GymNonMemberFrontController@update');
        Route::name('sw.deleteNonMember')
            ->get('{member}/delete', 'Front\GymNonMemberFrontController@destroy');


        Route::name('sw.listNonMemberReport')
            ->get('/reports', 'Front\GymNonMemberFrontController@reports');

        Route::name('sw.listNonMemberInTimeCalendar')
            ->get('/in-time/{id}/{date}/calendar', 'Front\GymNonMemberFrontController@listNonMemberInTimeCalendar');

        Route::name('sw.createNonMemberAttendInTimeCalendar')
            ->get('/in-attend/calendar', 'Front\GymNonMemberFrontController@createNonMemberAttendInTimeCalendar');


        Route::name('sw.showAllNonMember')
            ->get('/json/datatable', 'Front\GymNonMemberFrontController@showAll');

        Route::name('sw.getNonMemberReservation')
            ->get('/get-nonmember-reservation', 'Front\GymNonMemberFrontController@getNonMemberReservation');

        Route::name('sw.createReservationNonMemberAjax')
            ->get('create-reservation-non-member-ajax', 'Front\GymNonMemberFrontController@createReservationNonMemberAjax');

        Route::name('sw.deleteReservationNonMemberAjax')
            ->get('delete-reservation-non-member-ajax', 'Front\GymNonMemberFrontController@deleteReservationNonMemberAjax');


    });
