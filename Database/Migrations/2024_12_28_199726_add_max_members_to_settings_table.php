<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaxMembersToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->integer('max_members')->default(0)->nullable();
            $table->integer('max_non_members')->default(0)->nullable();
            $table->integer('max_pt_members')->default(0)->nullable();
            $table->integer('max_users')->default(0)->nullable();
            $table->boolean('active_activity_reservation')->after('active_activity')->default(0)->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('max_members');
            $table->dropColumn('max_non_members');
            $table->dropColumn('max_pt_members');
            $table->dropColumn('max_users');
            $table->dropColumn('active_activity_reservation');
        });
    }
}
