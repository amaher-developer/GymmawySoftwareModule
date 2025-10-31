<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStoreBalanceToSwGymMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->float('store_balance')->default(0)->nullable();
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('store_postpaid')->default(0)->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->dropColumn('store_balance');
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('store_postpaid');
        });
    }
}
