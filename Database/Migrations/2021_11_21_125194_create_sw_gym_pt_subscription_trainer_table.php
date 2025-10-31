<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymPTSubscriptionTrainerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_pt_subscription_trainer', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('pt_subscription_id')->index();
            $table->foreign('pt_subscription_id')->references('id')
                ->on('sw_gym_pt_subscriptions')
                ->onDelete('cascade');

            $table->unsignedInteger('pt_trainer_id')->index();
            $table->foreign('pt_trainer_id')->references('id')
                ->on('sw_gym_pt_trainers')
                ->onDelete('cascade');

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
        Schema::dropIfExists('sw_gym_pt_subscription_trainer');
    }
}
