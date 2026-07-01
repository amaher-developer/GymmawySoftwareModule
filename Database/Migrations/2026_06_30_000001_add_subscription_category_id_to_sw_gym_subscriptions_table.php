<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionCategoryIdToSwGymSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('sw_gym_subscriptions', 'subscription_category_id')) {
            Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
                $table->unsignedInteger('subscription_category_id')->index()->nullable()->after('category_id');
            });
        }

        $this->addForeignIfPossible(
            'sw_gym_subscriptions',
            'subscription_category_id',
            'sw_gym_subscription_categories',
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
        if (Schema::hasColumn('sw_gym_subscriptions', 'subscription_category_id')) {
            Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
                $table->dropForeign(['subscription_category_id']);
                $table->dropColumn('subscription_category_id');
            });
        }
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

        $constraint = "{$table}_{$column}_foreign";

        if (
            DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('TABLE_SCHEMA', Schema::getConnection()->getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->where('CONSTRAINT_NAME', $constraint)
                ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
                ->exists()
        ) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $table) use ($column, $referenceTable, $referenceColumn, $callback) {
                $foreign = $table->foreign($column)->references($referenceColumn)->on($referenceTable);

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
}
