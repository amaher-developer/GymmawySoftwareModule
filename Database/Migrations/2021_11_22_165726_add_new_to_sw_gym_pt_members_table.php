<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewToSwGymPtMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->integer('classes')->default(0);
            $table->integer('visits')->default(0);
            $table->integer('amount_paid')->default(0)->nullable();
            $table->integer('amount_remaining')->default(0);
//            $table->integer('amount_before_discount')->default(0);
//            $table->integer('discount_value')->default(0);
//            $table->tinyInteger('discount_type')->default(1)->nullable();
            $table->timestamp('joining_date')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable();
//            $table->timestamp('expire_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->dropColumn('classes');
            $table->dropColumn('visits');
            $table->dropColumn('amount_paid');
            $table->dropColumn('amount_remaining');
//            $table->dropColumn('amount_before_discount');
//            $table->dropColumn('discount_value');
//            $table->dropColumn('discount_type');
            $table->dropColumn('joining_date');
//            $table->dropColumn('expire_date');
        });
    }
}
