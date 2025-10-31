<?php

Route::prefix('operate/nonmember')
    ->middleware(['auth'])
    ->group(function () {
    Route::name('listNonMember')
        ->get('/', 'Admin\GymNonMemberAdminController@index')
        ->middleware(['permission:super|non-member-index']);
    Route::name('createNonMember')
        ->get('create', 'Admin\GymNonMemberAdminController@create')
        ->middleware(['permission:super|non-member-create']);
    Route::name('storeNonMember')
        ->post('create', 'Admin\GymNonMemberAdminController@store')
        ->middleware(['permission:super|non-member-create']);
    Route::name('editNonMember')
        ->get('{nonmember}/edit', 'Admin\GymNonMemberAdminController@edit')
        ->middleware(['permission:super|non-member-edit']);
    Route::name('editNonMember')
        ->post('{nonmember}/edit', 'Admin\GymNonMemberAdminController@update')
        ->middleware(['permission:super|non-member-edit']);
    Route::name('deleteNonMember')
        ->get('{nonmember}/delete', 'Admin\GymNonMemberAdminController@destroy')
        ->middleware(['permission:super|non-member-destroy']);
});
