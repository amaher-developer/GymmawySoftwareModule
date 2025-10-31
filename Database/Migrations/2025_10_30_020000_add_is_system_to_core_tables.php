<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'sw_gym_activities',
            'sw_gym_subscriptions',
            'sw_gym_pt_classes',
            'sw_gym_store_products',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'is_system')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // place towards the end to avoid index/position issues across tables
                    $table->boolean('is_system')->default(true)->after('deleted_at');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'sw_gym_activities',
            'sw_gym_subscriptions',
            'sw_gym_pt_classes',
            'sw_gym_store_products',
        ];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'is_system')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('is_system');
                });
            }
        }
    }
};


