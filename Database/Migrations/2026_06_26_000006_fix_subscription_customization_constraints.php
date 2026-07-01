<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. member_subscription_options: RESTRICT + rename price → price_snapshot ──
        Schema::table('sw_gym_member_subscription_options', function (Blueprint $table) {
            // Drop the cascade FK on option_id
            $table->dropForeign(['option_id']);

            // Re-add as RESTRICT — prevents hard-deleting an option that has history
            $table->foreign('option_id', 'fk_msopt_option_id_restrict')
                ->references('id')
                ->on('sw_gym_subscription_options')
                ->onDelete('restrict');

            // Rename price → price_snapshot
            $table->renameColumn('price', 'price_snapshot');
        });

        // ── 2. subscription_products: UNIQUE(subscription_id, product_id) ──
        Schema::table('sw_gym_subscription_products', function (Blueprint $table) {
            $table->unique(['subscription_id', 'product_id'], 'uq_sub_product');
        });
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
