<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceRemainingToSwGymNonMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->float('amount_remaining')->default(0)->nullable();
            $table->float('amount_before_discount')->default(0)->nullable();
            $table->float('discount_value')->default(0)->nullable();
            $table->tinyInteger('discount_type')->default(0)->nullable();
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('is_zk_online')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->dropColumn('amount_remaining');
            $table->dropColumn('amount_before_discount');
            $table->dropColumn('discount_value');
            $table->dropColumn('discount_type');
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('is_zk_online');
        });
    }
}
