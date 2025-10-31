<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymActivitySubscriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_activity_subscription', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('activity_id')->index();
            $table->foreign('activity_id')->references('id')
                ->on('sw_gym_activities')
                ->onDelete('cascade');
            $table->unsignedInteger('subscription_id')->index();
            $table->foreign('subscription_id')->references('id')
                ->on('sw_gym_subscriptions')
                ->onDelete('cascade');
            $table->integer('training_times')->nullable()->default(0);
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
        Schema::dropIfExists('sw_gym_activity_subscription');
    }
}
