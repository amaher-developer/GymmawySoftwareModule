<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNonMemberSubscriptionIdToSwGymMoneyBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            $table->unsignedInteger('non_member_subscription_id')->nullable()->index();
            $table->foreign('non_member_subscription_id')->references('id')
                ->on('sw_gym_non_members')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            $table->dropColumn('non_member_subscription_id');
        });
    }
}
