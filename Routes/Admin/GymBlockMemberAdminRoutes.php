<?php

Route::prefix('operate/gymblockmember')
    ->middleware(['auth'])
    ->group(function () {
    Route::name('listGymBlockMember')
        ->get('/', 'Admin\GymBlockMemberAdminController@index')
        ->middleware(['permission:super|gym-block-member-index']);
    Route::name('createGymBlockMember')
        ->get('create', 'Admin\GymBlockMemberAdminController@create')
        ->middleware(['permission:super|gym-block-member-create']);
    Route::name('storeGymBlockMember')
        ->post('create', 'Admin\GymBlockMemberAdminController@store')
        ->middleware(['permission:super|gym-block-member-create']);
    Route::name('editGymBlockMember')
        ->get('{gymblockmember}/edit', 'Admin\GymBlockMemberAdminController@edit')
        ->middleware(['permission:super|gym-block-member-edit']);
    Route::name('editGymBlockMember')
        ->post('{gymblockmember}/edit', 'Admin\GymBlockMemberAdminController@update')
        ->middleware(['permission:super|gym-block-member-edit']);
    Route::name('deleteGymBlockMember')
        ->get('{gymblockmember}/delete', 'Admin\GymBlockMemberAdminController@destroy')
        ->middleware(['permission:super|gym-block-member-destroy']);
});
