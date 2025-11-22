<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymReservationUsageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_reservation_usage', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->unsignedInteger('reservation_id')->index()->nullable();
            $table->foreign('reservation_id')->references('id')
                ->on('sw_gym_reservations')
                ->onDelete('cascade');

            $table->string('client_type', 20)->nullable()->comment('member, non_member');
            
            $table->unsignedInteger('member_id')->nullable()->index();
            $table->foreign('member_id')->references('id')
                ->on('sw_gym_members')
                ->onDelete('cascade');

            $table->unsignedInteger('non_member_id')->nullable()->index();
            $table->foreign('non_member_id')->references('id')
                ->on('sw_gym_non_members')
                ->onDelete('cascade');

            $table->unsignedInteger('activity_id')->nullable()->index();
            $table->foreign('activity_id')->references('id')
                ->on('sw_gym_activities')
                ->onDelete('cascade');

            $table->unsignedInteger('staff_id')->nullable()->index();
            $table->foreign('staff_id')->references('id')
                ->on('sw_gym_users')
                ->onDelete('set null');

            $table->timestamp('used_at')->nullable();
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
        Schema::dropIfExists('sw_gym_reservation_usage');
    }
}

