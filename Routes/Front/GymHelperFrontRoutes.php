<?php


Route::prefix('helper')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.listHelperTools')
            ->get('tools', 'Front\GymHelperToolsFrontController@list');


        Route::name('sw.calculateCalories')
            ->get('tools/calculate-calories/', 'Front\GymHelperToolsFrontController@calories');
        Route::name('sw.calculateCaloriesResult')
            ->post('tools/calculate-calories-result/', 'Front\GymHelperToolsFrontController@caloriesResult');

        Route::name('sw.calculateBMI')
            ->get('tools/calculate-bmi/', 'Front\GymHelperToolsFrontController@bmi');
        Route::name('sw.calculateBMIResult')
            ->post('tools/calculate-bmi-result/', 'Front\GymHelperToolsFrontController@bmiResult');

        Route::name('sw.calculateIBW')
            ->get('tools/calculate-ibw/', 'Front\GymHelperToolsFrontController@ibw');
        Route::name('calculateIBWResult')
            ->post('tools/calculate-ibw-result/', 'Front\GymHelperToolsFrontController@ibwResult');

        Route::name('sw.calculateWater')
            ->get('tools/calculate-water/', 'Front\GymHelperToolsFrontController@water');
        Route::name('sw.calculateWaterResult')
            ->post('tools/calculate-water-result/', 'Front\GymHelperToolsFrontController@waterResult');

        Route::name('sw.calculateVatPercentage')
            ->get('tools/calculate-vat-percentage/', 'Front\GymHelperToolsFrontController@calculateVatPercentage');
        Route::name('sw.calculateVatPercentageResult')
            ->post('tools/calculate-vat-percentage-result/', 'Front\GymHelperToolsFrontController@calculateVatPercentageResult');
});

