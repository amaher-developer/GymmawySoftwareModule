<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_members', 'additional_info')) {
                $table->text('additional_info')->nullable()->after('dob');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_members', 'additional_info')) {
                $table->dropColumn('additional_info');
            }
        });
    }
};

