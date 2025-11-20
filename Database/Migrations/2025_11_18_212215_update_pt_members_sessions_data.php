<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update total_sessions and remaining_sessions based on existing data
        // total_sessions = classes
        // remaining_sessions = (classes - visits), ensuring it's not negative
        
        DB::statement('
            UPDATE sw_gym_pt_members 
            SET 
                total_sessions = COALESCE(classes, 0),
                remaining_sessions = GREATEST(
                    COALESCE(classes, 0) - COALESCE(visits, 0),
                    0
                )
            WHERE 
                deleted_at IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only updates data, cannot be safely reversed
        // The down method is left empty as reverting would require tracking original values
    }
};
