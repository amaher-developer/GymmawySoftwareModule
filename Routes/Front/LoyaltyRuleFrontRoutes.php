<?php

/**
 * Loyalty System Front Routes
 *
 * Routes for managing loyalty point rules, campaigns, and transactions
 * (Using Front controllers with sw_permission middleware)
 */

// Loyalty Point Rules Routes
Route::prefix('loyalty/rules')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List loyalty point rules - view permission
        Route::group(['defaults' => ['permission' => 'loyalty_point_rules.index']], function () {
            Route::name('sw.loyalty_point_rules.index')
                ->get('/', 'Front\LoyaltyPointRuleFrontController@index');
        });

        // Create loyalty point rule - create permission
        Route::group(['defaults' => ['permission' => 'loyalty_point_rules.create']], function () {
            Route::name('sw.loyalty_point_rules.create')
                ->get('create', 'Front\LoyaltyPointRuleFrontController@create');
            Route::name('sw.loyalty_point_rules.store')
                ->post('create', 'Front\LoyaltyPointRuleFrontController@store');
        });

        // Edit loyalty point rule - edit permission
        Route::group(['defaults' => ['permission' => 'loyalty_point_rules.edit']], function () {
            Route::name('sw.loyalty_point_rules.edit')
                ->get('{id}/edit', 'Front\LoyaltyPointRuleFrontController@edit');
            Route::name('sw.loyalty_point_rules.update')
                ->post('{id}/edit', 'Front\LoyaltyPointRuleFrontController@update');
        });

        // Delete loyalty point rule - delete permission
        Route::group(['defaults' => ['permission' => 'loyalty_point_rules.destroy']], function () {
            Route::name('sw.loyalty_point_rules.destroy')
                ->get('{id}/delete', 'Front\LoyaltyPointRuleFrontController@destroy');
        });

        // Toggle active loyalty point rule - edit permission
        Route::group(['defaults' => ['permission' => 'loyalty_point_rules.toggle_active']], function () {
            Route::name('sw.loyalty_point_rules.toggle_active')
                ->post('{id}/toggle-active', 'Front\LoyaltyPointRuleFrontController@toggleActive');
        });

    });

// Loyalty Campaigns Routes
Route::prefix('loyalty/campaigns')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List loyalty campaigns - view permission
        Route::group(['defaults' => ['permission' => 'loyalty_campaigns.index']], function () {
            Route::name('sw.loyalty_campaigns.index')
                ->get('/', 'Front\LoyaltyCampaignFrontController@index');
            Route::name('sw.loyalty_campaigns.current')
                ->get('current', 'Front\LoyaltyCampaignFrontController@getCurrentCampaign');
        });

        // Create loyalty campaign - create permission
        Route::group(['defaults' => ['permission' => 'loyalty_campaigns.create']], function () {
            Route::name('sw.loyalty_campaigns.create')
                ->get('create', 'Front\LoyaltyCampaignFrontController@create');
            Route::name('sw.loyalty_campaigns.store')
                ->post('create', 'Front\LoyaltyCampaignFrontController@store');
        });

        // Edit loyalty campaign - edit permission
        Route::group(['defaults' => ['permission' => 'loyalty_campaigns.edit']], function () {
            Route::name('sw.loyalty_campaigns.edit')
                ->get('{id}/edit', 'Front\LoyaltyCampaignFrontController@edit');
            Route::name('sw.loyalty_campaigns.update')
                ->post('{id}/edit', 'Front\LoyaltyCampaignFrontController@update');
        });

        // Delete loyalty campaign - delete permission
        Route::group(['defaults' => ['permission' => 'loyalty_campaigns.destroy']], function () {
            Route::name('sw.loyalty_campaigns.destroy')
                ->get('{id}/delete', 'Front\LoyaltyCampaignFrontController@destroy');
        });

        // Toggle active loyalty campaign - edit permission
        Route::group(['defaults' => ['permission' => 'loyalty_campaigns.toggle_active']], function () {
            Route::name('sw.loyalty_campaigns.toggle_active')
                ->post('{id}/toggle-active', 'Front\LoyaltyCampaignFrontController@toggleActive');
        });

    });

// Loyalty Transactions Routes
Route::prefix('loyalty/transactions')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {

        // List loyalty transactions - view permission
        Route::group(['defaults' => ['permission' => 'loyalty_transactions.index']], function () {
            Route::name('sw.loyalty_transactions.index')
                ->get('/', 'Front\LoyaltyTransactionFrontController@index');
            Route::name('sw.loyalty_transactions.member_history')
                ->get('member/{memberId}/history', 'Front\LoyaltyTransactionFrontController@memberHistory');
            Route::name('sw.loyalty_transactions.export')
                ->get('export', 'Front\LoyaltyTransactionFrontController@export');
        });

        // Create manual loyalty transaction - create permission
        Route::group(['defaults' => ['permission' => 'loyalty_transactions.create_manual']], function () {
            Route::name('sw.loyalty_transactions.create_manual')
                ->get('manual/create', 'Front\LoyaltyTransactionFrontController@createManual');
            Route::name('sw.loyalty_transactions.store_manual')
                ->post('manual/create', 'Front\LoyaltyTransactionFrontController@storeManual');
        });

    });
