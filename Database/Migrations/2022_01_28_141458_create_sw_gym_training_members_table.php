<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymTrainingMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_training_members', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('member_id')->nullable()->index();
            $table->foreign('member_id')->references('id')
                ->on('sw_gym_members')
                ->onDelete('cascade');
            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')
                ->on('sw_gym_users')
                ->onDelete('cascade');
            $table->unsignedInteger('training_plan_id')->index();
            $table->foreign('training_plan_id')->references('id')
                ->on('sw_gym_training_plans')
                ->onDelete('cascade');
            $table->unsignedInteger('diet_plan_id')->index();
            $table->foreign('diet_plan_id')->references('id')
                ->on('sw_gym_training_plans')
                ->onDelete('cascade');

            $table->float('weight');
            $table->float('height');
            $table->text('notes')->nullable();
            $table->text('training_plan_details');
            $table->text('diet_plan_details');
            $table->text('diseases')->nullable();

            $table->timestamp('from_date')->nullable();
            $table->timestamp('to_date')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sw_gym_training_members');
    }
}
