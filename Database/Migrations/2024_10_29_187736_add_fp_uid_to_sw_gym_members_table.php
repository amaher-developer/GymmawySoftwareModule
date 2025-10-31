<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFpUidToSwGymMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->string('fp_uid')->nullable()->after('fp_id');
        });
        Schema::table('sw_gym_member_attendees', function (Blueprint $table) {
            $table->string('fp_att_id')->default(0)->nullable();
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
            $table->dropColumn('fp_uid');
        });
        Schema::table('sw_gym_member_attendees', function (Blueprint $table) {
            $table->dropColumn('fp_att_id');
        });
    }
}
