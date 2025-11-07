<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('sw_gym_training_member_logs')) {
            Schema::table('sw_gym_training_member_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('sw_gym_training_member_logs', 'reference_id')) {
                    $table->unsignedBigInteger('reference_id')->nullable()->after('training_type')->index()
                          ->comment('ID of related record (assessment_id, plan_id, medicine_id, etc.)');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sw_gym_training_member_logs')) {
            Schema::table('sw_gym_training_member_logs', function (Blueprint $table) {
                if (Schema::hasColumn('sw_gym_training_member_logs', 'reference_id')) {
                    $table->dropColumn('reference_id');
                }
            });
        }
    }
};
