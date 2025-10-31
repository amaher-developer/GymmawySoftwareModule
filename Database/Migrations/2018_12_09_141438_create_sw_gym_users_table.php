<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('address')->nullable();
            $table->double('salary')->nullable();
            $table->string('start_time_work')->nullable();
            $table->string('end_time_work')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_super_user')->default(false)->index();
            $table->jsonb('permissions')->nullable();
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
        Schema::dropIfExists('sw_gym_users');
    }
}
