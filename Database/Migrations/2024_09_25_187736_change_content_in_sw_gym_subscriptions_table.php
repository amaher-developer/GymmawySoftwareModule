<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeContentInSwGymSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->text('content_ar')->change();
            $table->text('content_en')->change();
        });
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->text('content_ar')->change();
            $table->text('content_en')->change();
        });
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->text('content_ar')->change();
            $table->text('content_en')->change();
        });
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->text('content_ar')->change();
            $table->text('content_en')->change();
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
            $table->string('content')->change();
            $table->string('content_en')->change();
        });
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->string('content')->change();
            $table->string('content_en')->change();
        });
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->string('content')->change();
            $table->string('content_en')->change();
        });
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->string('content')->change();
            $table->string('content_en')->change();
        });
    }
}
