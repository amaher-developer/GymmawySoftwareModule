<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeWeekToGymSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->jsonb('time_week')->nullable();
        });
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            $table->jsonb('time_week')->nullable();
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
            $table->dropColumn('time_week');
        });
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            $table->dropColumn('time_week');
        });

    }
}
