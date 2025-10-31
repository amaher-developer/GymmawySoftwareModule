<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeJoiningDateToSwGymMemberSubscriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sw_gym_member_subscription` CHANGE `joining_date` `joining_date` TIMESTAMP NULL DEFAULT NULL;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sw_gym_member_subscription` CHANGE `joining_date` `joining_date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;');
        });
    }
}
