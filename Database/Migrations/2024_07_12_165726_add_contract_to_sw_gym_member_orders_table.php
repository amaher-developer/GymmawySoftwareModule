<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractToSwGymMemberOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            $table->jsonb('contract_files')->nullable();
        });
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->jsonb('contract_files')->nullable();
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->jsonb('contract_files')->nullable();
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
            $table->dropColumn('contract_files');
        });
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->dropColumn('contract_files');
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->dropColumn('contract_files');
        });
    }
}
