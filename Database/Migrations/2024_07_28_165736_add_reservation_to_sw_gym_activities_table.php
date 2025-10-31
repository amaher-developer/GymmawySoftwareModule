<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReservationToSwGymActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->tinyInteger('reservation_limit')->default(0)->nullable();
            $table->string('reservation_duration')->nullable();
            $table->jsonb('reservation_details')->nullable();
            $table->integer('reservation_period')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->dropColumn('reservation_limit');
            $table->dropColumn('reservation_period');
            $table->dropColumn('reservation_details');
            $table->dropColumn('reservation_duration');
        });
    }
}
