<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateSwGymUserPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_user_permissions', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->unsignedInteger('user_id')->index()->nullable();

            $table->string('title_ar');
            $table->string('title_en');
            $table->text('permissions')->nullable(); // JSON array of permissions

            $table->softDeletes();
            $table->timestamps();
        });

        $this->addForeignIfPossible(
            'sw_gym_user_permissions',
            'branch_setting_id',
            'settings',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('cascade')
        );

        $this->addForeignIfPossible(
            'sw_gym_user_permissions',
            'user_id',
            'sw_gym_users',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('cascade')
        );

        if (Schema::hasTable('sw_gym_users') && !Schema::hasColumn('sw_gym_users', 'permission_group_id')) {
            Schema::table('sw_gym_users', function (Blueprint $table) {
                $table->unsignedInteger('permission_group_id')->nullable()->after('permissions');
            });
        }

        $this->addForeignIfPossible(
            'sw_gym_users',
            'permission_group_id',
            'sw_gym_user_permissions',
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
        // Drop foreign key from sw_gym_users
        Schema::table('sw_gym_users', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_users', 'permission_group_id')) {
                $this->dropForeignIfExists('sw_gym_users', 'permission_group_id');
                $table->dropColumn('permission_group_id');
            }
        });

        Schema::dropIfExists('sw_gym_user_permissions');
    }

    private function dropForeignIfExists(string $table, string $column, ?string $constraint = null): void
    {
        $constraint = $constraint ?? $this->guessForeignKeyName($table, $column);

        if ($this->foreignKeyExists($table, $constraint)) {
            DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $constraint));
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

        $constraint = $this->guessForeignKeyName($table, $column);

        if ($this->foreignKeyExists($table, $constraint)) {
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
}

