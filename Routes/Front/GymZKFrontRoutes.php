<?php


Route::prefix('zk')
//    ->middleware(['cors'])
    ->group(function () {

        // ZK login - public endpoint (no permission required)
        Route::name('sw.zk_login')
            ->any('/login', 'Front\GymZKFrontController@login');

        // ZK endpoint - public endpoint (no permission required)
        Route::name('sw.zk')
            ->any('/', 'Front\GymZKFrontController@zk');

    });
