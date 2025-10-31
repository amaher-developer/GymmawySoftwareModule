<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPtPercentageToSwGymPtTrainersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_pt_trainers', function (Blueprint $table) {
            $table->integer('percentage')->default(0)->nullable()->after('price');
            $table->integer('monthly_classes')->nullable()->change();
            $table->string('price')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_pt_trainers', function (Blueprint $table) {
            $table->dropColumn('percentage');
            $table->integer('monthly_classes')->change();
            $table->string('price')->change();
        });
    }
}
