<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('sound_active')->nullable();
            $table->string('sound_expired')->nullable();
            $table->string('price');
            $table->integer('period');
            $table->integer('workouts');
            $table->integer('freeze_limit')->default(0);
            $table->integer('number_times_freeze')->default(0);
            $table->boolean('is_expire_changeable');
//            $table->string('name_en')->nullable();
//            $table->string('duration');
//            $table->integer('price');
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
        Schema::dropIfExists('subscriptions');
    }
}
