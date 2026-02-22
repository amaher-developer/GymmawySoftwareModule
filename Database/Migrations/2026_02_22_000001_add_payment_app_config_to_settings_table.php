<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentAppConfigToSettins extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'payments')) {
                $table->json('payments')->nullable()->after('billing');
            }
            if (!Schema::hasColumn('settings', 'app_config')) {
                $table->json('app_config')->nullable()->after('payments');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['payments', 'app_config']);
        });
    }
}
