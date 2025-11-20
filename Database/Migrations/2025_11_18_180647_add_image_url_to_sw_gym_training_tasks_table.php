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
        if (!Schema::hasColumn('sw_gym_training_tasks', 'image_url')) {
            Schema::table('sw_gym_training_tasks', function (Blueprint $table) {
                $table->string('image_url')->nullable()->after('youtube_link');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sw_gym_training_tasks', 'image_url')) {
            Schema::table('sw_gym_training_tasks', function (Blueprint $table) {
                $table->dropColumn('image_url');
            });
        }
    }
};
