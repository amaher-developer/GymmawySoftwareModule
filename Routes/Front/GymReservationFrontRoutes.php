<?php

Route::prefix('reservation')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listReservation')
            ->get('/', 'Front\GymReservationFrontController@index');

        Route::name('sw.createReservation')
            ->get('create', 'Front\GymReservationFrontController@create');
        Route::name('sw.createReservation')
            ->post('create', 'Front\GymReservationFrontController@store');

        Route::name('sw.editReservation')
            ->get('{reservation}/edit', 'Front\GymReservationFrontController@edit');
        Route::name('sw.editReservation')
            ->post('{reservation}/edit', 'Front\GymReservationFrontController@update');

        Route::name('sw.deleteReservation')
            ->get('{reservation}/delete', 'Front\GymReservationFrontController@destroy');

        // Quick status actions (must be before other routes to avoid conflicts)
        Route::name('sw.reservation.confirm')->post('{id}/confirm', 'Front\GymReservationFrontController@confirm');
        Route::name('sw.reservation.cancel')->post('{id}/cancel', 'Front\GymReservationFrontController@cancel');
        Route::name('sw.reservation.attend')->post('{id}/attend', 'Front\GymReservationFrontController@attend');
        Route::name('sw.reservation.missed')->post('{id}/missed', 'Front\GymReservationFrontController@missed');

        Route::name('sw.attendReservation')
            ->get('{reservation}/attend', 'Front\GymReservationAttendanceFrontController@attendForm');
        Route::name('sw.attendReservation')
            ->post('{reservation}/attend', 'Front\GymReservationAttendanceFrontController@attend');

        // Calendar events feed
        Route::name('sw.reservation.events')->get('events', 'Front\GymReservationFrontController@events');

        // Slots API
        Route::name('sw.reservation.slots')->post('slots', 'Front\GymReservationFrontController@availableSlots');

        // AJAX overlap checker (used by form)
        Route::name('sw.reservation.check')->post('check-slot', 'Front\GymReservationFrontController@checkOverlap');

        // Quick booking endpoint (create reservation via AJAX)
        Route::name('sw.reservation.ajaxCreate')->post('ajax-create', 'Front\GymReservationFrontController@ajaxCreate');

        // Quick booking endpoint (update reservation via AJAX)
        Route::name('sw.reservation.ajaxUpdate')->post('ajax-update/{id}', 'Front\GymReservationFrontController@ajaxUpdate');

        // Get reservation data for editing
        Route::name('sw.reservation.ajaxGet')->get('ajax-get/{id}', 'Front\GymReservationFrontController@ajaxGet');

        // AJAX endpoints for loading members/non-members
        Route::name('sw.reservation.loadMembers')->get('load-members', 'Front\GymReservationFrontController@loadMembers');
        Route::name('sw.reservation.loadNonMembers')->get('load-non-members', 'Front\GymReservationFrontController@loadNonMembers');
    });
