<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserIdInSwGymUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_user_notifications', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable()->change();
        });
        Schema::table('sw_gym_user_logs', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_user_notifications', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable(false)->change();
        });
        Schema::table('sw_gym_user_logs', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable(false)->change();
        });
    }
}
