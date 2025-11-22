<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsFreezeToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('settings', 'is_freeze')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('is_freeze')->default(1)->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('settings', 'is_freeze')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('is_freeze');
        });
    }
}