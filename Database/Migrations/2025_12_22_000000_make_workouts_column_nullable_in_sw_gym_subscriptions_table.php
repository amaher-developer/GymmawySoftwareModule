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
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->integer('workouts')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->integer('workouts')->nullable(false)->change();
        });
    }
};
