<?php

Route::prefix('operate/activity')
    ->middleware(['auth'])
    ->group(function () {
    Route::name('listActivity')
        ->get('/', 'Admin\GymActivityAdminController@index')
        ->middleware(['permission:super|activity-index']);
    Route::name('createActivity')
        ->get('create', 'Admin\GymActivityAdminController@create')
        ->middleware(['permission:super|activity-create']);
    Route::name('storeActivity')
        ->post('create', 'Admin\GymActivityAdminController@store')
        ->middleware(['permission:super|activity-create']);
    Route::name('editActivity')
        ->get('{activity}/edit', 'Admin\GymActivityAdminController@edit')
        ->middleware(['permission:super|activity-edit']);
    Route::name('editActivity')
        ->post('{activity}/edit', 'Admin\GymActivityAdminController@update')
        ->middleware(['permission:super|activity-edit']);
    Route::name('deleteActivity')
        ->get('{activity}/delete', 'Admin\GymActivityAdminController@destroy')
        ->middleware(['permission:super|activity-destroy']);
});
