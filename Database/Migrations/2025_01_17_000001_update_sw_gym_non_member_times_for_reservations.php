<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sw_gym_non_member_times', function (Blueprint $table) {

            if (!Schema::hasColumn('sw_gym_non_member_times', 'client_type')) {
                $table->enum('client_type', ['member', 'non_member'])
                      ->default('non_member')->after('id');
            }

            if (!Schema::hasColumn('sw_gym_non_member_times', 'member_id')) {
                $table->unsignedBigInteger('member_id')->nullable()->after('client_type');
            }

            if (!Schema::hasColumn('sw_gym_non_member_times', 'non_member_id')) {
                $table->unsignedBigInteger('non_member_id')->nullable()->after('member_id');
            }

            if (!Schema::hasColumn('sw_gym_non_member_times', 'activity_id')) {
                $table->unsignedBigInteger('activity_id')->nullable()->after('non_member_id');
            }

            if (!Schema::hasColumn('sw_gym_non_member_times', 'reservation_date')) {
                $table->date('reservation_date')->nullable()->after('activity_id');
            }

            if (!Schema::hasColumn('sw_gym_non_member_times', 'start_time')) {
                $table->time('start_time')->nullable()->after('reservation_date');
            }

            if (!Schema::hasColumn('sw_gym_non_member_times', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }

            if (!Schema::hasColumn('sw_gym_non_member_times', 'status')) {
                $table->enum('status', ['pending','confirmed','cancelled','missed','attended'])
                      ->default('confirmed')->after('end_time');
            }

            if (!Schema::hasColumn('sw_gym_non_member_times', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('sw_gym_non_member_times', 'notes')) {
                $table->text('notes')->nullable()->after('cancelled_at');
            }
        });
    }

    public function down(): void
    {
        // No rollback to avoid dropping user data
    }
};

