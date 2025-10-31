<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContentToSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->string('content_ar')->nullable();
            $table->string('content_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->dropColumn('content_ar');
            $table->dropColumn('content_en');
        });
    }
}
