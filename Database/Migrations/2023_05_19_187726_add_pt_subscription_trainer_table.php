<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPtSubscriptionTrainerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('sw_gym_potential_members', function (Blueprint $table) {
            $table->dropUnique(['phone']);
        });
        Schema::table('sw_gym_pt_subscription_trainer', function (Blueprint $table) {
            $table->jsonb('reservation_details')->nullable();

            $table->unsignedInteger('pt_class_id')->nullable()->after('pt_trainer_id');
            $table->foreign('pt_class_id')->references('id')
                ->on('sw_gym_pt_subscription_trainer')
                ->onDelete('cascade');

            $table->unsignedInteger('pt_subscription_id')->nullable()->change();
            $table->boolean('is_completed')->default(0)->nullable();
            $table->integer('num_subscriptions')->nullable()->default(0);

        });

        Schema::table('sw_gym_pt_trainers', function (Blueprint $table) {
            $table->dropColumn('work_hours');
            $table->dropColumn('monthly_classes');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_pt_subscription_trainer', function (Blueprint $table) {
            $table->dropColumn('reservation_details');
            $table->dropColumn('pt_class_id');

            $table->unsignedInteger('pt_subscription_id')->nullable(false)->change();
            $table->dropColumn('is_completed');
            $table->dropColumn('num_subscriptions');
        });



        Schema::table('sw_gym_pt_trainers', function (Blueprint $table) {
            $table->text('work_hours')->nullable();
            $table->integer('monthly_classes');
        });
        Schema::table('sw_gym_potential_members', function (Blueprint $table) {
            $table->string('phone')->unique()->change();
        });

    }
}
