<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReservationDetailsToSwGymPtClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->jsonb('reservation_details')->nullable()->after('price');
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

            $table->dropColumn('reservation_details');

        });
    }
}
