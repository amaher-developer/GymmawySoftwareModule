<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sw_gym_subscription_options')) return;

        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_subscription_options', 'product_id')) {
                $table->unsignedInteger('product_id')->nullable()->after('option_group_id');
            }
        });

        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_subscription_options', 'name_ar')) {
                $table->dropColumn('name_ar');
            }
            if (Schema::hasColumn('sw_gym_subscription_options', 'name_en')) {
                $table->dropColumn('name_en');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('sw_gym_subscription_options')) return;

        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_subscription_options', 'name_ar')) {
                $table->string('name_ar')->default('')->after('option_group_id');
            }
            if (!Schema::hasColumn('sw_gym_subscription_options', 'name_en')) {
                $table->string('name_en')->default('')->after('name_ar');
            }
        });

        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_subscription_options', 'product_id')) {
                $table->dropColumn('product_id');
            }
        });
    }
};
