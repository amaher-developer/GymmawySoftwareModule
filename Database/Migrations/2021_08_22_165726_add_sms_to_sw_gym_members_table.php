<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSmsToSwGymMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {

            $table->boolean('sms_expire_member')->default(false)->after('dob');
            $table->boolean('sms_before_expire_member')->default(false)->after('dob');
            $table->boolean('sms_renew_member')->default(false)->after('dob');
            $table->boolean('sms_new_member')->default(false)->after('dob');

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

            $table->dropColumn('sms_new_member');
            $table->dropColumn('sms_renew_member');
            $table->dropColumn('sms_before_expire_member');
            $table->dropColumn('sms_expire_member');

        });
    }
}
