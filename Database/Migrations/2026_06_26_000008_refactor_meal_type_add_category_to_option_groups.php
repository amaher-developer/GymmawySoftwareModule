<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refactor: remove meal_type (replaced by product categories) and
 * add optional category_id to subscription option groups so each group
 * can be data-driven by a product category.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Remove meal_type — product categories are the single classification mechanism
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_store_products', 'meal_type')) {
                $table->dropColumn('meal_type');
            }
        });

        // Link option groups to product categories (optional — null means free-form choices)
        Schema::table('sw_gym_subscription_option_groups', function (Blueprint $table) {
            $table->unsignedInteger('category_id')
                  ->nullable()
                  ->after('name_en')
                  ->comment('Optional: restrict choices to products from this category');

            $table->foreign('category_id')
                  ->references('id')
                  ->on('sw_gym_store_categories')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_subscription_option_groups', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->string('meal_type')->nullable()->after('is_meal');
        });
    }
};
