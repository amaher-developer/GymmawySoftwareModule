<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sw_gym_member_subscription_options')) {
            Schema::table('sw_gym_member_subscription_options', function (Blueprint $table) {
                if (Schema::hasColumn('sw_gym_member_subscription_options', 'price') &&
                    !Schema::hasColumn('sw_gym_member_subscription_options', 'price_snapshot')) {
                    $table->renameColumn('price', 'price_snapshot');
                }
            });
        }

        if (Schema::hasTable('sw_gym_subscription_products')) {
            $indexes = collect(DB::select("SHOW INDEX FROM sw_gym_subscription_products"))
                ->pluck('Key_name')->toArray();
            if (!in_array('uq_sub_product', $indexes)) {
                Schema::table('sw_gym_subscription_products', function (Blueprint $table) {
                    $table->unique(['subscription_id', 'product_id'], 'uq_sub_product');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sw_gym_member_subscription_options') &&
            Schema::hasColumn('sw_gym_member_subscription_options', 'price_snapshot')) {
            Schema::table('sw_gym_member_subscription_options', function (Blueprint $table) {
                $table->renameColumn('price_snapshot', 'price');
            });
        }

        if (Schema::hasTable('sw_gym_subscription_products')) {
            $indexes = collect(DB::select("SHOW INDEX FROM sw_gym_subscription_products"))
                ->pluck('Key_name')->toArray();
            if (in_array('uq_sub_product', $indexes)) {
                Schema::table('sw_gym_subscription_products', function (Blueprint $table) {
                    $table->dropUnique('uq_sub_product');
                });
            }
        }
    }
};
