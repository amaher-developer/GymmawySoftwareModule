<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration to add loyalty points fields to members table
 * 
 * Adds balance tracking and last update timestamp
 */
class AddLoyaltyFieldsToSwGymMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            // Current loyalty points balance
            $table->integer('loyalty_points_balance')->default(0)
                ->comment('Current available loyalty points balance')
                ->after('id'); // Add after id column

            // Track when last points activity occurred
            $table->dateTime('last_points_update')->nullable()
                ->comment('Timestamp of last points transaction')
                ->after('loyalty_points_balance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points_balance', 'last_points_update']);
        });
    }
}

