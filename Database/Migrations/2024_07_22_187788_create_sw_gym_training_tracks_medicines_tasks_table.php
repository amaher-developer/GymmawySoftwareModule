<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymTrainingTracksMedicinesTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_training_medicines', function (Blueprint $table) {
        $table->increments('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->string('name_ar');
            $table->string('name_en');
            // default doses
            $table->string('dose')->nullable();
            $table->boolean('status')->default(0)->nullable();

            $table->softDeletes();
        $table->timestamps();
    });
        Schema::create('sw_gym_training_tasks', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->unsignedInteger('plan_id')->nullable()->index();
            $table->foreign('plan_id')->references('id')
                ->on('sw_gym_training_plans')
                ->onDelete('cascade');
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('youtube_link')->nullable();
            $table->integer('t_group')->nullable();
            $table->integer('t_repeats')->nullable();
            $table->string('t_rest')->nullable();
            $table->string('d_calories')->nullable();
            $table->string('d_protein')->nullable();
            $table->string('d_carb')->nullable();
            $table->string('d_fats')->nullable();
            $table->text('details')->nullable();
            $table->boolean('status')->default(0)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
//        Schema::create('sw_gym_training_plan_member', function (Blueprint $table) {
//            $table->increments('id');
//
//            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
//            $table->foreign('branch_setting_id')->references('id')
//                ->on('settings')
//                ->onDelete('cascade');
//
//            $table->unsignedInteger('member_id')->nullable()->index();
//            $table->foreign('member_id')->references('id')
//                ->on('sw_gym_members')
//                ->onDelete('cascade');
//            $table->unsignedInteger('plan_id')->nullable()->index();
//            $table->foreign('plan_id')->references('id')
//                ->on('sw_gym_training_plans')
//                ->onDelete('cascade');
//
//            $table->boolean('status')->default(0)->nullable();
//            $table->softDeletes();
//            $table->timestamps();
//        });
        Schema::create('sw_gym_training_member_logs', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->unsignedInteger('member_id')->nullable()->index();
            $table->foreign('member_id')->references('id')
                ->on('sw_gym_members')
                ->onDelete('cascade');

            // relation with medicens, tasks, tracks, plans
            // or show another way to relation by them one of them

            $table->unsignedInteger("training_id")->nullable();
            $table->string("training_type")->nullable();
            $table->index(["training_id", "training_type"]);

//            $table->unsignedInteger('task_id')->nullable()->index();
//            $table->foreign('task_id')->references('id')
//                ->on('sw_gym_training_tasks')
//                ->onDelete('cascade');
//
//            $table->unsignedInteger('medicine_id')->nullable()->index();
//            $table->foreign('medicine_id')->references('id')
//                ->on('sw_gym_training_medicines')
//                ->onDelete('cascade');
//
//            $table->unsignedInteger('track_id')->nullable()->index();
//            $table->foreign('track_id')->references('id')
//                ->on('sw_gym_training_tracks')
//                ->onDelete('cascade');
//
//            $table->unsignedInteger('plan_id')->nullable()->index();
//            $table->foreign('plan_id')->references('id')
//                ->on('sw_gym_training_plans')
//                ->onDelete('cascade');




            $table->mediumInteger('type')->default(0)->nullable();
            $table->boolean('status')->default(0)->nullable();

            $table->text('notes')->nullable();
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

        Schema::dropIfExists('sw_gym_training_medicines');
        Schema::dropIfExists('sw_gym_training_tasks');
//        Schema::dropIfExists('sw_gym_training_plan_member');
        Schema::dropIfExists('sw_gym_training_member_logs');

    }
}
