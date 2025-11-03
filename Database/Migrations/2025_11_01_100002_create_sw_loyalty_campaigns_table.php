<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration to create loyalty campaigns table
 * 
 * This table stores promotional campaigns that multiply points earned during specific periods
 */
class CreateSwLoyaltyCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_loyalty_campaigns', function (Blueprint $table) {
            $table->increments('id');

            // Branch association (multi-branch support)
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            // Campaign details
            $table->string('name')->comment('Campaign name (e.g., "Ramadan 2x Points")');
            
            $table->decimal('multiplier', 5, 2)->default(1.00)
                ->comment('Points multiplier (e.g., 2.00 = double points, 1.5 = 50% bonus)');

            // Time period
            $table->dateTime('start_date')->comment('When campaign starts');
            $table->dateTime('end_date')->comment('When campaign ends');

            // Optional targeting
            $table->string('applies_to')->nullable()
                ->comment('Optional: specific subscription/product types this applies to');

            // Status
            $table->boolean('is_active')->default(true)
                ->comment('Whether this campaign is currently active');

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
        Schema::dropIfExists('sw_loyalty_campaigns');
    }
}

