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
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'active_ai')) {
                $column = $table->boolean('active_ai')->default(0)->nullable();

                if (Schema::hasColumn('settings', 'active_telegram')) {
                    $column->after('active_telegram');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'active_ai')) {
                $table->dropColumn('active_ai');
            }
        });
    }
};