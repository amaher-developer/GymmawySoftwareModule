<?php

Route::prefix('banner')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List banners and gallery - view permission
        Route::group(['defaults' => ['permission' => 'listBanner']], function () {
            Route::name('sw.listGallery')
                ->get('/gallery', 'Front\GymBannerFrontController@gallery');
            Route::name('sw.listBanner')
                ->get('/', 'Front\GymBannerFrontController@index');
            Route::name('showAllBanner')
                ->get('/json/datatable', 'Front\GymBannerFrontController@showAll');
        });

        // Export banners - view permission
        Route::group(['defaults' => ['permission' => 'listBanner']], function () {
            Route::name('sw.exportBannerPDF')
                ->get('/pdf', 'Front\GymBannerFrontController@exportPDF');
            Route::name('sw.exportBannerExcel')
                ->get('/excel', 'Front\GymBannerFrontController@exportExcel');
        });

        // Create banner - create permission
        Route::group(['defaults' => ['permission' => 'createBanner']], function () {
            Route::name('sw.createBanner')
                ->get('create', 'Front\GymBannerFrontController@create');
            Route::name('sw.createBanner')
                ->post('create', 'Front\GymBannerFrontController@store');
        });

        // Edit banner - edit permission
        Route::group(['defaults' => ['permission' => 'editBanner']], function () {
            Route::name('sw.editBanner')
                ->get('{banner}/edit', 'Front\GymBannerFrontController@edit');
            Route::name('sw.editBanner')
                ->post('{banner}/edit', 'Front\GymBannerFrontController@update');
        });

        // Delete banner - delete permission
        Route::group(['defaults' => ['permission' => 'deleteBanner']], function () {
            Route::name('sw.deleteBanner')
                ->get('{banner}/delete', 'Front\GymBannerFrontController@destroy');
        });

    });
