<?php

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymStoreCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_store_categories', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->unsignedInteger('user_id')->index()->nullable();

            $table->string('name_ar');
            $table->string('name_en');
            $table->string('image')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        $this->addForeignIfPossible(
            'sw_gym_store_categories',
            'branch_setting_id',
            'settings',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('cascade')
        );

        $this->addForeignIfPossible(
            'sw_gym_store_categories',
            'user_id',
            'sw_gym_users',
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
        Schema::dropIfExists('sw_gym_store_categories');
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

