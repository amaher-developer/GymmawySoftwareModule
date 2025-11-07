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
                // Add action column if missing
                if (!Schema::hasColumn('sw_gym_training_member_logs', 'action')) {
                    $table->string('action')->nullable()->after('training_type')->comment('e.g., added, updated, removed');
                }
                
                // Add training_id column if missing
                if (!Schema::hasColumn('sw_gym_training_member_logs', 'training_id')) {
                    $table->unsignedBigInteger('training_id')->nullable()->after('member_id')->index();
                }
                
                // Add meta column if missing
                if (!Schema::hasColumn('sw_gym_training_member_logs', 'meta')) {
                    $table->text('meta')->nullable()->after('notes')->comment('JSON data with additional context');
                }
                
                // Add created_by column if missing
                if (!Schema::hasColumn('sw_gym_training_member_logs', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('meta');
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
                if (Schema::hasColumn('sw_gym_training_member_logs', 'action')) {
                    $table->dropColumn('action');
                }
                if (Schema::hasColumn('sw_gym_training_member_logs', 'training_id')) {
                    $table->dropColumn('training_id');
                }
                if (Schema::hasColumn('sw_gym_training_member_logs', 'meta')) {
                    $table->dropColumn('meta');
                }
                if (Schema::hasColumn('sw_gym_training_member_logs', 'created_by')) {
                    $table->dropColumn('created_by');
                }
            });
        }
    }
};
