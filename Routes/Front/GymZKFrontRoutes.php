<?php


Route::prefix('zk')
//    ->middleware(['cors'])
    ->group(function () {
        Route::name('sw.zk_login')
            ->any('/login', 'Front\GymZKFrontController@login');
        Route::name('sw.zk')
            ->any('/', 'Front\GymZKFrontController@zk');

    });
