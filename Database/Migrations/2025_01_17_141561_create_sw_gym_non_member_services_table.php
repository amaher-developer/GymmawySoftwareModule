<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymNonMemberServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_non_member_services', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->unsignedInteger('non_member_id')->index();
            $table->foreign('non_member_id')->references('id')
                ->on('sw_gym_non_members')
                ->onDelete('cascade');

            $table->unsignedInteger('subscription_id')->nullable()->index();
            $table->foreign('subscription_id')->references('id')
                ->on('sw_gym_reservations')
                ->onDelete('cascade');

            $table->unsignedInteger('activity_id')->nullable()->index();
            $table->foreign('activity_id')->references('id')
                ->on('sw_gym_activities')
                ->onDelete('set null');

            $table->unsignedInteger('reservation_id')->nullable()->index();
            $table->foreign('reservation_id')->references('id')
                ->on('sw_gym_reservations')
                ->onDelete('set null');

            $table->string('service_type', 50)->nullable()->comment('reservation, activity, etc.');
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->timestamp('service_date')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->string('status', 20)->default('pending')->comment('pending, used, cancelled');
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
        Schema::dropIfExists('sw_gym_non_member_services');
    }
}

