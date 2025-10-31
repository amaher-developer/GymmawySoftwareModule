<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymNonMemberTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_non_member_times', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->unsignedInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')
                ->on('sw_gym_users')
                ->onDelete('cascade');

            $table->unsignedInteger('non_member_id')->index()->nullable();
            $table->foreign('non_member_id')->references('id')
                ->on('sw_gym_non_members')
                ->onDelete('cascade');

            $table->unsignedInteger('member_id')->index()->nullable();
            $table->foreign('member_id')->references('id')
                ->on('sw_gym_members')
                ->onDelete('cascade');

            $table->unsignedInteger('activity_id')->index()->nullable();
            $table->foreign('activity_id')->references('id')
                ->on('sw_gym_activities')
                ->onDelete('cascade');

            $table->timestamp('date')->nullable();;
            $table->timestamp('expire_date')->nullable();
            $table->string('time_slot', 20)->nullable();
            $table->timestamp('attended_at')->nullable()->default(null);

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

        Schema::dropIfExists('sw_gym_non_member_times');

    }
}
