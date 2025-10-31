<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeToGymPtSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->string('start_time_day')->nullable();
            $table->string('end_time_day')->nullable();
            $table->integer('workouts_per_day')->nullable()->default(0);
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->string('start_time_day')->nullable();
            $table->string('end_time_day')->nullable();
            $table->integer('workouts_per_day')->nullable()->default(0);
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
            $table->dropColumn('start_time_day');
            $table->dropColumn('end_time_day');
            $table->dropColumn('workouts_per_day');
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->dropColumn('start_time_day');
            $table->dropColumn('end_time_day');
            $table->dropColumn('workouts_per_day');
        });

    }
}
