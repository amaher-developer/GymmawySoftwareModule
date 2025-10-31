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
        Route::name('sw.deleteReservation')
            ->get('{reservation}/delete', 'Front\GymReservationFrontController@destroy');


        Route::name('sw.getReservationMemberAjax')
            ->get('get-reservation-member-ajax', 'Front\GymReservationFrontController@getReservationMemberAjax');
        Route::name('sw.createReservationMemberAjax')
            ->get('create-reservation-member-ajax', 'Front\GymReservationFrontController@createReservationMemberAjax');
        Route::name('sw.deleteReservationMemberAjax')
            ->get('delete-reservation-member-ajax', 'Front\GymReservationFrontController@deleteReservationMemberAjax');

    });
