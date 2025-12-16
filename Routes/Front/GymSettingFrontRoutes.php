<?php

Route::prefix('setting')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

    // Edit general settings - edit permission
    Route::group(['defaults' => ['permission' => 'editSetting']], function () {
        Route::name('sw.editSetting')
            ->get('', 'Front\GymSettingFrontController@edit');
        Route::name('sw.editSetting')
            ->post('', 'Front\GymSettingFrontController@update');
        Route::name('sw.editSettingUploadImage')
            ->post('/ajax_upload/upload', 'Front\GymSettingFrontController@updateImage');
        Route::name('sw.editSettingDeleteUploadImage')
            ->post('/ajax_upload/delete', 'Front\GymSettingFrontController@updateImageDelete');
    });

    // List payment types - view permission
    Route::name('sw.listPaymentType')
        ->get('/payment-type', 'Front\GymSettingFrontController@indexPaymentType');

    // Create payment type - create permission
    Route::group(['defaults' => ['permission' => 'createPaymentType']], function () {
        Route::name('sw.createPaymentType')
            ->get('payment-type/create', 'Front\GymSettingFrontController@createPaymentType');
        Route::name('sw.createPaymentType')
            ->post('payment-type/create', 'Front\GymSettingFrontController@storePaymentType');
    });

    // Edit payment type - edit permission
    Route::group(['defaults' => ['permission' => 'editPaymentType']], function () {
        Route::name('sw.editPaymentType')
            ->get('payment-type/{payment_type}/edit', 'Front\GymSettingFrontController@editPaymentType');
        Route::name('sw.editPaymentType')
            ->post('payment-type/{payment_type}/edit', 'Front\GymSettingFrontController@updatePaymentType');
    });

    // Delete payment type - delete permission
    Route::name('sw.deletePaymentType')
        ->get('payment-type/{payment_type}/delete', 'Front\GymSettingFrontController@destroyPaymentType');

    // List group discounts - view permission
    Route::name('sw.listGroupDiscount')
        ->get('/group-discount', 'Front\GymSettingFrontController@indexGroupDiscount');

    // Create group discount - create permission
    Route::group(['defaults' => ['permission' => 'createGroupDiscount']], function () {
        Route::name('sw.createGroupDiscount')
            ->get('group-discount/create', 'Front\GymSettingFrontController@createGroupDiscount');
        Route::name('sw.createGroupDiscount')
            ->post('group-discount/create', 'Front\GymSettingFrontController@storeGroupDiscount');
    });

    // Edit group discount - edit permission
    Route::group(['defaults' => ['permission' => 'editGroupDiscount']], function () {
        Route::name('sw.editGroupDiscount')
            ->get('group-discount/{group_discount}/edit', 'Front\GymSettingFrontController@editGroupDiscount');
        Route::name('sw.editGroupDiscount')
            ->post('group-discount/{group_discount}/edit', 'Front\GymSettingFrontController@updateGroupDiscount');
    });

    // Delete group discount - delete permission
    Route::name('sw.deleteGroupDiscount')
        ->get('group-discount/{group_discount}/delete', 'Front\GymSettingFrontController@destroyGroupDiscount');

    // List sale channels - view permission
    Route::name('sw.listSaleChannel')
        ->get('/sale-channel', 'Front\GymSettingFrontController@indexSaleChannel');

    // Create sale channel - create permission
    Route::group(['defaults' => ['permission' => 'createSaleChannel']], function () {
        Route::name('sw.createSaleChannel')
            ->get('sale-channel/create', 'Front\GymSettingFrontController@createSaleChannel');
        Route::name('sw.createSaleChannel')
            ->post('sale-channel/create', 'Front\GymSettingFrontController@storeSaleChannel');
    });

    // Edit sale channel - edit permission
    Route::group(['defaults' => ['permission' => 'editSaleChannel']], function () {
        Route::name('sw.editSaleChannel')
            ->get('sale-channel/{sale_channel}/edit', 'Front\GymSettingFrontController@editSaleChannel');
        Route::name('sw.editSaleChannel')
            ->post('sale-channel/{sale_channel}/edit', 'Front\GymSettingFrontController@updateSaleChannel');
    });

    // Delete sale channel - delete permission
    Route::name('sw.deleteSaleChannel')
        ->get('sale-channel/{sale_channel}/delete', 'Front\GymSettingFrontController@destroySaleChannel');

    // List store groups - view permission
    Route::name('sw.listStoreGroup')
        ->get('/store-group', 'Front\GymSettingFrontController@indexStoreGroup');

    // Create store group - create permission
    Route::group(['defaults' => ['permission' => 'createStoreGroup']], function () {
        Route::name('sw.createStoreGroup')
            ->get('store-group/create', 'Front\GymSettingFrontController@createStoreGroup');
        Route::name('sw.createStoreGroup')
            ->post('store-group/create', 'Front\GymSettingFrontController@storeStoreGroup');
    });

    // Edit store group - edit permission
    Route::group(['defaults' => ['permission' => 'editStoreGroup']], function () {
        Route::name('sw.editStoreGroup')
            ->get('store-group/{store_group}/edit', 'Front\GymSettingFrontController@editStoreGroup');
        Route::name('sw.editStoreGroup')
            ->post('store-group/{store_group}/edit', 'Front\GymSettingFrontController@updateStoreGroup');
    });

    // Delete store group - delete permission
    Route::name('sw.deleteStoreGroup')
        ->get('store-group/{store_group}/delete', 'Front\GymSettingFrontController@destroyStoreGroup');

    // List money box types - view permission
    Route::name('sw.listMoneyBoxType')
        ->get('/money-box-type', 'Front\GymSettingFrontController@indexMoneyBoxType');

    // Create money box type - create permission
    Route::group(['defaults' => ['permission' => 'createMoneyBoxType']], function () {
        Route::name('sw.createMoneyBoxType')
            ->get('money-box-type/create', 'Front\GymSettingFrontController@createMoneyBoxType');
        Route::name('sw.createMoneyBoxType')
            ->post('money-box-type/create', 'Front\GymSettingFrontController@storeMoneyBoxType');
    });

    // Edit money box type - edit permission
    Route::group(['defaults' => ['permission' => 'editMoneyBoxType']], function () {
        Route::name('sw.editMoneyBoxType')
            ->get('money-box-type/{money_box_type}/edit', 'Front\GymSettingFrontController@editMoneyBoxType');
        Route::name('sw.editMoneyBoxType')
            ->post('money-box-type/{money_box_type}/edit', 'Front\GymSettingFrontController@updateMoneyBoxType');
    });

    // Delete money box type - delete permission
    Route::name('sw.deleteMoneyBoxType')
        ->get('money-box-type/{money_box_type}/delete', 'Front\GymSettingFrontController@destroyMoneyBoxType');

    });
