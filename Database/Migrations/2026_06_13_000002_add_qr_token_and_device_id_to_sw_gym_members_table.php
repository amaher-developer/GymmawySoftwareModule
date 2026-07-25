<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQrTokenAndDeviceIdToSwGymMembersTable extends Migration
{
    public function up(): void
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_members', 'qr_token')) {
                $table->string('qr_token', 64)->nullable();
            }
            if (!Schema::hasColumn('sw_gym_members', 'qr_token_expires_at')) {
                $table->timestamp('qr_token_expires_at')->nullable();
            }
            if (!Schema::hasColumn('sw_gym_members', 'device_id')) {
                $table->string('device_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'qr_token_expires_at', 'device_id']);
        });
    }
}
