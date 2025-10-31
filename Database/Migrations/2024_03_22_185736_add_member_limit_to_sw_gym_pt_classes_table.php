<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMemberLimitToSwGymPtClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->integer('member_limit')->nullable()->default(0);
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->date('expire_date')->nullable()->after('joining_date');
        });
        Schema::table('sw_gym_pt_member_attendees', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->dropColumn('member_limit');
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->dropColumn('expire_date');
        });
        Schema::table('sw_gym_pt_member_attendees', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
