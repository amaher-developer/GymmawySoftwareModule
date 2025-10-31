<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatGymMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_member_subscription` ADD `vat` FLOAT NULL DEFAULT '0' AFTER `amount_paid`;");
        });

        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_pt_members` ADD `vat` FLOAT NULL DEFAULT '0' AFTER `amount_paid`;");
            DB::statement("ALTER TABLE `sw_gym_pt_members` ADD `payment_type` TINYINT NOT NULL DEFAULT '0' AFTER `trainer_amount_status`;");
//            DB::statement("ALTER TABLE `sw_gym_pt_members` ADD `discount_value` INT NOT NULL DEFAULT '0' AFTER `trainer_amount_status`;");
        });

//        ALTER TABLE `sw_gym_member_subscription` ADD `vat` FLOAT NULL DEFAULT '0' AFTER `amount_paid`;
//        ALTER TABLE `sw_gym_pt_members` ADD `vat` FLOAT NULL DEFAULT '0' AFTER `amount_paid`;
//        ALTER TABLE `sw_gym_pt_members` ADD `payment_type` TINYINT NOT NULL DEFAULT '0' AFTER `trainer_amount_status`;

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            $table->dropColumn('vat');
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->dropColumn('vat');
            $table->dropColumn('payment_type');
//            $table->dropColumn('discount_value');
        });

    }
}
