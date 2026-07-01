<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // source_type on groups: 'product' (default) or 'activity'
        Schema::table('sw_gym_subscription_option_groups', function (Blueprint $table) {
            $table->string('source_type', 20)->default('product')->after('category_id');
        });

        // activity_id on options; product_id becomes nullable so either can be set
        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->unsignedBigInteger('activity_id')->nullable()->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_subscription_option_groups', function (Blueprint $table) {
            $table->dropColumn('source_type');
        });

        Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
            $table->dropColumn('activity_id');
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });
    }
};
