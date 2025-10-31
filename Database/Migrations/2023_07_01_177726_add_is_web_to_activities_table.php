<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsWebToActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->boolean('is_web')->default(0)->nullable();
            $table->boolean('is_mobile')->default(0)->nullable();
        });
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->boolean('is_web')->default(0)->nullable();
            $table->boolean('is_mobile')->default(0)->nullable();
        });
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->boolean('is_web')->default(0)->nullable();
            $table->boolean('is_mobile')->default(0)->nullable();
        });
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->boolean('is_web')->default(0)->nullable();
            $table->boolean('is_mobile')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->dropColumn('is_web');
            $table->dropColumn('is_mobile');
        });
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropColumn('is_web');
            $table->dropColumn('is_mobile');
        });
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->dropColumn('is_web');
            $table->dropColumn('is_mobile');
        });
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->dropColumn('is_web');
            $table->dropColumn('is_mobile');
        });
    }
}
