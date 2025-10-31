<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_categories', function (Blueprint $table) {
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

            $table->boolean('is_subscription')->default(false)->nullable();
            $table->boolean('is_pt_subscription')->default(false)->nullable();
            $table->boolean('is_store')->default(false)->nullable();
            $table->boolean('is_activity')->default(false)->nullable();
            $table->boolean('is_training')->default(false)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')
                ->on('sw_gym_categories')
                ->onDelete('cascade');
        });

        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->index()->nullable();
            $table->foreign('category_id')->references('id')
                ->on('sw_gym_categories')
                ->onDelete('cascade');
        });

        Schema::table('sw_gym_pt_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->index()->nullable();
            $table->foreign('category_id')->references('id')
                ->on('sw_gym_categories')
                ->onDelete('cascade');
        });

        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->index()->nullable();
            $table->foreign('category_id')->references('id')
                ->on('sw_gym_categories')
                ->onDelete('cascade');
        });

        Schema::table('sw_gym_training_plans', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->index()->nullable();
            $table->foreign('category_id')->references('id')
                ->on('sw_gym_categories')
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
        Schema::dropIfExists('sw_gym_categories');

        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
        Schema::table('sw_gym_pt_subscriptions', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
        Schema::table('sw_gym_training_plans', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
    }
}
