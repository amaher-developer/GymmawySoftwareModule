<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUserTransactionIdForeignKeyInSwGymMoneyBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            // Drop the existing foreign key with cascade delete
            $table->dropForeign(['user_transaction_id']);
            
            // Recreate the foreign key WITHOUT any delete action
            // Money box entries are immutable like invoices and should NEVER be deleted
            // The relationship is maintained for audit trail purposes only
            $table->foreign('user_transaction_id')->references('id')
                ->on('sw_gym_user_transactions');
                // No onDelete action - money box entries remain intact even if transaction is deleted
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
            // Drop the restrict foreign key
            $table->dropForeign(['user_transaction_id']);
            
            // Recreate with cascade (original behavior)
            $table->foreign('user_transaction_id')->references('id')
                ->on('sw_gym_user_transactions')
                ->onDelete('cascade');
        });
    }
}

