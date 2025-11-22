<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create loyalty transactions table
 * 
 * This table records all points movements (earn/redeem/manual adjustments)
 */
class CreateSwLoyaltyTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_loyalty_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Member association
            $table->unsignedInteger('member_id')->index();

            // Rule association (optional - may not apply to manual transactions)
            $table->unsignedInteger('rule_id')->nullable();

            // Campaign association (optional - only when campaign is active)
            $table->unsignedInteger('campaign_id')->nullable();

            // Points and transaction details
            $table->integer('points')->comment('Positive for earn/add, negative for redeem/deduct');
            
            $table->enum('type', ['earn', 'redeem', 'manual'])->default('earn')
                ->comment('Transaction type: earn (from payment), redeem (discount), manual (admin adjustment)');

            // Source tracking (polymorphic - can be subscription, order, etc.)
            $table->string('source_type')->nullable()
                ->comment('Type of source (e.g., "subscription", "order", "manual")');
            
            $table->unsignedBigInteger('source_id')->nullable()
                ->comment('ID of the source record');

            // Additional information
            $table->text('reason')->nullable()
                ->comment('Reason for transaction (especially for manual adjustments)');

            $table->decimal('amount_spent', 10, 2)->nullable()
                ->comment('Amount of money spent that generated these points (for earn type)');

            // Expiry tracking
            $table->dateTime('expires_at')->nullable()
                ->comment('When these points expire (null = never)');

            $table->boolean('is_expired')->default(false)
                ->comment('Whether these points have been marked as expired');

            // Admin tracking (for manual transactions)
            $table->unsignedInteger('created_by')->nullable();

            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['member_id', 'type']);
            $table->index(['member_id', 'expires_at']);
        });

        $this->addForeignIfPossible(
            'sw_loyalty_transactions',
            'member_id',
            'sw_gym_members',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('cascade')
        );

        $this->addForeignIfPossible(
            'sw_loyalty_transactions',
            'rule_id',
            'sw_loyalty_point_rules',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('set null')
        );

        $this->addForeignIfPossible(
            'sw_loyalty_transactions',
            'campaign_id',
            'sw_loyalty_campaigns',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('set null')
        );

        $this->addForeignIfPossible(
            'sw_loyalty_transactions',
            'created_by',
            'sw_gym_users',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('set null')
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sw_loyalty_transactions');
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

