<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sw_gym_pt_member_attendees', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_pt_member_attendees', 'session_date')) {
                $table->dateTime('session_date')->nullable()->after('session_id');
            }

            if (!Schema::hasColumn('sw_gym_pt_member_attendees', 'pt_member_session_unique')) {
                $table->index(['pt_member_id', 'session_date'], 'pt_member_session_unique');
            }
        });

        Schema::table('sw_gym_pt_commissions', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_pt_commissions', 'pt_member_attendee_id')) {
                $table->unsignedBigInteger('pt_member_attendee_id')->nullable()->after('pt_member_id');
            }

            if (!Schema::hasColumn('sw_gym_pt_commissions', 'session_date')) {
                $table->dateTime('session_date')->nullable()->after('session_id');
                $table->index('session_date');
            }
        });

        $this->addForeignIfPossible(
            'sw_gym_pt_commissions',
            'pt_member_attendee_id',
            'sw_gym_pt_member_attendees',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->nullOnDelete()
        );
    }

    public function down(): void
    {
        Schema::table('sw_gym_pt_member_attendees', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_pt_member_attendees', 'session_date')) {
                $table->dropColumn('session_date');
            }
            $table->dropIndex('pt_member_session_unique');
        });

        Schema::table('sw_gym_pt_commissions', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_pt_commissions', 'session_date')) {
                $table->dropColumn('session_date');
            }

            if (Schema::hasColumn('sw_gym_pt_commissions', 'pt_member_attendee_id')) {
                $this->dropForeignIfExists('sw_gym_pt_commissions', 'pt_member_attendee_id');
                $table->dropColumn('pt_member_attendee_id');
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

};





