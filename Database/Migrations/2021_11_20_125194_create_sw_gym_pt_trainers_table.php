<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymPTTrainersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_pt_trainers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->string('phone')->unique();

            $table->text('work_hours')->nullable();
            $table->integer('monthly_classes');
            $table->string('price');

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
        Schema::dropIfExists('sw_gym_pt_trainers');
    }
}
