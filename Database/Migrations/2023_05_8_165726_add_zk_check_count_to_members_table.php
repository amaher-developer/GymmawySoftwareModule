<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddZkCheckCountToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->integer('fp_check_count')->default(0)->nullable()->after('fp_check');
        });
        Schema::table('sw_gym_users', function (Blueprint $table) {
            $table->integer('fp_check_count')->default(0)->nullable()->after('fp_check');
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
            $table->dropColumn('fp_check_count');
        });
        Schema::table('sw_gym_users', function (Blueprint $table) {
            $table->dropColumn('fp_check_count');
        });

    }
}
