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
        // Add deleted_at to assessments
        if (Schema::hasTable('sw_gym_training_assessments')) {
            Schema::table('sw_gym_training_assessments', function (Blueprint $table) {
                if (!Schema::hasColumn('sw_gym_training_assessments', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Add deleted_at to member logs
        if (Schema::hasTable('sw_gym_training_member_logs')) {
            Schema::table('sw_gym_training_member_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('sw_gym_training_member_logs', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Add deleted_at to AI recommendations
        if (Schema::hasTable('sw_ai_recommendations')) {
            Schema::table('sw_ai_recommendations', function (Blueprint $table) {
                if (!Schema::hasColumn('sw_ai_recommendations', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Add deleted_at to templates
        if (Schema::hasTable('sw_gym_training_templates')) {
            Schema::table('sw_gym_training_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('sw_gym_training_templates', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Add deleted_at to feedback
        if (Schema::hasTable('sw_gym_training_feedback')) {
            Schema::table('sw_gym_training_feedback', function (Blueprint $table) {
                if (!Schema::hasColumn('sw_gym_training_feedback', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Add deleted_at to notifications
        if (Schema::hasTable('sw_gym_training_notifications')) {
            Schema::table('sw_gym_training_notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('sw_gym_training_notifications', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove deleted_at from all tables
        $tables = [
            'sw_gym_training_assessments',
            'sw_gym_training_member_logs',
            'sw_ai_recommendations',
            'sw_gym_training_templates',
            'sw_gym_training_feedback',
            'sw_gym_training_notifications',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
