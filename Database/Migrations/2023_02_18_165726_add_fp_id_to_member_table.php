<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFpIdToMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_members` ADD `fp_id` VARCHAR(50) NULL DEFAULT NULL AFTER `code`;");
        });
        // ALTER TABLE `sw_gym_members` ADD `fp_id` VARCHAR(50) NULL DEFAULT NULL AFTER `code`;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->dropColumn('fp_id');
        });
    }
}
