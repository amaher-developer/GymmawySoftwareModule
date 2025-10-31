<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNationalIdToSwGymNonMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->string('national_id')->nullable();
            $table->string('national_type')->nullable();

        });
//        ALTER TABLE `sw_gym_non_members` ADD `national_id` VARCHAR(191) NULL DEFAULT NULL;
//        ALTER TABLE `sw_gym_non_members` ADD `national_type` VARCHAR(191) NULL DEFAULT NULL;

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->dropColumn('national_id');
            $table->dropColumn('national_type');
        });
    }
}
