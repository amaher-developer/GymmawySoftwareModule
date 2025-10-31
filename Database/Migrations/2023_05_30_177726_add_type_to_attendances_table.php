<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_member_attendees', function (Blueprint $table) {
            $table->tinyInteger('type')->default(0)->nullable()->after('user_id');
            $table->integer('pt_subscription_id')->nullable()->after('user_id');
            $table->integer('subscription_id')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_member_attendees', function (Blueprint $table) {
            $table->dropColumn('subscription_id');
            $table->dropColumn('pt_subscription_id');
            $table->dropColumn('type');
        });
    }
}
