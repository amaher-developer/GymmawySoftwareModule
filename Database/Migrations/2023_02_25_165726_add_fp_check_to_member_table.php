<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFpCheckToMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_members` ADD `fp_check` TINYINT(1) NULL DEFAULT '0' AFTER `fp_id`;");
        });
        // ALTER TABLE `sw_gym_members` ADD `fp_check` TINYINT(1) NULL DEFAULT '0' AFTER `fp_id`;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->dropColumn('fp_check');
        });
    }
}
