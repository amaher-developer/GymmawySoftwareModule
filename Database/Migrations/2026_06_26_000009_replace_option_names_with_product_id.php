<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replace free-text option names with a direct FK to sw_gym_store_products.
 * Products become the single source of truth for names, images, and nutrition.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            $table->unsignedInteger('product_id')
                  ->nullable()
                  ->after('option_group_id');

            $table->foreign('product_id')
                  ->references('id')
                  ->on('sw_gym_store_products')
                  ->nullOnDelete();
        });

        // Drop free-text name columns — product provides the display name
        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en']);
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            $table->string('name_ar')->default('')->after('option_group_id');
            $table->string('name_en')->default('')->after('name_ar');
        });

        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
