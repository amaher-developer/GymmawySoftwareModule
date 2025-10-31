<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewToSwGymMemberSubscriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            $table->integer('amount_before_discount')->default(0);
            $table->integer('discount_value')->default(0);
            $table->tinyInteger('discount_type')->default(1)->nullable();
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
            $table->dropColumn('amount_before_discount');
            $table->dropColumn('discount_value');
            $table->dropColumn('discount_type');
        });
    }
}
