<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPtTrainerAmountStatusToSwGymPtMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->tinyInteger('trainer_amount_status')->default(0)->nullable()->after('trainer_percentage');
            $table->integer('trainer_amount_paid')->default(0)->nullable()->after('trainer_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->dropColumn('trainer_amount_status');
            $table->dropColumn('trainer_amount_paid');
        });
    }
}
