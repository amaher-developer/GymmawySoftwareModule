<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sw_gym_member_subscription_options')) {
            Schema::table('sw_gym_member_subscription_options', function (Blueprint $table) {
                // Only rename if the old column still exists
                if (Schema::hasColumn('sw_gym_member_subscription_options', 'price') &&
                    !Schema::hasColumn('sw_gym_member_subscription_options', 'price_snapshot')) {
                    $table->dropForeign(['option_id']);
                    $table->foreign('option_id', 'fk_msopt_option_id_restrict')
                        ->references('id')
                        ->on('sw_gym_subscription_options')
                        ->onDelete('restrict');
                    $table->renameColumn('price', 'price_snapshot');
                }
            });
        }

        if (Schema::hasTable('sw_gym_subscription_products')) {
            $indexes = collect(\DB::select("SHOW INDEX FROM sw_gym_subscription_products"))
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
        Schema::table('sw_gym_member_subscription_options', function (Blueprint $table) {
            $table->dropForeign('fk_msopt_option_id_restrict');
            $table->foreign('option_id')
                ->references('id')
                ->on('sw_gym_subscription_options')
                ->onDelete('cascade');
            $table->renameColumn('price_snapshot', 'price');
        });

        Schema::table('sw_gym_subscription_products', function (Blueprint $table) {
            $table->dropUnique('uq_sub_product');
        });
    }
};
