<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmployeeIdAndTransactionTypeToSwGymUserTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_user_transactions', function (Blueprint $table) {
            // Add employee_id column (the employee receiving the transaction)
            if (!Schema::hasColumn('sw_gym_user_transactions', 'employee_id')) {
                $table->unsignedInteger('employee_id')->after('user_id')->index()->nullable();
                $table->foreign('employee_id')->references('id')
                    ->on('sw_gym_users')
                    ->onDelete('cascade');
            }

            // Add transaction_type column
            if (!Schema::hasColumn('sw_gym_user_transactions', 'transaction_type')) {
                $table->string('transaction_type')->after('employee_id')->nullable();
            }

            // Add notes column (rename from note if exists, or add new)
            if (!Schema::hasColumn('sw_gym_user_transactions', 'notes')) {
                $table->text('notes')->after('transaction_type')->nullable();
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
        Schema::table('sw_gym_user_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_user_transactions', 'employee_id')) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            }
            
            if (Schema::hasColumn('sw_gym_user_transactions', 'transaction_type')) {
                $table->dropColumn('transaction_type');
            }

            if (Schema::hasColumn('sw_gym_user_transactions', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
}

