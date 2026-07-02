<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sw_gym_reservations')) return;

        if (!Schema::hasColumn('sw_gym_reservations', 'source_reservation_id')) {
            Schema::table('sw_gym_reservations', function (Blueprint $table) {
                // Links an attendance log row back to the booking row that created it.
                // Null = created from home page or old data.
                $table->unsignedInteger('source_reservation_id')->nullable()->index()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sw_gym_reservations') &&
            Schema::hasColumn('sw_gym_reservations', 'source_reservation_id')) {
            Schema::table('sw_gym_reservations', function (Blueprint $table) {
                $table->dropColumn('source_reservation_id');
            });
        }
    }
};
