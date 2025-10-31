<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMemberNameGymMoneyboxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_money_boxes` ADD `member_name` VARCHAR(191) NULL DEFAULT NULL AFTER `member_id`;");
        });
        // ALTER TABLE `sw_gym_money_boxes` ADD `member_name` VARCHAR(191) NULL DEFAULT NULL AFTER `member_id`;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            $table->dropColumn('member_name');
        });
    }
}
