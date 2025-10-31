<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentTypeToNonMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_non_members` ADD `payment_type` TINYINT(4) NULL DEFAULT '0' AFTER `national_type`;");
        });
        // ALTER TABLE `sw_gym_non_members` ADD `payment_type` TINYINT(4) NULL DEFAULT '0' AFTER `national_type`;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
}
