<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddElementVatPtSubscriptionIdToMoneyBoxsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_money_boxes` ADD `member_pt_subscription_id` INT NULL DEFAULT NULL AFTER `member_subscription_id`;");
        });
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_member_subscription` ADD `vat_percentage` INT(2) NULL DEFAULT '0' AFTER `vat`;");
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_pt_members` ADD `vat_percentage` INT(2) NULL DEFAULT '0' AFTER `vat`;");
        });
        // ALTER TABLE `sw_gym_money_boxes` ADD `member_pt_subscription_id` INT NULL DEFAULT NULL AFTER `member_subscription_id`;
        // ALTER TABLE `sw_gym_member_subscription` ADD `vat_percentage` INT(2) NULL DEFAULT '0' AFTER `vat`;
        // ALTER TABLE `sw_gym_pt_members` ADD `vat_percentage` INT(2) NULL DEFAULT '0' AFTER `vat`;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            $table->dropColumn('member_pt_subscription_id');
        });
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            $table->dropColumn('vat_percentage');
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->dropColumn('vat_percentage');
        });
    }
}
