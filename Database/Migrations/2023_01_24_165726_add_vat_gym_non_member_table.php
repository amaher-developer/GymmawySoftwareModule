<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatGymNonMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_non_members` ADD `vat` FLOAT NULL DEFAULT '0' AFTER `price`;");
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->dropColumn('vat');
        });

    }
}
