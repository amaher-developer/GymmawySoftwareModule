<?php

Route::prefix('member')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        Route::name('sw.listMember')
            ->get('/', 'Front\GymMemberFrontController@index');
        Route::name('sw.exportMemberPDF')
            ->get('/pdf', 'Front\GymMemberFrontController@exportPDF');
        Route::name('sw.exportMemberExcel')
            ->get('/excel', 'Front\GymMemberFrontController@exportExcel');
        Route::name('sw.createMember')
            ->get('create', 'Front\GymMemberFrontController@create');
        Route::name('sw.createMember')
            ->post('create', 'Front\GymMemberFrontController@store');
        Route::name('sw.editMember')
            ->get('{member}/edit', 'Front\GymMemberFrontController@edit');
        Route::name('sw.editMember')
            ->post('{member}/edit', 'Front\GymMemberFrontController@update');
        Route::name('sw.deleteMember')
            ->get('{member}/delete', 'Front\GymMemberFrontController@destroy');
        Route::name('sw.deleteMemberSubscription')
            ->get('subscription/{subscription}/delete', 'Front\GymMemberFrontController@destroySubscription');

        Route::name('sw.showMemberProfile')
            ->get('{member}/profile', 'Front\GymMemberFrontController@showProfile');

        Route::name('sw.creditMemberBalance')
            ->get('/balance', 'Front\GymMemberFrontController@creditMemberBalance');
        Route::name('sw.creditMemberBalanceAdd')
            ->post('/{member}/add-balance', 'Front\GymMemberFrontController@creditMemberBalanceAdd');

        Route::name('sw.createMemberPayAmountRemainingForm')
            ->get('/pay-amount-remaining', 'Front\GymMemberFrontController@payAmountRemaining');

        Route::name('sw.freezeMember')
            ->get('/freeze-member', 'Front\GymMemberFrontController@freezeMember');
        Route::name('sw.unfreezeMember')
            ->get('/unfreeze-member', 'Front\GymMemberFrontController@unfreezeMember');

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

        Route::name('sw.memberAttendees')
            ->get('/member-attendees', 'Front\GymMemberFrontController@memberAttendees');

        Route::name('sw.memberSubscriptionRenew')
            ->get('{id}/member-subscription-renew', 'Front\GymMemberFrontController@memberSubscriptionRenew');

        Route::name('sw.memberSubscriptionEdit')
            ->post('{id}/member-subscription-edit', 'Front\GymMemberFrontController@memberSubscriptionEdit');

        Route::name('sw.memberSubscriptionRenewStore')
            ->any('{id}/member-subscription-renew-store', 'Front\GymMemberFrontController@memberSubscriptionRenewStore');


        Route::name('sw.memberInvitationAttendees')
            ->get('/member-invitation-attendees', 'Front\GymMemberFrontController@memberInvitationAttendees');

        Route::name('sw.memberPTAttendees')
            ->get('/member-pt-attendees', 'Front\GymPTMemberFrontController@memberPTAttendees');


        Route::name('sw.memberActivityMembershipAttendees')
            ->get('/member-activity-membership-attendees', 'Front\GymMemberFrontController@memberActivityMembershipAttendees');



        Route::name('sw.updateSubscriptionsStatus')
            ->get('/update-subscriptions-status', 'Front\GymMemberFrontController@updateSubscriptionsStatus');


        Route::name('sw.fingerprintRefresh')
            ->get('/fingerprint-refresh', 'Front\GymMemberFrontController@fingerprintRefresh');

        Route::name('sw.membersRefresh')
            ->get('/members-refresh', 'Front\GymMemberFrontController@membersRefresh');

    });
