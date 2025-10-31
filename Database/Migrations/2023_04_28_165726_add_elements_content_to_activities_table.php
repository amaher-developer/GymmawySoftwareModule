<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddElementsContentToActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_activities` ADD `content_ar` VARCHAR(255) NULL DEFAULT NULL AFTER `name_ar`;");
            DB::statement("ALTER TABLE `sw_gym_activities` ADD `content_en` VARCHAR(255) NULL DEFAULT NULL AFTER `name_ar`;");
        });
        // ALTER TABLE `sw_gym_activities` ADD `content_ar` VARCHAR(255) NULL DEFAULT NULL AFTER `name_ar`;
        // ALTER TABLE `sw_gym_activities` ADD `content_en` VARCHAR(255) NULL DEFAULT NULL AFTER `name_ar`;

        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_pt_classes` ADD `content_ar` VARCHAR(255) NULL DEFAULT NULL AFTER `reservation_details`;");
            DB::statement("ALTER TABLE `sw_gym_pt_classes` ADD `content_en` VARCHAR(255) NULL DEFAULT NULL AFTER `reservation_details`;");
        });
        // ALTER TABLE `sw_gym_pt_classes` ADD `content_ar` VARCHAR(255) NULL DEFAULT NULL AFTER `reservation_details`;
        // ALTER TABLE `sw_gym_pt_classes` ADD `content_en` VARCHAR(255) NULL DEFAULT NULL AFTER `reservation_details`;

        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_store_products` ADD `content_ar` VARCHAR(255) NULL DEFAULT NULL AFTER `name_en`;");
            DB::statement("ALTER TABLE `sw_gym_store_products` ADD `content_en` VARCHAR(255) NULL DEFAULT NULL AFTER `name_en`;");
        });
        // ALTER TABLE `sw_gym_store_products` ADD `content_ar` VARCHAR(255) NULL DEFAULT NULL AFTER `name_en`;
        // ALTER TABLE `sw_gym_store_products` ADD `content_en` VARCHAR(255) NULL DEFAULT NULL AFTER `name_en`;

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->dropColumn('content_ar');
            $table->dropColumn('content_en');
        });
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->dropColumn('content_ar');
            $table->dropColumn('content_en');
        });
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropColumn('content_ar');
            $table->dropColumn('content_en');
        });
    }
}
