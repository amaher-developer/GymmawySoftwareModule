<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymGroupStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_store_groups', function (Blueprint $table) {
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

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->unsignedInteger('group_id')->index()->nullable()->after('id');
            $table->foreign('group_id')->references('id')
                ->on('sw_gym_store_groups')
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
        Schema::dropIfExists('sw_gym_store_groups');

        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });
    }
}
