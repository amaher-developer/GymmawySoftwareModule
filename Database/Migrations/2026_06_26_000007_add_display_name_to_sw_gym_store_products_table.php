<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->string('display_name_ar')->nullable()->after('name_ar');
            $table->string('display_name_en')->nullable()->after('name_en');
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropColumn(['display_name_ar', 'display_name_en']);
        });
    }
};
