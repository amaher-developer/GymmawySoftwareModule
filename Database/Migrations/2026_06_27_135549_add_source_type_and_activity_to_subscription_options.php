<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sw_gym_subscription_option_groups') &&
            !Schema::hasColumn('sw_gym_subscription_option_groups', 'source_type')) {
            Schema::table('sw_gym_subscription_option_groups', function (Blueprint $table) {
                $table->string('source_type', 20)->default('product')->after('category_id');
            });
        }

        if (Schema::hasTable('sw_gym_subscription_options')) {
            Schema::table('sw_gym_subscription_options', function (Blueprint $table) {
                if (Schema::hasColumn('sw_gym_subscription_options', 'product_id')) {
                    $table->unsignedBigInteger('product_id')->nullable()->change();
                }
                if (!Schema::hasColumn('sw_gym_subscription_options', 'activity_id')) {
                    $table->unsignedBigInteger('activity_id')->nullable()->after('product_id');
                }
            });
        }
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
