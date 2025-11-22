<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sw_gym_member_subscription_freezes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('member_subscription_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['pending', 'approved', 'rejected', 'active', 'completed'])->default('pending');
            $table->integer('freeze_limit')->default(0);
            $table->text('reason')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        $this->addForeignIfPossible(
            'sw_gym_member_subscription_freezes',
            'member_subscription_id',
            'sw_gym_member_subscription',
            'id',
            function (ForeignKeyDefinition $foreign) {
                $foreign->onDelete('cascade')->indexName('fk_gms_freezes_sub');
            }
        );

        // Backfill existing freeze data from sw_gym_member_subscription
        try {
            DB::table('sw_gym_member_subscription')
                ->select(['id as member_subscription_id', 'member_id', 'start_freeze_date', 'end_freeze_date', 'freeze_limit'])
                ->whereNotNull('start_freeze_date')
                ->whereNotNull('end_freeze_date')
                ->orderBy('id')
                ->chunk(500, function ($rows) {
                    $now = Carbon::now();
                    $insert = [];
                    foreach ($rows as $row) {
                        $start = Carbon::parse($row->start_freeze_date);
                        $end = Carbon::parse($row->end_freeze_date);
                        $status = 'approved';
                        if ($start->lte($now) && $end->gt($now)) {
                            $status = 'active';
                        } elseif ($end->lte($now)) {
                            $status = 'completed';
                        }
                        $insert[] = [
                            'member_id' => $row->member_id,
                            'member_subscription_id' => $row->member_subscription_id,
                            'start_date' => $start->toDateString(),
                            'end_date' => $end->toDateString(),
                            'status' => $status,
                            'freeze_limit' => (int)($row->freeze_limit ?? 0),
                            'reason' => null,
                            'admin_note' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    if ($insert) {
                        DB::table('sw_gym_member_subscription_freezes')->insert($insert);
                    }
                });
        } catch (\Throwable $e) {
            // If the source columns don't exist or any error occurs, skip backfill
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sw_gym_member_subscription_freezes');
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
};

