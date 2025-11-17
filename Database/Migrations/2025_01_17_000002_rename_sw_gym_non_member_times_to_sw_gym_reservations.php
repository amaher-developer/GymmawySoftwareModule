<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing sw_gym_reservations table if it exists
        if (Schema::hasTable('sw_gym_reservations')) {
            Schema::dropIfExists('sw_gym_reservations');
        }

        // Rename sw_gym_non_member_times to sw_gym_reservations
        if (Schema::hasTable('sw_gym_non_member_times')) {
            Schema::rename('sw_gym_non_member_times', 'sw_gym_reservations');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sw_gym_reservations')) {
            Schema::rename('sw_gym_reservations', 'sw_gym_non_member_times');
        }
    }
};

