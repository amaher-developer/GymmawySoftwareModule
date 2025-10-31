<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDeviceTypeInGymPushTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_push_tokens', function (Blueprint $table) {
            $table->string('device_type', 50)->change();
            $table->unsignedInteger('member_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_push_tokens', function (Blueprint $table) {
            $table->integer('device_type')->change();
            $table->unsignedInteger('member_id')->nullable(false)->change();
        });
    }
}
