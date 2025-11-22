<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create loyalty campaigns table
 * 
 * This table stores promotional campaigns that multiply points earned during specific periods
 */
class CreateSwLoyaltyCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_loyalty_campaigns', function (Blueprint $table) {
            $table->increments('id');

            // Branch association (multi-branch support)
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();

            // Campaign details
            $table->string('name')->comment('Campaign name (e.g., "Ramadan 2x Points")');
            
            $table->decimal('multiplier', 5, 2)->default(1.00)
                ->comment('Points multiplier (e.g., 2.00 = double points, 1.5 = 50% bonus)');

            // Time period
            $table->dateTime('start_date')->comment('When campaign starts');
            $table->dateTime('end_date')->comment('When campaign ends');

            // Optional targeting
            $table->string('applies_to')->nullable()
                ->comment('Optional: specific subscription/product types this applies to');

            // Status
            $table->boolean('is_active')->default(true)
                ->comment('Whether this campaign is currently active');

            $table->softDeletes();
            $table->timestamps();
        });

        $this->addForeignIfPossible(
            'sw_loyalty_campaigns',
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
        Schema::dropIfExists('sw_loyalty_campaigns');
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

