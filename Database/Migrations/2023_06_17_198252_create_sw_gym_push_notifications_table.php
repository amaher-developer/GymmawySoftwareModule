<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymPushNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_push_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('member_id')->nullable();
            $table->string('notification_id')->index();
            $table->text('stats')->nullable();
            $table->string('title');
            $table->jsonb('body')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sw_gym_push_notifications');
    }
}
