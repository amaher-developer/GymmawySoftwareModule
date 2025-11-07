<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_store_products', 'sku')) {
                $table->string('sku')->nullable()->after('name_en');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_store_products', 'sku')) {
                $table->dropColumn('sku');
            }
        });
    }
};

