<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToTrainingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_training_members', function (Blueprint $table) {

            $table->dropColumn('training_plan_details');
            $table->dropColumn('diet_plan_details');
            $table->dropColumn('training_plan_id');
            $table->dropColumn('diet_plan_id');

            $table->unsignedInteger('plan_id')->index();
            $table->foreign('plan_id')->references('id')
                ->on('sw_gym_training_plans')
                ->onDelete('cascade');

            $table->tinyInteger('type');
            $table->string('title');
            $table->text('plan_details');
            $table->string('member_comment')->nullable();
            $table->tinyInteger('status')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_training_members', function (Blueprint $table) {
            $table->dropColumn('plan_id');
            $table->dropColumn('plan_details');
            $table->dropColumn('member_comment');
            $table->dropColumn('status');
            $table->dropColumn('type');
            $table->dropColumn('title');


            $table->unsignedInteger('training_plan_id')->index();
            $table->foreign('training_plan_id')->references('id')
                ->on('sw_gym_training_plans')
                ->onDelete('cascade');
            $table->unsignedInteger('diet_plan_id')->index();
            $table->foreign('diet_plan_id')->references('id')
                ->on('sw_gym_training_plans')
                ->onDelete('cascade');

            $table->text('training_plan_details');
            $table->text('diet_plan_details');
        });
    }
}
