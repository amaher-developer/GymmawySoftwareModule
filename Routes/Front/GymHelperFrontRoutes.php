<?php


Route::prefix('helper')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List helper tools - view permission
        Route::group(['defaults' => ['permission' => 'listHelperTools']], function () {
            Route::name('sw.listHelperTools')
                ->get('tools', 'Front\GymHelperToolsFrontController@list');
        });

        // Calculate calories - helper tool permission
        Route::group(['defaults' => ['permission' => 'calculateCalories']], function () {
            Route::name('sw.calculateCalories')
                ->get('tools/calculate-calories/', 'Front\GymHelperToolsFrontController@calories');
            Route::name('sw.calculateCaloriesResult')
                ->post('tools/calculate-calories-result/', 'Front\GymHelperToolsFrontController@caloriesResult');
        });

        // Calculate BMI - helper tool permission
        Route::group(['defaults' => ['permission' => 'calculateBMI']], function () {
            Route::name('sw.calculateBMI')
                ->get('tools/calculate-bmi/', 'Front\GymHelperToolsFrontController@bmi');
            Route::name('sw.calculateBMIResult')
                ->post('tools/calculate-bmi-result/', 'Front\GymHelperToolsFrontController@bmiResult');
        });

        // Calculate IBW - helper tool permission
        Route::group(['defaults' => ['permission' => 'calculateIBW']], function () {
            Route::name('sw.calculateIBW')
                ->get('tools/calculate-ibw/', 'Front\GymHelperToolsFrontController@ibw');
            Route::name('calculateIBWResult')
                ->post('tools/calculate-ibw-result/', 'Front\GymHelperToolsFrontController@ibwResult');
        });

        // Calculate water - helper tool permission
        Route::group(['defaults' => ['permission' => 'calculateWater']], function () {
            Route::name('sw.calculateWater')
                ->get('tools/calculate-water/', 'Front\GymHelperToolsFrontController@water');
            Route::name('sw.calculateWaterResult')
                ->post('tools/calculate-water-result/', 'Front\GymHelperToolsFrontController@waterResult');
        });

        // Calculate VAT percentage - helper tool permission
        Route::group(['defaults' => ['permission' => 'calculateVatPercentage']], function () {
            Route::name('sw.calculateVatPercentage')
                ->get('tools/calculate-vat-percentage/', 'Front\GymHelperToolsFrontController@calculateVatPercentage');
            Route::name('sw.calculateVatPercentageResult')
                ->post('tools/calculate-vat-percentage-result/', 'Front\GymHelperToolsFrontController@calculateVatPercentageResult');
        });

});

