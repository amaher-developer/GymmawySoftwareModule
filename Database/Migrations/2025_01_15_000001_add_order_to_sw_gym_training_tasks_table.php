<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderToSwGymTrainingTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('sw_gym_training_tasks', 'order')) {
            Schema::table('sw_gym_training_tasks', function (Blueprint $table) {
                $table->unsignedSmallInteger('order')->default(0)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('sw_gym_training_tasks', 'order')) {
            Schema::table('sw_gym_training_tasks', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }
}

