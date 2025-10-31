<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddElementFpToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_zk_fingerprints', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_zk_fingerprints` ADD `uid` INT(4) NULL DEFAULT NULL AFTER `type`;");
            DB::statement("ALTER TABLE `sw_gym_zk_fingerprints` ADD `cardno` VARCHAR(100) NULL DEFAULT NULL AFTER `uid`;");
        });

        // ALTER TABLE `sw_gym_zk_fingerprints` ADD `uid` INT(4) NULL DEFAULT NULL AFTER `type`;
        // ALTER TABLE `sw_gym_zk_fingerprints` ADD `cardno` VARCHAR(100) NULL DEFAULT NULL AFTER `type`;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_zk_fingerprints', function (Blueprint $table) {
            $table->dropColumn('uid');
            $table->dropColumn('cardno');
        });
    }
}
