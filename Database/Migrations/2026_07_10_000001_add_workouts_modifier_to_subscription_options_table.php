<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_subscription_options', 'workouts_modifier')) {
                $table->integer('workouts_modifier')->nullable()->after('price_modifier');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            $table->dropColumn('workouts_modifier');
        });
    }
};
