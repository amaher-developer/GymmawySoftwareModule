<?php

Route::prefix('event-notification')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // Edit event notification - edit permission
        Route::group(['defaults' => ['permission' => 'editEventNotification']], function () {
            Route::name('sw.editEventNotification')
                ->get('edit', 'Front\GymEventNotificationFrontController@edit');
            Route::name('sw.editEventNotificationAjax')
                ->get('edit/{id}/{status}', 'Front\GymEventNotificationFrontController@updateAjax');
            Route::name('sw.updateEventNotificationMessage')
                ->post('update-message', 'Front\GymEventNotificationFrontController@updateMessageAjax');
        });

    });
