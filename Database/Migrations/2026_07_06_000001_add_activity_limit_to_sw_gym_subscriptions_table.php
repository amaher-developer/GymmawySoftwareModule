<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActivityLimitToSwGymSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('sw_gym_subscriptions', 'activity_limit')) {
            Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
                $table->unsignedInteger('activity_limit')->nullable()->after('workouts');
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
        if (Schema::hasColumn('sw_gym_subscriptions', 'activity_limit')) {
            Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
                $table->dropColumn('activity_limit');
            });
        }
    }
}
