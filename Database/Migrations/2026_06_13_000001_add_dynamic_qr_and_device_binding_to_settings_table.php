<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDynamicQrAndDeviceBindingToSettingsTable extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'enable_dynamic_qr')) {
                $table->boolean('enable_dynamic_qr')->default(0)->after('app_config');
            }
            if (!Schema::hasColumn('settings', 'qr_expiry_seconds')) {
                $table->integer('qr_expiry_seconds')->default(60)->after('enable_dynamic_qr');
            }
            if (!Schema::hasColumn('settings', 'enable_device_binding')) {
                $table->boolean('enable_device_binding')->default(0)->after('qr_expiry_seconds');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['enable_dynamic_qr', 'qr_expiry_seconds', 'enable_device_binding']);
        });
    }
}
