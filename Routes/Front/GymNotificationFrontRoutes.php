<?php


Route::prefix('notification')
    ->middleware(['auth:sw'])
    ->group(function () {
        Route::name('sw.sendToUsers')
            ->get('/send-to-users', 'Front\GymNotificationFrontController@sendToUsers');
        Route::name('sw.markAsRead')
            ->get('/mark-as-read', 'Front\GymNotificationFrontController@markAsRead');

    });
