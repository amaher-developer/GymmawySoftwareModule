<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add active_ai column to settings table
 * 
 * This migration adds the active_ai feature flag to control
 * AI module visibility and functionality
 */
class AddActiveAiToSettingsTable20250120 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('settings', function (Blueprint $table) {
        //     if (!Schema::hasColumn('settings', 'active_ai')) {
        //         $table->boolean('active_ai')->default(0)->nullable()->after('active_telegram');
        //     }
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('settings', function (Blueprint $table) {
        //     if (Schema::hasColumn('settings', 'active_ai')) {
        //         $table->dropColumn('active_ai');
        //     }
        // });
    }
}


