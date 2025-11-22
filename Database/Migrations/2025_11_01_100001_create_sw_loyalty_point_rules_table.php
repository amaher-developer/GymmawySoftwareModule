<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create loyalty point rules table
 * 
 * This table stores conversion rates and expiry settings for the loyalty points system
 */
class CreateSwLoyaltyPointRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_loyalty_point_rules', function (Blueprint $table) {
            $table->increments('id');

            // Branch association (multi-branch support)
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();

            // Rule name for identification
            $table->string('name')->comment('Name of the loyalty rule (e.g., "Standard Points Rule")');

            // Conversion rates
            $table->decimal('money_to_point_rate', 10, 2)->default(10.00)
                ->comment('Amount of money needed to earn 1 point (e.g., 10.00 = 10 EGP = 1 Point)');
            
            $table->decimal('point_to_money_rate', 10, 2)->default(10.00)
                ->comment('Value of 1 point when redeemed (e.g., 10.00 = 1 Point = 10 EGP)');

            // Expiry settings
            $table->integer('expires_after_days')->nullable()
                ->comment('Number of days until points expire (null = never expire)');

            // Status
            $table->boolean('is_active')->default(true)
                ->comment('Whether this rule is currently active');

            $table->softDeletes();
            $table->timestamps();
        });

        $this->addForeignIfPossible(
            'sw_loyalty_point_rules',
            'branch_setting_id',
            'settings',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('cascade')
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sw_loyalty_point_rules');
    }
    private function addForeignIfPossible(
        string $table,
        string $column,
        string $referenceTable,
        string $referenceColumn = 'id',
        ?callable $callback = null
    ): void {
        if (!Schema::hasTable($table) || !Schema::hasTable($referenceTable)) {
            return;
        }

        $constraint = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', Schema::getConnection()->getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->value('CONSTRAINT_NAME');

        if ($constraint && $this->foreignKeyExists($table, $constraint)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $referenceTable, $referenceColumn, $callback) {
                $foreign = $blueprint->foreign($column)->references($referenceColumn)->on($referenceTable);

                if ($callback) {
                    $callback($foreign);
                }
            });
        } catch (QueryException $e) {
            Log::warning(sprintf(
                'Skipping FK creation %s -> %s.%s: %s',
                "{$table}.{$column}",
                $referenceTable,
                $referenceColumn,
                $e->getMessage()
            ));
        }
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', Schema::getConnection()->getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
}

