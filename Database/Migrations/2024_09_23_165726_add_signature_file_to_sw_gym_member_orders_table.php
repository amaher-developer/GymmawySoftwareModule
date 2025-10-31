<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSignatureFileToSwGymMemberOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            $table->string('signature_file')->nullable();
        });
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->string('signature_file')->nullable();
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->string('signature_file')->nullable();
        });




        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->string('default_discount_value')->nullable();
        });
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->string('default_discount_value')->nullable();
        });
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->string('default_discount_value')->nullable();
        });
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->string('default_discount_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            $table->dropColumn('signature_file');
        });
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->dropColumn('signature_file');
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->dropColumn('signature_file');
        });



        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->dropColumn('default_discount_value');
        });
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->dropColumn('default_discount_value');
        });
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropColumn('default_discount_value');
        });
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->dropColumn('default_discount_value');
        });
    }
}
