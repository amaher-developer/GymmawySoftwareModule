<?php

Route::prefix('setting')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
    Route::name('sw.editSetting')
        ->get('', 'Front\GymSettingFrontController@edit');

    Route::name('sw.editSetting')
        ->post('', 'Front\GymSettingFrontController@update');

    Route::name('sw.editSettingUploadImage')
        ->post('/ajax_upload/upload', 'Front\GymSettingFrontController@updateImage');

    Route::name('sw.editSettingDeleteUploadImage')
        ->post('/ajax_upload/delete', 'Front\GymSettingFrontController@updateImageDelete');




        // payment types
        Route::name('sw.listPaymentType')
            ->get('/payment-type', 'Front\GymSettingFrontController@indexPaymentType');
        Route::name('sw.createPaymentType')
            ->get('payment-type/create', 'Front\GymSettingFrontController@createPaymentType');
        Route::name('sw.createPaymentType')
            ->post('payment-type/create', 'Front\GymSettingFrontController@storePaymentType');
        Route::name('sw.editPaymentType')
            ->get('payment-type/{payment_type}/edit', 'Front\GymSettingFrontController@editPaymentType');
        Route::name('sw.editPaymentType')
            ->post('payment-type/{payment_type}/edit', 'Front\GymSettingFrontController@updatePaymentType');
        Route::name('sw.deletePaymentType')
            ->get('payment-type/{payment_type}/delete', 'Front\GymSettingFrontController@destroyPaymentType');



        // group discounts
        Route::name('sw.listGroupDiscount')
            ->get('/group-discount', 'Front\GymSettingFrontController@indexGroupDiscount');
        Route::name('sw.createGroupDiscount')
            ->get('group-discount/create', 'Front\GymSettingFrontController@createGroupDiscount');
        Route::name('sw.createGroupDiscount')
            ->post('group-discount/create', 'Front\GymSettingFrontController@storeGroupDiscount');
        Route::name('sw.editGroupDiscount')
            ->get('group-discount/{group_discount}/edit', 'Front\GymSettingFrontController@editGroupDiscount');
        Route::name('sw.editGroupDiscount')
            ->post('group-discount/{group_discount}/edit', 'Front\GymSettingFrontController@updateGroupDiscount');
        Route::name('sw.deleteGroupDiscount')
            ->get('group-discount/{group_discount}/delete', 'Front\GymSettingFrontController@destroyGroupDiscount');


        // sale channels
        Route::name('sw.listSaleChannel')
            ->get('/sale-channel', 'Front\GymSettingFrontController@indexSaleChannel');
        Route::name('sw.createSaleChannel')
            ->get('sale-channel/create', 'Front\GymSettingFrontController@createSaleChannel');
        Route::name('sw.createSaleChannel')
            ->post('sale-channel/create', 'Front\GymSettingFrontController@storeSaleChannel');
        Route::name('sw.editSaleChannel')
            ->get('sale-channel/{sale_channel}/edit', 'Front\GymSettingFrontController@editSaleChannel');
        Route::name('sw.editSaleChannel')
            ->post('sale-channel/{sale_channel}/edit', 'Front\GymSettingFrontController@updateSaleChannel');
        Route::name('sw.deleteSaleChannel')
            ->get('sale-channel/{sale_channel}/delete', 'Front\GymSettingFrontController@destroySaleChannel');


        // store groups
        Route::name('sw.listStoreGroup')
            ->get('/store-group', 'Front\GymSettingFrontController@indexStoreGroup');
        Route::name('sw.createStoreGroup')
            ->get('store-group/create', 'Front\GymSettingFrontController@createStoreGroup');
        Route::name('sw.createStoreGroup')
            ->post('store-group/create', 'Front\GymSettingFrontController@storeStoreGroup');
        Route::name('sw.editStoreGroup')
            ->get('store-group/{store_group}/edit', 'Front\GymSettingFrontController@editStoreGroup');
        Route::name('sw.editStoreGroup')
            ->post('store-group/{store_group}/edit', 'Front\GymSettingFrontController@updateStoreGroup');
        Route::name('sw.deleteStoreGroup')
            ->get('store-group/{store_group}/delete', 'Front\GymSettingFrontController@destroyStoreGroup');

        // money box types
        Route::name('sw.listMoneyBoxType')
            ->get('/money-box-type', 'Front\GymSettingFrontController@indexMoneyBoxType');
        Route::name('sw.createMoneyBoxType')
            ->get('money-box-type/create', 'Front\GymSettingFrontController@createMoneyBoxType');
        Route::name('sw.createMoneyBoxType')
            ->post('money-box-type/create', 'Front\GymSettingFrontController@storeMoneyBoxType');
        Route::name('sw.editMoneyBoxType')
            ->get('money-box-type/{money_box_type}/edit', 'Front\GymSettingFrontController@editMoneyBoxType');
        Route::name('sw.editMoneyBoxType')
            ->post('money-box-type/{money_box_type}/edit', 'Front\GymSettingFrontController@updateMoneyBoxType');
        Route::name('sw.deleteMoneyBoxType')
            ->get('money-box-type/{money_box_type}/delete', 'Front\GymSettingFrontController@destroyMoneyBoxType');


    });
