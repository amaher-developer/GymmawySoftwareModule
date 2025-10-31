<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameToGymPtClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('id');
            $table->string('name_ar')->nullable()->after('id');
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
            $table->dropColumn('name_ar');
            $table->dropColumn('name_en');
        });

    }
}
