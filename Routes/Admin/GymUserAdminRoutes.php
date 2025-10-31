<?php

Route::prefix('operate/gymuser')
    ->middleware(['auth'])
    ->group(function () {
    Route::name('listGymUser')
        ->get('/', 'Admin\GymUserAdminController@index')
        ->middleware(['permission:super|gym-user-index']);
    Route::name('createGymUser')
        ->get('create', 'Admin\GymUserAdminController@create')
        ->middleware(['permission:super|gym-user-create']);
    Route::name('storeGymUser')
        ->post('create', 'Admin\GymUserAdminController@store')
        ->middleware(['permission:super|gym-user-create']);
    Route::name('editGymUser')
        ->get('{gymuser}/edit', 'Admin\GymUserAdminController@edit')
        ->middleware(['permission:super|gym-user-edit']);
    Route::name('editGymUser')
        ->post('{gymuser}/edit', 'Admin\GymUserAdminController@update')
        ->middleware(['permission:super|gym-user-edit']);
    Route::name('deleteGymUser')
        ->get('{gymuser}/delete', 'Admin\GymUserAdminController@destroy')
        ->middleware(['permission:super|gym-user-destroy']);
});
