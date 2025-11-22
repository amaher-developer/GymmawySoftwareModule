<?php

use Illuminate\Support\Facades\DB;
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
        $this->dropForeignIfExists('sw_gym_money_boxes', 'user_transaction_id', 'sw_gym_money_boxes_user_transaction_id_foreign');

        if (Schema::hasColumn('sw_gym_money_boxes', 'user_transaction_id')) {
            Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
                // Recreate the foreign key WITHOUT any delete action
                // Money box entries are immutable like invoices and should NEVER be deleted
                // The relationship is maintained for audit trail purposes only
                $table->foreign('user_transaction_id')->references('id')
                    ->on('sw_gym_user_transactions');
                    // No onDelete action - money box entries remain intact even if transaction is deleted
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropForeignIfExists('sw_gym_money_boxes', 'user_transaction_id', 'sw_gym_money_boxes_user_transaction_id_foreign');

        if (Schema::hasColumn('sw_gym_money_boxes', 'user_transaction_id')) {
            Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
                // Recreate with cascade (original behavior)
                $table->foreign('user_transaction_id')->references('id')
                    ->on('sw_gym_user_transactions')
                    ->onDelete('cascade');
            });
        }
    }

    protected function dropForeignIfExists(string $table, string $column, ?string $constraint = null): void
    {
        $constraint = $constraint ?? $this->guessForeignKeyName($table, $column);

        if ($this->foreignKeyExists($table, $constraint)) {
            DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $constraint));
        }
    }

    protected function guessForeignKeyName(string $table, string $column): string
    {
        return "{$table}_{$column}_foreign";
    }

    protected function foreignKeyExists(string $table, string $foreignKey): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $foreignKey)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
}

