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
        if (Schema::hasTable('sw_gym_training_tracks')) {
            Schema::table('sw_gym_training_tracks', function (Blueprint $table) {
                if (!Schema::hasColumn('sw_gym_training_tracks', 'bmi')) {
                    $table->float('bmi')->nullable()->after('height')->comment('Body Mass Index');
                }
                if (!Schema::hasColumn('sw_gym_training_tracks', 'date')) {
                    $table->date('date')->nullable()->after('id')->comment('Measurement date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sw_gym_training_tracks')) {
            Schema::table('sw_gym_training_tracks', function (Blueprint $table) {
                if (Schema::hasColumn('sw_gym_training_tracks', 'bmi')) {
                    $table->dropColumn('bmi');
                }
                if (Schema::hasColumn('sw_gym_training_tracks', 'date')) {
                    $table->dropColumn('date');
                }
            });
        }
    }
};

