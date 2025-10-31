<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActivityIdToPotentialMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_potential_members', function (Blueprint $table) {

            $table->unsignedInteger('activity_id')->index()->nullable()->after('pt_class_id');
            $table->foreign('activity_id')->references('id')
                ->on('sw_gym_activities')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_potential_members', function (Blueprint $table) {
            $table->dropColumn('activity_id');
        });
    }
}
