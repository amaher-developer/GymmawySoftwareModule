<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_store_products', 'is_meal')) {
                $table->boolean('is_meal')->default(false)->nullable()->after('is_system');
            }
            if (!Schema::hasColumn('sw_gym_store_products', 'meal_type')) {
                $table->string('meal_type')->nullable()->after('is_meal');
            }
            if (!Schema::hasColumn('sw_gym_store_products', 'calories')) {
                $table->decimal('calories', 8, 2)->nullable()->after('meal_type');
            }
            if (!Schema::hasColumn('sw_gym_store_products', 'protein')) {
                $table->decimal('protein', 8, 2)->nullable()->after('calories');
            }
            if (!Schema::hasColumn('sw_gym_store_products', 'carbs')) {
                $table->decimal('carbs', 8, 2)->nullable()->after('protein');
            }
            if (!Schema::hasColumn('sw_gym_store_products', 'fat')) {
                $table->decimal('fat', 8, 2)->nullable()->after('carbs');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropColumn(['is_meal', 'meal_type', 'calories', 'protein', 'carbs', 'fat']);
        });
    }
};
