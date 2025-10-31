<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymSaleChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_sale_channels', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->unsignedInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')
                ->on('sw_gym_users')
                ->onDelete('cascade');

            $table->string('name_ar');
            $table->string('name_en');
            $table->string('image')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

//        Schema::table('sw_gym_non_members', function (Blueprint $table) {
//            $table->unsignedInteger('sale_channel_id')->index()->default(1)->nullable();
//            $table->foreign('sale_channel_id')->references('id')
//                ->on('sw_gym_sale_channels')
//                ->onDelete('cascade');
//        });
        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->unsignedInteger('sale_channel_id')->index()->nullable();
            $table->foreign('sale_channel_id')->references('id')
                ->on('sw_gym_sale_channels')
                ->onDelete('cascade');

            $table->unsignedInteger('sale_user_id')->index()->nullable();
            $table->foreign('sale_user_id')->references('id')
                ->on('sw_gym_users')
                ->onDelete('cascade');
        });

//        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
//            $table->unsignedInteger('sale_channel_id')->index()->default(1)->nullable();
//            $table->foreign('sale_channel_id')->references('id')
//                ->on('sw_gym_sale_channels')
//                ->onDelete('cascade');
//        });
//        Schema::table('sw_gym_training_members', function (Blueprint $table) {
//            $table->unsignedInteger('sale_channel_id')->index()->default(1)->nullable();
//            $table->foreign('sale_channel_id')->references('id')
//                ->on('sw_gym_sale_channels')
//                ->onDelete('cascade');
//        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sw_gym_sale_channels');

        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->dropColumn('sale_channel_id');
            $table->dropColumn('sale_user_id');
        });

    }
}
