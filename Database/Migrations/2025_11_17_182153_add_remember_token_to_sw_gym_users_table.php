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
        Schema::table('sw_gym_users', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_users', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sw_gym_users', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_users', 'remember_token')) {
                $table->dropRememberToken();
            }
        });
    }
};
