<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFpCheckInToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_users', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_users` ADD `fp_id` VARCHAR(50) NULL DEFAULT NULL AFTER `email`;");
            DB::statement("ALTER TABLE `sw_gym_users` ADD `fp_check` TINYINT(1) NULL DEFAULT '0' AFTER `fp_id`;");
        });
        // ALTER TABLE `sw_gym_users` ADD `fp_id` VARCHAR(50) NULL DEFAULT NULL AFTER `email`;
        // ALTER TABLE `sw_gym_users` ADD `fp_check` TINYINT(1) NULL DEFAULT '0' AFTER `fp_id`;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_users', function (Blueprint $table) {
            $table->dropColumn('fp_id');
            $table->dropColumn('fp_check');
        });
    }
}
