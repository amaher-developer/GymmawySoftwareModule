<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAmountElementsTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sw_gym_member_subscription` CHANGE `amount_remaining` `amount_remaining` FLOAT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_member_subscription` CHANGE `amount_paid` `amount_paid` FLOAT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_member_subscription` CHANGE `amount_before_discount` `amount_before_discount` FLOAT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_member_subscription` CHANGE `discount_value` `discount_value` FLOAT NOT NULL DEFAULT "0";');
        });
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sw_gym_money_boxes` CHANGE `vat` `vat` FLOAT NULL DEFAULT "0";');
        });

        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sw_gym_pt_members` CHANGE `amount_paid` `amount_paid` FLOAT  NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_pt_members` CHANGE `amount_remaining` `amount_remaining` FLOAT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_pt_members` CHANGE `amount_before_discount` `amount_before_discount` FLOAT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_pt_members` CHANGE `discount_value` `discount_value` FLOAT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_pt_members` CHANGE `trainer_amount_paid` `trainer_amount_paid` FLOAT NULL DEFAULT "0";');
        });
        Schema::table('sw_gym_store_orders', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sw_gym_store_orders` CHANGE `amount_paid` `amount_paid` FLOAT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_store_orders` CHANGE `vat` `vat` FLOAT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_store_orders` CHANGE `amount_remaining` `amount_remaining` FLOAT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_store_orders` CHANGE `amount_before_discount` `amount_before_discount` FLOAT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_store_orders` CHANGE `discount_value` `discount_value` FLOAT NOT NULL DEFAULT "0";');
        });


//        ALTER TABLE `sw_gym_member_subscription` CHANGE `amount_remaining` `amount_remaining` FLOAT NOT NULL DEFAULT "0";
//        ALTER TABLE `sw_gym_member_subscription` CHANGE `amount_paid` `amount_paid` FLOAT NULL DEFAULT "0";
//        ALTER TABLE `sw_gym_member_subscription` CHANGE `amount_before_discount` `amount_before_discount` FLOAT NOT NULL DEFAULT "0";
//        ALTER TABLE `sw_gym_member_subscription` CHANGE `discount_value` `discount_value` FLOAT NOT NULL DEFAULT "0";
//
//        ALTER TABLE `sw_gym_money_boxes` CHANGE `vat` `vat` FLOAT NULL DEFAULT "0";
//
//        ALTER TABLE `sw_gym_pt_members` CHANGE `amount_paid` `amount_paid` FLOAT  NULL DEFAULT "0";
//        ALTER TABLE `sw_gym_pt_members` CHANGE `amount_remaining` `amount_remaining` FLOAT NOT NULL DEFAULT "0";
//        ALTER TABLE `sw_gym_pt_members` CHANGE `amount_before_discount` `amount_before_discount` FLOAT NOT NULL DEFAULT "0";
//        ALTER TABLE `sw_gym_pt_members` CHANGE `discount_value` `discount_value` FLOAT NOT NULL DEFAULT "0";
//        ALTER TABLE `sw_gym_pt_members` CHANGE `trainer_amount_paid` `trainer_amount_paid` FLOAT NULL DEFAULT "0";
//
//
//        ALTER TABLE `sw_gym_store_orders` CHANGE `amount_paid` `amount_paid` FLOAT NULL DEFAULT "0";
//        ALTER TABLE `sw_gym_store_orders` CHANGE `vat` `vat` FLOAT NULL DEFAULT "0";
//        ALTER TABLE `sw_gym_store_orders` CHANGE `amount_remaining` `amount_remaining` FLOAT NOT NULL DEFAULT "0";
//        ALTER TABLE `sw_gym_store_orders` CHANGE `amount_before_discount` `amount_before_discount` FLOAT NOT NULL DEFAULT "0";
//        ALTER TABLE `sw_gym_store_orders` CHANGE `discount_value` `discount_value` FLOAT NOT NULL DEFAULT "0";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sw_gym_member_subscription` CHANGE `amount_remaining` `amount_remaining` INT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_member_subscription` CHANGE `amount_paid` `amount_paid` INT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_member_subscription` CHANGE `amount_before_discount` `amount_before_discount` INT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_member_subscription` CHANGE `discount_value` `discount_value` INT NOT NULL DEFAULT "0";');
        });
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sw_gym_money_boxes` CHANGE `vat` `vat` INT NULL DEFAULT "0";');
        });

        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sw_gym_pt_members` CHANGE `amount_paid` `amount_paid` INT  NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_pt_members` CHANGE `amount_remaining` `amount_remaining` INT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_pt_members` CHANGE `amount_before_discount` `amount_before_discount` INT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_pt_members` CHANGE `discount_value` `discount_value` INT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_pt_members` CHANGE `trainer_amount_paid` `trainer_amount_paid` INT NULL DEFAULT "0";');
        });
        Schema::table('sw_gym_store_orders', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sw_gym_store_orders` CHANGE `amount_paid` `amount_paid` INT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_store_orders` CHANGE `vat` `vat` INT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_store_orders` CHANGE `amount_remaining` `amount_remaining` INT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_store_orders` CHANGE `amount_before_discount` `amount_before_discount` INT NOT NULL DEFAULT "0";');
            DB::statement('ALTER TABLE `sw_gym_store_orders` CHANGE `discount_value` `discount_value` INT NOT NULL DEFAULT "0";');
        });
    }
}
