<?php

Route::prefix('user')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listUser')
            ->get('/', 'Front\GymUserFrontController@index');


        Route::name('sw.exportUserPDF')
            ->get('/pdf', 'Front\GymUserFrontController@exportPDF');
        Route::name('sw.exportUserExcel')
            ->get('/excel', 'Front\GymUserFrontController@exportExcel');

        Route::name('sw.listUserJson')
            ->get('/json', 'Front\GymUserFrontController@indexJson');
        Route::name('sw.createUser')
            ->get('create', 'Front\GymUserFrontController@create');
        Route::name('sw.createUser')
            ->post('create', 'Front\GymUserFrontController@store');
        Route::name('sw.editUser')
            ->get('{user}/edit', 'Front\GymUserFrontController@edit');

        Route::name('sw.editUserProfile')
            ->get('/profile', 'Front\GymUserFrontController@editProfile');
        Route::name('sw.editUserProfile')
            ->post('/profile', 'Front\GymUserFrontController@updateProfile');

        Route::name('sw.editUser')
            ->post('{user}/edit', 'Front\GymUserFrontController@update');
        Route::name('sw.deleteUser')
            ->get('{user}/delete', 'Front\GymUserFrontController@destroy');



        Route::name('sw.userAttendees')
            ->get('/attendees', 'Front\GymUserFrontController@userAttendees');
        Route::name('sw.userAttendeesStore')
            ->get('/attendees-store', 'Front\GymUserFrontController@userAttendeesStore');
    });
