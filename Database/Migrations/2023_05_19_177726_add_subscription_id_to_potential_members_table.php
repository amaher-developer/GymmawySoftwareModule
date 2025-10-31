<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscriptionIdToPotentialMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_potential_members', function (Blueprint $table) {

            $table->unsignedInteger('subscription_id')->index()->nullable();
            $table->foreign('subscription_id')->references('id')
                ->on('sw_gym_subscriptions')
                ->onDelete('cascade');

            $table->unsignedInteger('pt_subscription_id')->index()->nullable();
            $table->foreign('pt_subscription_id')->references('id')
                ->on('sw_gym_pt_subscriptions')
                ->onDelete('cascade');

            $table->unsignedInteger('pt_class_id')->index()->nullable();
            $table->foreign('pt_class_id')->references('id')
                ->on('sw_gym_pt_classes')
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
        Schema::table('sw_gym_potential_members', function (Blueprint $table) {
            $table->dropColumn('subscription_id');
            $table->dropColumn('pt_subscription_id');
            $table->dropColumn('pt_class_id');
        });
    }
}
