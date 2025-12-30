<?php

Route::prefix('user')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List users - view permission
        Route::name('sw.listUser')
            ->get('/', 'Front\GymUserFrontController@index');
        Route::name('sw.listUserJson')
            ->get('/json', 'Front\GymUserFrontController@indexJson');

        // Export users - view permission
        Route::name('sw.exportUserPDF')
            ->get('/pdf', 'Front\GymUserFrontController@exportPDF');
        Route::name('sw.exportUserExcel')
            ->get('/excel', 'Front\GymUserFrontController@exportExcel');

        // Create user - create permission
        Route::group(['defaults' => ['permission' => 'createUser']], function () {
            Route::name('sw.createUser')
                ->get('create', 'Front\GymUserFrontController@create');
            Route::name('sw.createUser')
                ->post('create', 'Front\GymUserFrontController@store');
        });

        // Edit user - edit permission
        Route::group(['defaults' => ['permission' => 'editUser']], function () {
            Route::name('sw.editUser')
                ->get('{user}/edit', 'Front\GymUserFrontController@edit');
            Route::name('sw.editUser')
                ->post('{user}/edit', 'Front\GymUserFrontController@update');
        });

        // Edit user profile - edit permission (already in default_permissions in middleware)
        Route::group(['defaults' => ['permission' => 'editUserProfile']], function () {
            Route::name('sw.editUserProfile')
                ->get('/profile', 'Front\GymUserFrontController@editProfile');
            Route::name('sw.editUserProfile')
                ->post('/profile', 'Front\GymUserFrontController@updateProfile');
        });

        // Delete user - delete permission
        Route::name('sw.deleteUser')
            ->get('{user}/delete', 'Front\GymUserFrontController@destroy');

        // User attendees - view permission (already in default_permissions in middleware)
        Route::name('sw.userAttendees')
            ->get('/attendees', 'Front\GymUserFrontController@userAttendees');
        Route::name('sw.userAttendeesStore')
            ->get('/attendees-store', 'Front\GymUserFrontController@userAttendeesStore');
        Route::name('sw.attendanceGeofenceCheck')
            ->post('/attendance-geofence-check', 'Front\GymUserFrontController@attendanceGeofenceCheck');
    });
