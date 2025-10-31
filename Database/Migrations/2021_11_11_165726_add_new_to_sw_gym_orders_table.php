<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewToSwGymOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_orders', function (Blueprint $table) {
            $table->dropColumn('member_id');
            $table->dropColumn('subscription_id');
            $table->dropColumn('date_from');
            $table->dropColumn('date_to');

            $table->unsignedInteger('user_id')->index()->after('id');
            $table->foreign('user_id')->references('id')
                ->on('sw_gym_users')
                ->onDelete('cascade');

            $table->string('price_before')->nullable()->after('price');
            $table->tinyInteger('type')->index()->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_orders', function (Blueprint $table) {

            $table->unsignedInteger('member_id')->index();
            $table->foreign('member_id')->references('id')
                ->on('sw_gym_members')
                ->onDelete('cascade');

            $table->unsignedInteger('subscription_id')->index();
            $table->foreign('subscription_id')->references('id')
                ->on('sw_gym_subscriptions')
                ->onDelete('cascade');

            $table->date('date_from');
            $table->date('date_to');


            $table->dropColumn('price_before');
            $table->dropColumn('type');
            $table->dropColumn('user_id');

        });
    }
}
