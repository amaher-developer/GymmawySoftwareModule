<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImageToStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_store_products` ADD `image` VARCHAR(191) NULL DEFAULT NULL AFTER `code`;");
        });
        Schema::table('sw_gym_pt_subscriptions', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_pt_subscriptions` ADD `image` VARCHAR(191) NULL DEFAULT NULL AFTER `name_en`;");
        });
        Schema::table('sw_gym_pt_trainers', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_pt_trainers` ADD `image` VARCHAR(191) NULL DEFAULT NULL AFTER `price`;");
        });
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_subscriptions` ADD `image` VARCHAR(191) NULL DEFAULT NULL AFTER `price`;");
        });

        // ALTER TABLE `sw_gym_store_products` ADD `image` VARCHAR(191) NULL DEFAULT NULL AFTER `code`;
        // ALTER TABLE `sw_gym_pt_subscriptions` ADD `image` VARCHAR(191) NULL DEFAULT NULL AFTER `name_en`;
        // ALTER TABLE `sw_gym_pt_trainers` ADD `image` VARCHAR(191) NULL DEFAULT NULL AFTER `price`;

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropColumn('image');
        });
        Schema::table('sw_gym_pt_subscriptions', function (Blueprint $table) {
            $table->dropColumn('image');
        });
        Schema::table('sw_gym_pt_trainers', function (Blueprint $table) {
            $table->dropColumn('image');
        });
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
}
