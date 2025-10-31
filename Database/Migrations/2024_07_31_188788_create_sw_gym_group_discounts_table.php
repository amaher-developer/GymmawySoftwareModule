<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymGroupDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_group_discounts', function (Blueprint $table) {
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

            $table->boolean('is_member')->default(false)->nullable();
            $table->boolean('is_pt_member')->default(false)->nullable();
            $table->boolean('is_store')->default(false)->nullable();
            $table->boolean('is_non_member')->default(false)->nullable();
            $table->boolean('is_training_member')->default(false)->nullable();
            $table->tinyInteger('type')->default(0)->nullable();
            $table->float('amount')->default(0)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            $table->unsignedInteger('group_discount_id')->nullable();
            $table->foreign('group_discount_id')->references('id')
                ->on('sw_gym_group_discounts')
                ->onDelete('cascade');
        });

        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->unsignedInteger('group_discount_id')->index()->nullable();
            $table->foreign('group_discount_id')->references('id')
                ->on('sw_gym_group_discounts')
                ->onDelete('cascade');
        });

        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->unsignedInteger('group_discount_id')->index()->nullable();
            $table->foreign('group_discount_id')->references('id')
                ->on('sw_gym_group_discounts')
                ->onDelete('cascade');
        });

        Schema::table('sw_gym_store_orders', function (Blueprint $table) {
            $table->unsignedInteger('group_discount_id')->index()->nullable();
            $table->foreign('group_discount_id')->references('id')
                ->on('sw_gym_group_discounts')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sw_gym_group_discounts');

        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            $table->dropColumn('group_discount_id');
        });
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->dropColumn('group_discount_id');
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->dropColumn('group_discount_id');
        });
        Schema::table('sw_gym_store_orders', function (Blueprint $table) {
            $table->dropColumn('group_discount_id');
        });
    }
}
