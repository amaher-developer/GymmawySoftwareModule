<?php

Route::prefix('event-notification')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.editEventNotification')
            ->get('edit', 'Front\GymEventNotificationFrontController@edit');
        Route::name('sw.editEventNotificationAjax')
            ->get('edit/{id}/{status}', 'Front\GymEventNotificationFrontController@updateAjax');





    });
