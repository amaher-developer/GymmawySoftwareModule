<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->boolean('is_meal')->default(false)->nullable()->after('is_system');
            $table->string('meal_type')->nullable()->after('is_meal');
            $table->decimal('calories', 8, 2)->nullable()->after('meal_type');
            $table->decimal('protein', 8, 2)->nullable()->after('calories');
            $table->decimal('carbs', 8, 2)->nullable()->after('protein');
            $table->decimal('fat', 8, 2)->nullable()->after('carbs');
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropColumn(['is_meal', 'meal_type', 'calories', 'protein', 'carbs', 'fat']);
        });
    }
};
