<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_subscription_options', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('activity_id');
            }
            if (!Schema::hasColumn('sw_gym_subscription_options', 'name_en')) {
                $table->string('name_en')->nullable()->after('name_ar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en']);
        });
    }
};
