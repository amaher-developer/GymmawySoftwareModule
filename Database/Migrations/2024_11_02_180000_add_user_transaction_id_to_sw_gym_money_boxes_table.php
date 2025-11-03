<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserTransactionIdToSwGymMoneyBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_money_boxes', 'user_transaction_id')) {
                $table->unsignedBigInteger('user_transaction_id')->after('store_order_id')->index()->nullable();
                $table->foreign('user_transaction_id')->references('id')
                    ->on('sw_gym_user_transactions')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_money_boxes', 'user_transaction_id')) {
                $table->dropForeign(['user_transaction_id']);
                $table->dropColumn('user_transaction_id');
            }
        });
    }
}

