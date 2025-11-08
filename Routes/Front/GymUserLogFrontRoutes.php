<?php
Route::middleware(['auth:sw', 'sw_permission'])
    ->name('sw.listReports')
    ->get('reports', 'Front\GymUserLogFrontController@reports');

Route::prefix('user/log')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        Route::name('sw.listUserLog')
            ->get('/', 'Front\GymUserLogFrontController@index');

        Route::name('sw.reportRenewMemberList')
            ->get('renew', 'Front\GymUserLogFrontController@reportRenewMemberList');

        Route::name('sw.reportExpireMemberList')
            ->get('expire', 'Front\GymUserLogFrontController@reportExpireMemberList');

        Route::name('sw.exportExpireMemberPDF')
            ->get('/expire-member-pdf', 'Front\GymUserLogFrontController@exportExpireMemberPDF');
        Route::name('sw.exportExpireMemberExcel')
            ->get('/expire-member-excel', 'Front\GymUserLogFrontController@exportExpireMemberExcel');

        Route::name('sw.exportRenewMemberPDF')
            ->get('/renew-member-pdf', 'Front\GymUserLogFrontController@exportRenewMemberPDF');
        Route::name('sw.exportRenewMemberExcel')
            ->get('/renew-member-excel', 'Front\GymUserLogFrontController@exportRenewMemberExcel');


        Route::name('sw.reportSubscriptionMemberList')
            ->get('subscription', 'Front\GymUserLogFrontController@reportSubscriptionMemberList');

        Route::name('sw.exportSubscriptionMemberPDF')
            ->get('/subscription-member-pdf', 'Front\GymUserLogFrontController@exportSubscriptionMemberPDF');
        Route::name('sw.exportSubscriptionMemberExcel')
            ->get('/subscription-member-excel', 'Front\GymUserLogFrontController@exportSubscriptionMemberExcel');


        Route::name('sw.reportPTSubscriptionMemberList')
            ->get('pt-subscription', 'Front\GymUserLogFrontController@reportPTSubscriptionMemberList');

        Route::name('sw.exportPTSubscriptionMemberPDF')
            ->get('/pt-subscription-member-pdf', 'Front\GymUserLogFrontController@exportPTSubscriptionMemberPDF');
        Route::name('sw.exportPTSubscriptionMemberExcel')
            ->get('/pt-subscription-member-excel', 'Front\GymUserLogFrontController@exportPTSubscriptionMemberExcel');


        Route::name('sw.reportDetailMemberList')
            ->get('detail', 'Front\GymUserLogFrontController@reportDetailMemberList');


        Route::name('sw.reportTodayMemberList')
            ->get('today', 'Front\GymUserLogFrontController@reportTodayMemberList');

        Route::name('sw.exportTodayMemberPDF')
            ->get('/today-member-pdf', 'Front\GymUserLogFrontController@exportTodayMemberPDF');
        Route::name('sw.exportTodayMemberExcel')
            ->get('/today-member-excel', 'Front\GymUserLogFrontController@exportTodayMemberExcel');



        Route::name('sw.reportTodayPTMemberList')
            ->get('pt-today', 'Front\GymUserLogFrontController@reportTodayPTMemberList');

        Route::name('sw.exportTodayPTMemberPDF')
            ->get('/today-pt-member-pdf', 'Front\GymUserLogFrontController@exportTodayPTMemberPDF');
        Route::name('sw.exportTodayPTMemberExcel')
            ->get('/today-pt-member-excel', 'Front\GymUserLogFrontController@exportTodayPTMemberExcel');



        Route::name('sw.reportTodayNonMemberList')
            ->get('non-member-today', 'Front\GymUserLogFrontController@reportTodayNonMemberList');

        Route::name('sw.exportTodayNonMemberPDF')
            ->get('/today-non-member-pdf', 'Front\GymUserLogFrontController@exportTodayNonMemberPDF');
        Route::name('sw.exportTodayNonMemberExcel')
            ->get('/today-non-member-excel', 'Front\GymUserLogFrontController@exportTodayNonMemberExcel');



        Route::name('sw.reportUserAttendeesList')
            ->get('user-attendees', 'Front\GymUserLogFrontController@reportUserAttendeesList');

        Route::name('sw.reportStoreList')
            ->get('store', 'Front\GymUserLogFrontController@reportStoreList');

        Route::name('sw.reportZatcaInvoices')
            ->get('zatca-invoices', 'Front\GymUserLogFrontController@reportZatcaInvoices');

        Route::name('sw.reportMoneyboxTax')
            ->get('moneybox-tax', 'Front\GymUserLogFrontController@reportMoneyboxTax');

        Route::name('sw.reportOnlinePaymentTransactionList')
            ->get('online-payment-transaction', 'Front\GymUserLogFrontController@reportOnlinePaymentTransactionList');



        Route::name('sw.exportMoneyBoxTaxPDF')
            ->get('/moneybox-tax/pdf', 'Front\GymUserLogFrontController@exportPDFMoneyboxTax');
        Route::name('sw.exportMoneyBoxTaxExcel')
            ->get('/moneybox-tax/excel', 'Front\GymUserLogFrontController@exportExcelMoneyboxTax');



        Route::name('sw.reportUserNotificationsList')
            ->get('/user-notifications', 'Front\GymUserLogFrontController@reportUserNotificationsList');

        Route::name('sw.reportFreezeMemberList')
            ->get('freeze-members', 'Front\GymUserLogFrontController@reportFreezeMemberList');

        Route::name('sw.exportFreezeMemberPDF')
            ->get('/freeze-members-pdf', 'Front\GymUserLogFrontController@exportFreezeMemberPDF');
        Route::name('sw.exportFreezeMemberExcel')
            ->get('/freeze-members-excel', 'Front\GymUserLogFrontController@exportFreezeMemberExcel');

        });


