<?php

Route::prefix('non-member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List non-members - view permission
        Route::group(['defaults' => ['permission' => 'listNonMember']], function () {
            Route::name('sw.listNonMember')
                ->get('/', 'Front\GymNonMemberFrontController@index');
            Route::name('sw.showAllNonMember')
                ->get('/json/datatable', 'Front\GymNonMemberFrontController@showAll');
        });

        // Export non-members - view permission
        Route::group(['defaults' => ['permission' => 'listNonMember']], function () {
            Route::name('sw.exportNonMemberPDF')
                ->get('/pdf', 'Front\GymNonMemberFrontController@exportPDF');
            Route::name('sw.exportNonMemberExcel')
                ->get('/excel', 'Front\GymNonMemberFrontController@exportExcel');
        });

        // Create non-member - create permission
        Route::group(['defaults' => ['permission' => 'createNonMember']], function () {
            Route::name('sw.createNonMember')
                ->get('create', 'Front\GymNonMemberFrontController@create');
            Route::name('sw.createNonMember')
                ->post('create', 'Front\GymNonMemberFrontController@store');
        });

        // Edit non-member - edit permission
        Route::group(['defaults' => ['permission' => 'editNonMember']], function () {
            Route::name('sw.editNonMember')
                ->get('{member}/edit', 'Front\GymNonMemberFrontController@edit');
            Route::name('sw.editNonMember')
                ->post('{member}/edit', 'Front\GymNonMemberFrontController@update');
        });

        // Delete non-member - delete permission
        Route::group(['defaults' => ['permission' => 'deleteNonMember']], function () {
            Route::name('sw.deleteNonMember')
                ->get('{member}/delete', 'Front\GymNonMemberFrontController@destroy');
        });

        // Non-member reports - view permission
        Route::group(['defaults' => ['permission' => 'listNonMemberReport']], function () {
            Route::name('sw.listNonMemberReport')
                ->get('/reports', 'Front\GymNonMemberFrontController@reports');
        });

        // Non-member calendar views - view permission
        Route::group(['defaults' => ['permission' => 'listNonMemberInTimeCalendar']], function () {
            Route::name('sw.listNonMemberInTimeCalendar')
                ->get('/in-time/{id}/{date}/calendar', 'Front\GymNonMemberFrontController@listNonMemberInTimeCalendar');
        });

        // Create non-member attendance in calendar - create permission
        Route::group(['defaults' => ['permission' => 'createNonMemberAttendInTimeCalendar']], function () {
            Route::name('sw.createNonMemberAttendInTimeCalendar')
                ->get('/in-attend/calendar', 'Front\GymNonMemberFrontController@createNonMemberAttendInTimeCalendar');
        });

        // Non-member reservations - manage reservations
        Route::group(['defaults' => ['permission' => 'getNonMemberReservation']], function () {
            Route::name('sw.getNonMemberReservation')
                ->get('/get-nonmember-reservation', 'Front\GymNonMemberFrontController@getNonMemberReservation');
        });

        // Create non-member reservation - create permission
        Route::group(['defaults' => ['permission' => 'createReservationNonMemberAjax']], function () {
            Route::name('sw.createReservationNonMemberAjax')
                ->get('create-reservation-non-member-ajax', 'Front\GymNonMemberFrontController@createReservationNonMemberAjax');
        });

        // Delete non-member reservation - delete permission
        Route::group(['defaults' => ['permission' => 'deleteReservationNonMemberAjax']], function () {
            Route::name('sw.deleteReservationNonMemberAjax')
                ->get('delete-reservation-non-member-ajax', 'Front\GymNonMemberFrontController@deleteReservationNonMemberAjax');
        });

    });
