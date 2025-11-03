<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration to create loyalty point rules table
 * 
 * This table stores conversion rates and expiry settings for the loyalty points system
 */
class CreateSwLoyaltyPointRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_loyalty_point_rules', function (Blueprint $table) {
            $table->increments('id');

            // Branch association (multi-branch support)
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            // Rule name for identification
            $table->string('name')->comment('Name of the loyalty rule (e.g., "Standard Points Rule")');

            // Conversion rates
            $table->decimal('money_to_point_rate', 10, 2)->default(10.00)
                ->comment('Amount of money needed to earn 1 point (e.g., 10.00 = 10 EGP = 1 Point)');
            
            $table->decimal('point_to_money_rate', 10, 2)->default(10.00)
                ->comment('Value of 1 point when redeemed (e.g., 10.00 = 1 Point = 10 EGP)');

            // Expiry settings
            $table->integer('expires_after_days')->nullable()
                ->comment('Number of days until points expire (null = never expire)');

            // Status
            $table->boolean('is_active')->default(true)
                ->comment('Whether this rule is currently active');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sw_loyalty_point_rules');
    }
}

