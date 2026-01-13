<?php

// List reports - view permission
Route::middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.listReports')
            ->get('reports', 'Front\GymUserLogFrontController@reports');
    });

Route::prefix('user/log')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List user logs - view permission
        Route::name('sw.listUserLog')
            ->get('/', 'Front\GymUserLogFrontController@index');

        // Renew member report - view permission
        Route::name('sw.reportRenewMemberList')
            ->get('renew', 'Front\GymUserLogFrontController@reportRenewMemberList');
        Route::name('sw.exportRenewMemberPDF')
            ->get('/renew-member-pdf', 'Front\GymUserLogFrontController@exportRenewMemberPDF');
        Route::name('sw.exportRenewMemberExcel')
            ->get('/renew-member-excel', 'Front\GymUserLogFrontController@exportRenewMemberExcel');

        // Expire member report - view permission
        Route::name('sw.reportExpireMemberList')
            ->get('expire', 'Front\GymUserLogFrontController@reportExpireMemberList');
        Route::name('sw.exportExpireMemberPDF')
            ->get('/expire-member-pdf', 'Front\GymUserLogFrontController@exportExpireMemberPDF');
        Route::name('sw.exportExpireMemberExcel')
            ->get('/expire-member-excel', 'Front\GymUserLogFrontController@exportExpireMemberExcel');

        // Subscription member report - view permission
        Route::name('sw.reportSubscriptionMemberList')
            ->get('subscription', 'Front\GymUserLogFrontController@reportSubscriptionMemberList');
        Route::name('sw.exportSubscriptionMemberPDF')
            ->get('/subscription-member-pdf', 'Front\GymUserLogFrontController@exportSubscriptionMemberPDF');
        Route::name('sw.exportSubscriptionMemberExcel')
            ->get('/subscription-member-excel', 'Front\GymUserLogFrontController@exportSubscriptionMemberExcel');

        // PT subscription member report - view permission
        Route::name('sw.reportPTSubscriptionMemberList')
            ->get('pt-subscription', 'Front\GymUserLogFrontController@reportPTSubscriptionMemberList');
        Route::name('sw.exportPTSubscriptionMemberPDF')
            ->get('/pt-subscription-member-pdf', 'Front\GymUserLogFrontController@exportPTSubscriptionMemberPDF');
        Route::name('sw.exportPTSubscriptionMemberExcel')
            ->get('/pt-subscription-member-excel', 'Front\GymUserLogFrontController@exportPTSubscriptionMemberExcel');

        // Detail member report - view permission
        Route::name('sw.reportDetailMemberList')
            ->get('detail', 'Front\GymUserLogFrontController@reportDetailMemberList');

        // Today member report - view permission
        Route::name('sw.reportTodayMemberList')
            ->get('today', 'Front\GymUserLogFrontController@reportTodayMemberList');
        Route::name('sw.exportTodayMemberPDF')
            ->get('/today-member-pdf', 'Front\GymUserLogFrontController@exportTodayMemberPDF');
        Route::name('sw.exportTodayMemberExcel')
            ->get('/today-member-excel', 'Front\GymUserLogFrontController@exportTodayMemberExcel');

        // Attendance management - create and delete permissions
        Route::name('sw.createAttendance')
            ->post('/create-attendance', 'Front\GymUserLogFrontController@createAttendance');
        Route::name('sw.deleteAttendance')
            ->delete('/attendance/delete/{id}', 'Front\GymUserLogFrontController@deleteAttendance');

        // Today PT member report - view permission
        Route::name('sw.reportTodayPTMemberList')
            ->get('pt-today', 'Front\GymUserLogFrontController@reportTodayPTMemberList');
        Route::name('sw.exportTodayPTMemberPDF')
            ->get('/today-pt-member-pdf', 'Front\GymUserLogFrontController@exportTodayPTMemberPDF');
        Route::name('sw.exportTodayPTMemberExcel')
            ->get('/today-pt-member-excel', 'Front\GymUserLogFrontController@exportTodayPTMemberExcel');

        // Today non-member report - view permission
        Route::name('sw.reportTodayNonMemberList')
            ->get('non-member-today', 'Front\GymUserLogFrontController@reportTodayNonMemberList');
        Route::name('sw.exportTodayNonMemberPDF')
            ->get('/today-non-member-pdf', 'Front\GymUserLogFrontController@exportTodayNonMemberPDF');
        Route::name('sw.exportTodayNonMemberExcel')
            ->get('/today-non-member-excel', 'Front\GymUserLogFrontController@exportTodayNonMemberExcel');

        // User attendees report - view permission
        Route::name('sw.reportUserAttendeesList')
            ->get('user-attendees', 'Front\GymUserLogFrontController@reportUserAttendeesList');
        Route::name('sw.exportUserAttendeesPDF')
            ->get('/user-attendees-pdf', 'Front\GymUserLogFrontController@exportUserAttendeesPDF');
        Route::name('sw.exportUserAttendeesExcel')
            ->get('/user-attendees-excel', 'Front\GymUserLogFrontController@exportUserAttendeesExcel');

        // Store report - view permission
        Route::name('sw.reportStoreList')
            ->get('store', 'Front\GymUserLogFrontController@reportStoreList');
        Route::name('sw.exportStorePDF')
            ->get('/store-pdf', 'Front\GymUserLogFrontController@exportStorePDF');
        Route::name('sw.exportStoreExcel')
            ->get('/store-excel', 'Front\GymUserLogFrontController@exportStoreExcel');

        // ZATCA invoices report - view permission
        Route::name('sw.reportZatcaInvoices')
            ->get('zatca-invoices', 'Front\GymUserLogFrontController@reportZatcaInvoices');

        // Moneybox tax report - view permission
        Route::name('sw.reportMoneyboxTax')
            ->get('moneybox-tax', 'Front\GymUserLogFrontController@reportMoneyboxTax');
        Route::name('sw.exportMoneyBoxTaxPDF')
            ->get('/moneybox-tax/pdf', 'Front\GymUserLogFrontController@exportPDFMoneyboxTax');
        Route::name('sw.exportMoneyBoxTaxExcel')
            ->get('/moneybox-tax/excel', 'Front\GymUserLogFrontController@exportExcelMoneyboxTax');

        // Online payment transaction report - view permission
        Route::name('sw.reportOnlinePaymentTransactionList')
            ->get('online-payment-transaction', 'Front\GymUserLogFrontController@reportOnlinePaymentTransactionList');
        Route::name('sw.exportOnlinePaymentPDF')
            ->get('/online-payment-pdf', 'Front\GymUserLogFrontController@exportOnlinePaymentPDF');
        Route::name('sw.exportOnlinePaymentExcel')
            ->get('/online-payment-excel', 'Front\GymUserLogFrontController@exportOnlinePaymentExcel');

        // User notifications report - view permission
        Route::name('sw.reportUserNotificationsList')
            ->get('/user-notifications', 'Front\GymUserLogFrontController@reportUserNotificationsList');

        // Freeze member report - view permission
        Route::name('sw.reportFreezeMemberList')
            ->get('freeze-members', 'Front\GymUserLogFrontController@reportFreezeMemberList');
        Route::name('sw.exportFreezeMemberPDF')
            ->get('/freeze-members-pdf', 'Front\GymUserLogFrontController@exportFreezeMemberPDF');
        Route::name('sw.exportFreezeMemberExcel')
            ->get('/freeze-members-excel', 'Front\GymUserLogFrontController@exportFreezeMemberExcel');

    });
