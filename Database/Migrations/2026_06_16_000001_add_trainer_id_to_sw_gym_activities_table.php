<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrainerIdToSwGymActivitiesTable extends Migration
{
    public function up()
    {
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('trainer_id')->nullable()->after('branch_setting_id');
        });
    }
    public function down()
    {
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->dropColumn('trainer_id');
        });
    }
}
