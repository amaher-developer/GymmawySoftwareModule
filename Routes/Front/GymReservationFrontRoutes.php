<?php

Route::prefix('reservation')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List reservations - view permission
        Route::group(['defaults' => ['permission' => 'listReservation']], function () {
            Route::name('sw.listReservation')
                ->get('/', 'Front\GymReservationFrontController@index');
            Route::name('sw.reservation.events')
                ->get('events', 'Front\GymReservationFrontController@events');
        });

        // Create reservation - create permission
        Route::group(['defaults' => ['permission' => 'createReservation']], function () {
            Route::name('sw.createReservation')
                ->get('create', 'Front\GymReservationFrontController@create');
            Route::name('sw.createReservation')
                ->post('create', 'Front\GymReservationFrontController@store');
            Route::name('sw.reservation.ajaxCreate')
                ->post('ajax-create', 'Front\GymReservationFrontController@ajaxCreate');
            Route::name('sw.reservation.slots')
                ->post('slots', 'Front\GymReservationFrontController@availableSlots');
            Route::name('sw.reservation.check')
                ->post('check-slot', 'Front\GymReservationFrontController@checkOverlap');
        });

        // Edit reservation - edit permission
        Route::group(['defaults' => ['permission' => 'editReservation']], function () {
            Route::name('sw.editReservation')
                ->get('{reservation}/edit', 'Front\GymReservationFrontController@edit');
            Route::name('sw.editReservation')
                ->post('{reservation}/edit', 'Front\GymReservationFrontController@update');
            Route::name('sw.reservation.ajaxUpdate')
                ->post('ajax-update/{id}', 'Front\GymReservationFrontController@ajaxUpdate');
            Route::name('sw.reservation.ajaxGet')
                ->get('ajax-get/{id}', 'Front\GymReservationFrontController@ajaxGet');
        });

        // Delete reservation - delete permission
        Route::group(['defaults' => ['permission' => 'deleteReservation']], function () {
            Route::name('sw.deleteReservation')
                ->get('{reservation}/delete', 'Front\GymReservationFrontController@destroy');
        });

        // Reservation status actions - manage permission
        Route::group(['defaults' => ['permission' => 'editReservation']], function () {
            Route::name('sw.reservation.confirm')
                ->post('{id}/confirm', 'Front\GymReservationFrontController@confirm');
            Route::name('sw.reservation.cancel')
                ->post('{id}/cancel', 'Front\GymReservationFrontController@cancel');
            Route::name('sw.reservation.attend')
                ->post('{id}/attend', 'Front\GymReservationFrontController@attend');
            Route::name('sw.reservation.missed')
                ->post('{id}/missed', 'Front\GymReservationFrontController@missed');
        });

        // Attend reservation - attendance permission
        Route::group(['defaults' => ['permission' => 'attendReservation']], function () {
            Route::name('sw.attendReservation')
                ->get('{reservation}/attend', 'Front\GymReservationAttendanceFrontController@attendForm');
            Route::name('sw.attendReservation')
                ->post('{reservation}/attend', 'Front\GymReservationAttendanceFrontController@attend');
        });

        // Load members and non-members - helper endpoints
        Route::group(['defaults' => ['permission' => 'listReservation']], function () {
            Route::name('sw.reservation.loadMembers')
                ->get('load-members', 'Front\GymReservationFrontController@loadMembers');
            Route::name('sw.reservation.loadNonMembers')
                ->get('load-non-members', 'Front\GymReservationFrontController@loadNonMembers');
        });

    });
