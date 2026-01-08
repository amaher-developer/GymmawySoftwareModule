<?php


Route::prefix('notification')
    ->middleware(['auth:sw'])
    ->group(function () {

        // Send notifications to users - no specific permission (authenticated users only)
        Route::name('sw.sendToUsers')
            ->get('/send-to-users', 'Front\GymNotificationFrontController@sendToUsers');

        // Mark notification as read - no specific permission (authenticated users only)
        Route::name('sw.markAsRead')
            ->get('/mark-as-read', 'Front\GymNotificationFrontController@markAsRead');

    });
