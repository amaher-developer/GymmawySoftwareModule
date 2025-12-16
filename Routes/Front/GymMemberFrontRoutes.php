<?php

Route::prefix('member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List members - view permission
        Route::group(['defaults' => ['permission' => 'listMember']], function () {
            Route::name('sw.listMember')
                ->get('/', 'Front\GymMemberFrontController@index');
        });

        // Export members - view permission
        Route::group(['defaults' => ['permission' => 'listMember']], function () {
            Route::name('sw.exportMemberPDF')
                ->get('/pdf', 'Front\GymMemberFrontController@exportPDF');
            Route::name('sw.exportMemberExcel')
                ->get('/excel', 'Front\GymMemberFrontController@exportExcel');
        });

        // Create member - create permission
        Route::group(['defaults' => ['permission' => 'createMember']], function () {
            Route::name('sw.createMember')
                ->get('create', 'Front\GymMemberFrontController@create');
            Route::name('sw.createMember')
                ->post('create', 'Front\GymMemberFrontController@store');
        });

        // Edit member - edit permission
        Route::group(['defaults' => ['permission' => 'editMember']], function () {
            Route::name('sw.editMember')
                ->get('{member}/edit', 'Front\GymMemberFrontController@edit');
            Route::name('sw.editMember')
                ->post('{member}/edit', 'Front\GymMemberFrontController@update');
        });

        // Delete member - delete permission
        Route::group(['defaults' => ['permission' => 'deleteMember']], function () {
            Route::name('sw.deleteMember')
                ->get('{member}/delete', 'Front\GymMemberFrontController@destroy');
            Route::name('sw.deleteMemberSubscription')
                ->get('subscription/{subscription}/delete', 'Front\GymMemberFrontController@destroySubscription');
        });

        // View member profile - view permission (already in default_permissions in middleware)
        Route::group(['defaults' => ['permission' => 'showMemberProfile']], function () {
            Route::name('sw.showMemberProfile')
                ->get('{member}/profile', 'Front\GymMemberFrontController@showProfile');
        });

        // Credit member balance - edit permission (already in default_permissions in middleware)
        Route::group(['defaults' => ['permission' => 'creditMemberBalance']], function () {
            Route::name('sw.creditMemberBalance')
                ->get('/balance', 'Front\GymMemberFrontController@creditMemberBalance');
            Route::name('sw.creditMemberBalanceAdd')
                ->post('/{member}/add-balance', 'Front\GymMemberFrontController@creditMemberBalanceAdd');
        });

        // Pay amount remaining - edit permission
        //Route::group(['defaults' => ['permission' => 'createMemberPayAmountRemainingForm']], function () {
            Route::name('sw.createMemberPayAmountRemainingForm')
                ->get('/pay-amount-remaining', 'Front\GymMemberFrontController@payAmountRemaining');
        //});

        // Freeze/unfreeze member - edit permission
        //Route::group(['defaults' => ['permission' => 'editMember']], function () {
            Route::name('sw.freezeMember')
                ->get('/freeze-member', 'Front\GymMemberFrontController@freezeMember');
            Route::name('sw.unfreezeMember')
                ->get('/unfreeze-member', 'Front\GymMemberFrontController@unfreezeMember');
        //});

        // Generate and download barcodes - view permission
       // Route::group(['defaults' => ['permission' => 'listMember']], function () {
            Route::name('sw.generateBarcode')
                ->get('/barcode-generate', 'Front\GymMemberFrontController@generateBarcode');
            Route::name('sw.downloadCode')
                ->get('/download-code', 'Front\GymMemberFrontController@downloadCode');
            Route::name('sw.downloadMemberBarcode')
                ->get('/members/{member}/download-barcode', 'Front\GymMemberFrontController@downloadMemberBarcode');
            Route::name('sw.downloadQRCode')
                ->get('/download-qr-code', 'Front\GymMemberFrontController@downloadQRCode');
            Route::name('sw.downloadCard')
                ->get('/download-card', 'Front\GymMemberFrontController@downloadCard');
        //});

        // Member attendees - view permission (already in default_permissions in middleware)
        Route::group(['defaults' => ['permission' => 'memberAttendees']], function () {
            Route::name('sw.memberAttendees')
                ->get('/member-attendees', 'Front\GymMemberFrontController@memberAttendees');
        });

        // Member subscription operations - edit permission (already in default_permissions in middleware)
        Route::group(['defaults' => ['permission' => 'memberSubscriptionRenew']], function () {
            Route::name('sw.memberSubscriptionRenew')
                ->get('{id}/member-subscription-renew', 'Front\GymMemberFrontController@memberSubscriptionRenew');
            Route::name('sw.memberSubscriptionEdit')
                ->post('{id}/member-subscription-edit', 'Front\GymMemberFrontController@memberSubscriptionEdit');
            Route::name('sw.memberSubscriptionRenewStore')
                ->any('{id}/member-subscription-renew-store', 'Front\GymMemberFrontController@memberSubscriptionRenewStore');
        });

        // Member invitation attendees - view permission (already in default_permissions in middleware)
        Route::group(['defaults' => ['permission' => 'memberInvitationAttendees']], function () {
            Route::name('sw.memberInvitationAttendees')
                ->get('/member-invitation-attendees', 'Front\GymMemberFrontController@memberInvitationAttendees');
        });

        // Member PT attendees - view permission (already in default_permissions in middleware)
        Route::group(['defaults' => ['permission' => 'memberPTAttendees']], function () {
            Route::name('sw.memberPTAttendees')
                ->get('/member-pt-attendees', 'Front\GymPTMemberFrontController@memberPTAttendees');
        });

        // Member activity membership attendees - view permission (already in default_permissions in middleware)
        Route::group(['defaults' => ['permission' => 'memberActivityMembershipAttendees']], function () {
            Route::name('sw.memberActivityMembershipAttendees')
                ->get('/member-activity-membership-attendees', 'Front\GymMemberFrontController@memberActivityMembershipAttendees');
        });

        // Update subscriptions status - edit permission
        Route::group(['defaults' => ['permission' => 'editMember']], function () {
            Route::name('sw.updateSubscriptionsStatus')
                ->get('/update-subscriptions-status', 'Front\GymMemberFrontController@updateSubscriptionsStatus');
        });

        // Fingerprint refresh - view permission (already in default_permissions in middleware)
        Route::group(['defaults' => ['permission' => 'fingerprintRefresh']], function () {
            Route::name('sw.fingerprintRefresh')
                ->get('/fingerprint-refresh', 'Front\GymMemberFrontController@fingerprintRefresh');
        });

        // Members refresh - view permission (already in default_permissions in middleware)
        Route::group(['defaults' => ['permission' => 'membersRefresh']], function () {
            Route::name('sw.membersRefresh')
                ->get('/members-refresh', 'Front\GymMemberFrontController@membersRefresh');
        });

    });
