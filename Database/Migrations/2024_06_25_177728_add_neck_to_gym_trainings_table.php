<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNeckToGymTrainingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_training_tracks', function (Blueprint $table) {
            $table->float('neck_circumference')->nullable()->after('height');
            $table->float('chest_circumference')->nullable()->after('height');
            $table->float('arm_circumference')->nullable()->after('height');
            $table->float('abdominal_circumference')->nullable()->after('height');
            $table->float('pelvic_circumference')->nullable()->after('height');
            $table->float('thigh_circumference')->nullable()->after('height');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_training_tracks', function (Blueprint $table) {
            $table->dropColumn('neck_circumference');
            $table->dropColumn('chest_circumference');
            $table->dropColumn('arm_circumference');
            $table->dropColumn('abdominal_circumference');
            $table->dropColumn('pelvic_circumference');
            $table->dropColumn('thigh_circumference');
        });

    }
}
