<?php

Route::prefix('banner')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listGallery')
            ->get('/gallery', 'Front\GymBannerFrontController@gallery');

        Route::name('sw.listBanner')
            ->get('/', 'Front\GymBannerFrontController@index');
        Route::name('sw.exportBannerPDF')
            ->get('/pdf', 'Front\GymBannerFrontController@exportPDF');
        Route::name('sw.exportBannerExcel')
            ->get('/excel', 'Front\GymBannerFrontController@exportExcel');
        Route::name('sw.createBanner')
            ->get('create', 'Front\GymBannerFrontController@create');
        Route::name('sw.createBanner')
            ->post('create', 'Front\GymBannerFrontController@store');
        Route::name('sw.editBanner')
            ->get('{banner}/edit', 'Front\GymBannerFrontController@edit');
        Route::name('sw.editBanner')
            ->post('{banner}/edit', 'Front\GymBannerFrontController@update');
        Route::name('sw.deleteBanner')
            ->get('{banner}/delete', 'Front\GymBannerFrontController@destroy');


        Route::name('showAllBanner')
            ->get('/json/datatable', 'Front\GymBannerFrontController@showAll');

    });
