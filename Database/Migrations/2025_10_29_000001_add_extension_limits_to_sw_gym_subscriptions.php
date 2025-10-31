<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_subscriptions', 'max_extension_days')) {
                $table->integer('max_extension_days')->default(0)->after('number_times_freeze');
            }
            if (!Schema::hasColumn('sw_gym_subscriptions', 'max_freeze_extension_sum')) {
                $table->integer('max_freeze_extension_sum')->default(0)->after('max_extension_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_subscriptions', 'max_extension_days')) {
                $table->dropColumn('max_extension_days');
            }
            if (Schema::hasColumn('sw_gym_subscriptions', 'max_freeze_extension_sum')) {
                $table->dropColumn('max_freeze_extension_sum');
            }
        });
    }
};


