<?php


Route::prefix('telegram')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.createTelegram')
            ->get('create', 'Front\GymTelegramFrontController@create');
        Route::name('sw.storeTelegram')
            ->post('create', 'Front\GymTelegramFrontController@store');
        Route::name('sw.phonesByAjax')
            ->get('phones-by-ajax', 'Front\GymTelegramFrontController@phonesByAjax');

        Route::name('sw.listTelegramLog')
            ->get('/logs', 'Front\GymTelegramFrontController@index');



        Route::name('sw.telegramUpdatedActivity')
            ->get('/updated-activity', 'Front\GymTelegramFrontController@updatedActivity');
        Route::name('sw.telegramSendMessage')
            ->get('/', 'Front\GymTelegramFrontController@sendMessage');
        Route::name('sw.telegramStoreMessage')
            ->post('/send-message', 'Front\GymTelegramFrontController@storeMessage');
        Route::name('sw.telegramStorePhoto')
            ->post('/store-photo', 'Front\GymTelegramFrontController@storePhoto');

});

