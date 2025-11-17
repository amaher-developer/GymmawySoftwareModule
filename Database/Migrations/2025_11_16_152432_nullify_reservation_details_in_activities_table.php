<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Set all reservation_details to NULL in sw_gym_activities table.
     * This makes all activities available for reservation at any time.
     */
    public function up(): void
    {
        // Check if table exists
        if (!Schema::hasTable('sw_gym_activities')) {
            return;
        }

        // Check if column exists
        if (!Schema::hasColumn('sw_gym_activities', 'reservation_details')) {
            return;
        }

        // Set all reservation_details to NULL
        DB::table('sw_gym_activities')
            ->whereNotNull('reservation_details')
            ->update(['reservation_details' => null]);
    }

    /**
     * Reverse the migrations.
     * Note: This migration cannot be reversed as we don't know the original values.
     */
    public function down(): void
    {
        // Cannot reverse - original reservation_details values are lost
        // This is a data migration that cannot be undone
    }
};
