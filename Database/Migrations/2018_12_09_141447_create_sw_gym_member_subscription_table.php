<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymMemberSubscriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_member_subscription', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('member_id')->index();
            $table->foreign('member_id')->references('id')
                ->on('sw_gym_members')
                ->onDelete('cascade');
            $table->unsignedInteger('subscription_id')->index();
            $table->foreign('subscription_id')->references('id')
                ->on('sw_gym_subscriptions')
                ->onDelete('cascade');
            $table->integer('workouts')->default(0);
            $table->integer('visits')->default(0);
            $table->integer('amount_remaining')->default(0);
            $table->timestamp('joining_date');
            $table->timestamp('expire_date')->nullable();
            $table->boolean('status')->default(0);
            $table->integer('freeze_limit')->default(0);
            $table->date('start_freeze_date')->nullable();
            $table->date('end_freeze_date')->nullable();
            $table->integer('number_times_freeze')->default(0);
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
        Schema::dropIfExists('sw_gym_member_subscription');
    }
}
