<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AddActivityTrainerSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->addActivityLimitToSubscriptions();
        $this->createActivityTrainersTable();
        $this->addTrainerColumnsToReservations();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->revertReservationsTable();

        if (Schema::hasTable('sw_gym_activity_trainers')) {
            Schema::dropIfExists('sw_gym_activity_trainers');
        }

        $this->revertSubscriptionsTable();
    }

    private function addActivityLimitToSubscriptions(): void
    {
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_subscriptions', 'activity_limit')) {
                // null = unlimited, preserves today's "include every allowed activity" behavior
                $table->unsignedInteger('activity_limit')->nullable();
            }
        });
    }

    private function createActivityTrainersTable(): void
    {
        if (Schema::hasTable('sw_gym_activity_trainers')) {
            return;
        }

        Schema::create('sw_gym_activity_trainers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('branch_setting_id')->nullable()->index();
            $table->unsignedBigInteger('activity_id')->index();
            $table->unsignedBigInteger('trainer_id')->index();
            $table->json('schedule')->nullable();
            $table->unsignedInteger('reservation_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        $this->addForeignIfPossible(
            'sw_gym_activity_trainers',
            'activity_id',
            'sw_gym_activities',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('cascade')
        );

        $this->addForeignIfPossible(
            'sw_gym_activity_trainers',
            'trainer_id',
            'sw_gym_pt_trainers',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('cascade')
        );
    }

    private function addTrainerColumnsToReservations(): void
    {
        Schema::table('sw_gym_reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_reservations', 'activity_trainer_id')) {
                $table->unsignedBigInteger('activity_trainer_id')->nullable()->after('activity_id')->index();
            }

            if (!Schema::hasColumn('sw_gym_reservations', 'trainer_id')) {
                $table->unsignedBigInteger('trainer_id')->nullable()->after('activity_trainer_id')->index();
            }
        });

        $this->addForeignIfPossible(
            'sw_gym_reservations',
            'activity_trainer_id',
            'sw_gym_activity_trainers',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('set null')
        );

        $this->addForeignIfPossible(
            'sw_gym_reservations',
            'trainer_id',
            'sw_gym_pt_trainers',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('set null')
        );
    }

    private function revertReservationsTable(): void
    {
        if (Schema::hasColumn('sw_gym_reservations', 'trainer_id')) {
            $this->dropForeignIfExists('sw_gym_reservations', 'trainer_id');
        }

        if (Schema::hasColumn('sw_gym_reservations', 'activity_trainer_id')) {
            $this->dropForeignIfExists('sw_gym_reservations', 'activity_trainer_id');
        }

        Schema::table('sw_gym_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_reservations', 'trainer_id')) {
                $table->dropColumn('trainer_id');
            }

            if (Schema::hasColumn('sw_gym_reservations', 'activity_trainer_id')) {
                $table->dropColumn('activity_trainer_id');
            }
        });
    }

    private function revertSubscriptionsTable(): void
    {
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_subscriptions', 'activity_limit')) {
                $table->dropColumn('activity_limit');
            }
        });
    }

    private function dropForeignIfExists(string $table, string $column, ?string $constraint = null): void
    {
        $constraint = $constraint ?? $this->guessForeignKeyName($table, $column);

        if ($this->foreignKeyExists($table, $constraint)) {
            DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $constraint));
        }
    }

    private function guessForeignKeyName(string $table, string $column): string
    {
        return "{$table}_{$column}_foreign";
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    private function addForeignIfPossible(
        string $table,
        string $column,
        string $referenceTable,
        string $referenceColumn = 'id',
        ?callable $callback = null
    ): void {
        if (
            !Schema::hasTable($table) ||
            !Schema::hasColumn($table, $column) ||
            !Schema::hasTable($referenceTable) ||
            !Schema::hasColumn($referenceTable, $referenceColumn)
        ) {
            return;
        }

        $constraint = $this->guessForeignKeyName($table, $column);

        if ($this->foreignKeyExists($table, $constraint)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $referenceTable, $referenceColumn, $callback, $constraint) {
                $foreign = $blueprint->foreign($column, $constraint)->references($referenceColumn)->on($referenceTable);

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
