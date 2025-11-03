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
        
        Route::name('sw.loyalty_point_rules.index')
            ->get('/', 'Front\LoyaltyPointRuleFrontController@index');
        
        Route::name('sw.loyalty_point_rules.create')
            ->get('create', 'Front\LoyaltyPointRuleFrontController@create');
        
        Route::name('sw.loyalty_point_rules.store')
            ->post('create', 'Front\LoyaltyPointRuleFrontController@store');
        
        Route::name('sw.loyalty_point_rules.edit')
            ->get('{id}/edit', 'Front\LoyaltyPointRuleFrontController@edit');
        
        Route::name('sw.loyalty_point_rules.update')
            ->post('{id}/edit', 'Front\LoyaltyPointRuleFrontController@update');
        
        Route::name('sw.loyalty_point_rules.destroy')
            ->get('{id}/delete', 'Front\LoyaltyPointRuleFrontController@destroy');
        
        Route::name('sw.loyalty_point_rules.toggle_active')
            ->post('{id}/toggle-active', 'Front\LoyaltyPointRuleFrontController@toggleActive');
    });

// Loyalty Campaigns Routes
Route::prefix('loyalty/campaigns')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        
        Route::name('sw.loyalty_campaigns.index')
            ->get('/', 'Front\LoyaltyCampaignFrontController@index');
        
        Route::name('sw.loyalty_campaigns.create')
            ->get('create', 'Front\LoyaltyCampaignFrontController@create');
        
        Route::name('sw.loyalty_campaigns.store')
            ->post('create', 'Front\LoyaltyCampaignFrontController@store');
        
        Route::name('sw.loyalty_campaigns.edit')
            ->get('{id}/edit', 'Front\LoyaltyCampaignFrontController@edit');
        
        Route::name('sw.loyalty_campaigns.update')
            ->post('{id}/edit', 'Front\LoyaltyCampaignFrontController@update');
        
        Route::name('sw.loyalty_campaigns.destroy')
            ->get('{id}/delete', 'Front\LoyaltyCampaignFrontController@destroy');
        
        Route::name('sw.loyalty_campaigns.toggle_active')
            ->post('{id}/toggle-active', 'Front\LoyaltyCampaignFrontController@toggleActive');
        
        Route::name('sw.loyalty_campaigns.current')
            ->get('current', 'Front\LoyaltyCampaignFrontController@getCurrentCampaign');
    });

// Loyalty Transactions Routes
Route::prefix('loyalty/transactions')
    ->middleware(['auth:sw', 'sw_permission'])
    ->group(function () {
        
        Route::name('sw.loyalty_transactions.index')
            ->get('/', 'Front\LoyaltyTransactionFrontController@index');
        
        Route::name('sw.loyalty_transactions.create_manual')
            ->get('manual/create', 'Front\LoyaltyTransactionFrontController@createManual');
        
        Route::name('sw.loyalty_transactions.store_manual')
            ->post('manual/create', 'Front\LoyaltyTransactionFrontController@storeManual');
        
        Route::name('sw.loyalty_transactions.member_history')
            ->get('member/{memberId}/history', 'Front\LoyaltyTransactionFrontController@memberHistory');
        
        Route::name('sw.loyalty_transactions.export')
            ->get('export', 'Front\LoyaltyTransactionFrontController@export');
    });

