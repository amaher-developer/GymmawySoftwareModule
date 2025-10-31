<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserIdInGymMoneyBoxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sw_gym_money_boxes` CHANGE `user_id` `user_id` INT(10) UNSIGNED NULL DEFAULT NULL;');
        });
        // ALTER TABLE `sw_gym_member_attendees` CHANGE `user_id` `user_id` INT(10) UNSIGNED NULL DEFAULT NULL;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sw_gym_money_boxes` CHANGE `user_id` `user_id` INT(10) UNSIGNED NOT NULL;');
        });
        // ALTER TABLE `sw_gym_member_attendees` CHANGE `user_id` `user_id` INT(10) UNSIGNED NOT NULL;
    }
}
