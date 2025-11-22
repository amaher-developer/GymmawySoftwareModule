<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UpdatePtSchemaPhase1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createClassTrainerTable();
        $this->createSessionTable();
        $this->createCommissionTable();

        $this->updateClassesTable();
        $this->updateMembersTable();
        $this->updateMemberAttendeesTable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->revertMemberAttendeesTable();
        $this->revertMembersTable();
        $this->revertClassesTable();

        if (Schema::hasTable('sw_gym_pt_commissions')) {
            Schema::dropIfExists('sw_gym_pt_commissions');
        }

        if (Schema::hasTable('sw_gym_pt_sessions')) {
            Schema::dropIfExists('sw_gym_pt_sessions');
        }

        if (Schema::hasTable('sw_gym_pt_class_trainers')) {
            Schema::dropIfExists('sw_gym_pt_class_trainers');
        }
    }

    private function createClassTrainerTable(): void
    {
        if (Schema::hasTable('sw_gym_pt_class_trainers')) {
            return;
        }

        Schema::create('sw_gym_pt_class_trainers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('branch_setting_id')->nullable()->index();
            $table->unsignedBigInteger('class_id')->index();
            $table->unsignedBigInteger('trainer_id')->index();
            $table->string('session_type')->nullable();
            $table->unsignedInteger('session_count')->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        $this->addForeignIfPossible(
            'sw_gym_pt_class_trainers',
            'class_id',
            'sw_gym_pt_classes',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('cascade')
        );

        $this->addForeignIfPossible(
            'sw_gym_pt_class_trainers',
            'trainer_id',
            'sw_gym_pt_trainers',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('cascade')
        );
    }

    private function createSessionTable(): void
    {
        if (Schema::hasTable('sw_gym_pt_sessions')) {
            return;
        }

        Schema::create('sw_gym_pt_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('branch_setting_id')->nullable()->index();
            $table->unsignedBigInteger('class_id')->index();
            $table->unsignedBigInteger('class_trainer_id')->nullable()->index();
            $table->unsignedBigInteger('trainer_id')->nullable()->index();
            $table->dateTime('session_date')->index();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        $this->addForeignIfPossible(
            'sw_gym_pt_sessions',
            'class_id',
            'sw_gym_pt_classes',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('cascade')
        );

        $this->addForeignIfPossible(
            'sw_gym_pt_sessions',
            'class_trainer_id',
            'sw_gym_pt_class_trainers',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('set null')
        );

        $this->addForeignIfPossible(
            'sw_gym_pt_sessions',
            'trainer_id',
            'sw_gym_pt_trainers',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('set null')
        );
    }

    private function createCommissionTable(): void
    {
        if (Schema::hasTable('sw_gym_pt_commissions')) {
            return;
        }

        Schema::create('sw_gym_pt_commissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('branch_setting_id')->nullable()->index();
            $table->unsignedBigInteger('trainer_id')->index();
            $table->unsignedBigInteger('pt_member_id')->index();
            $table->unsignedBigInteger('session_id')->nullable()->index();
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'paid'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        $this->addForeignIfPossible(
            'sw_gym_pt_commissions',
            'trainer_id',
            'sw_gym_pt_trainers',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('cascade')
        );

        $this->addForeignIfPossible(
            'sw_gym_pt_commissions',
            'pt_member_id',
            'sw_gym_pt_members',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('cascade')
        );

        $this->addForeignIfPossible(
            'sw_gym_pt_commissions',
            'session_id',
            'sw_gym_pt_sessions',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('set null')
        );

        $this->addForeignIfPossible(
            'sw_gym_pt_commissions',
            'paid_by',
            'sw_gym_users',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('set null')
        );
    }

    private function updateClassesTable(): void
    {
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $afterForSchedule = Schema::hasColumn('sw_gym_pt_classes', 'reservation_details') ? 'reservation_details' : 'price';

            if (!Schema::hasColumn('sw_gym_pt_classes', 'class_type')) {
                $table->enum('class_type', ['private', 'group', 'mixed'])->default('private')->after('price');
            }

            if (!Schema::hasColumn('sw_gym_pt_classes', 'pricing_type')) {
                $table->enum('pricing_type', ['per_member', 'per_group'])->default('per_member')->after('class_type');
            }

            if (!Schema::hasColumn('sw_gym_pt_classes', 'is_mixed')) {
                $table->boolean('is_mixed')->default(false)->after('pricing_type');
            }

            if (!Schema::hasColumn('sw_gym_pt_classes', 'total_sessions')) {
                $table->unsignedInteger('total_sessions')->default(0)->after('classes');
            }

            if (!Schema::hasColumn('sw_gym_pt_classes', 'max_members')) {
                $table->unsignedInteger('max_members')->nullable()->after('total_sessions');
            }

            if (!Schema::hasColumn('sw_gym_pt_classes', 'schedule')) {
                $table->json('schedule')->nullable()->after($afterForSchedule);
            }

            if (!Schema::hasColumn('sw_gym_pt_classes', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('schedule');
            }
        });
    }

    private function updateMembersTable(): void
    {
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_pt_members', 'class_id')) {
                $table->unsignedBigInteger('class_id')->nullable()->after('pt_class_id')->index();
            }

            if (!Schema::hasColumn('sw_gym_pt_members', 'class_trainer_id')) {
                $table->unsignedBigInteger('class_trainer_id')->nullable()->after('class_id')->index();
            }

            if (!Schema::hasColumn('sw_gym_pt_members', 'total_sessions')) {
                $table->unsignedInteger('total_sessions')->default(0)->after('classes');
            }

            if (!Schema::hasColumn('sw_gym_pt_members', 'remaining_sessions')) {
                $table->unsignedInteger('remaining_sessions')->default(0)->after('total_sessions');
            }

            if (!Schema::hasColumn('sw_gym_pt_members', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->default(0)->after('amount_paid');
            }

            if (!Schema::hasColumn('sw_gym_pt_members', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('discount_value');
            }

            if (!Schema::hasColumn('sw_gym_pt_members', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('discount_type');
            }

            if (!Schema::hasColumn('sw_gym_pt_members', 'start_date')) {
                $table->date('start_date')->nullable()->after('joining_date');
            }

            if (!Schema::hasColumn('sw_gym_pt_members', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }

            if (!Schema::hasColumn('sw_gym_pt_members', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('end_date');
            }
        });

        $this->addForeignIfPossible(
            'sw_gym_pt_members',
            'class_id',
            'sw_gym_pt_classes',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('set null')
        );

        $this->addForeignIfPossible(
            'sw_gym_pt_members',
            'class_trainer_id',
            'sw_gym_pt_class_trainers',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('set null')
        );
    }

    private function updateMemberAttendeesTable(): void
    {
        Schema::table('sw_gym_pt_member_attendees', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_pt_member_attendees', 'branch_setting_id')) {
                $table->unsignedInteger('branch_setting_id')->nullable()->after('id')->index();
            }

            if (!Schema::hasColumn('sw_gym_pt_member_attendees', 'session_id')) {
                $table->unsignedBigInteger('session_id')->nullable()->after('pt_member_id')->index();
            }

            if (!Schema::hasColumn('sw_gym_pt_member_attendees', 'attended')) {
                $table->boolean('attended')->default(true)->after('session_id');
            }

            if (!Schema::hasColumn('sw_gym_pt_member_attendees', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        $this->addForeignIfPossible(
            'sw_gym_pt_member_attendees',
            'session_id',
            'sw_gym_pt_sessions',
            'id',
            fn (ForeignKeyDefinition $foreign) => $foreign->onDelete('set null')
        );
    }

    private function revertClassesTable(): void
    {
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_pt_classes', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('sw_gym_pt_classes', 'schedule')) {
                $table->dropColumn('schedule');
            }

            if (Schema::hasColumn('sw_gym_pt_classes', 'max_members')) {
                $table->dropColumn('max_members');
            }

            if (Schema::hasColumn('sw_gym_pt_classes', 'total_sessions')) {
                $table->dropColumn('total_sessions');
            }

            if (Schema::hasColumn('sw_gym_pt_classes', 'is_mixed')) {
                $table->dropColumn('is_mixed');
            }

            if (Schema::hasColumn('sw_gym_pt_classes', 'pricing_type')) {
                $table->dropColumn('pricing_type');
            }

            if (Schema::hasColumn('sw_gym_pt_classes', 'class_type')) {
                $table->dropColumn('class_type');
            }
        });
    }

    private function revertMembersTable(): void
    {
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_pt_members', 'class_trainer_id')) {
                $this->dropForeignIfExists('sw_gym_pt_members', 'class_trainer_id');
                $table->dropColumn('class_trainer_id');
            }

            if (Schema::hasColumn('sw_gym_pt_members', 'class_id')) {
                $this->dropForeignIfExists('sw_gym_pt_members', 'class_id');
                $table->dropColumn('class_id');
            }

            if (Schema::hasColumn('sw_gym_pt_members', 'total_sessions')) {
                $table->dropColumn('total_sessions');
            }

            if (Schema::hasColumn('sw_gym_pt_members', 'remaining_sessions')) {
                $table->dropColumn('remaining_sessions');
            }

            if (Schema::hasColumn('sw_gym_pt_members', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }

            if (Schema::hasColumn('sw_gym_pt_members', 'discount')) {
                $table->dropColumn('discount');
            }

            if (Schema::hasColumn('sw_gym_pt_members', 'payment_method')) {
                $table->dropColumn('payment_method');
            }

            if (Schema::hasColumn('sw_gym_pt_members', 'start_date')) {
                $table->dropColumn('start_date');
            }

            if (Schema::hasColumn('sw_gym_pt_members', 'end_date')) {
                $table->dropColumn('end_date');
            }

            if (Schema::hasColumn('sw_gym_pt_members', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }

    private function revertMemberAttendeesTable(): void
    {
        Schema::table('sw_gym_pt_member_attendees', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_pt_member_attendees', 'session_id')) {
                $this->dropForeignIfExists('sw_gym_pt_member_attendees', 'session_id');
                $table->dropColumn('session_id');
            }

            if (Schema::hasColumn('sw_gym_pt_member_attendees', 'branch_setting_id')) {
                $table->dropColumn('branch_setting_id');
            }

            if (Schema::hasColumn('sw_gym_pt_member_attendees', 'attended')) {
                $table->dropColumn('attended');
            }

            if (Schema::hasColumn('sw_gym_pt_member_attendees', 'deleted_at')) {
                $table->dropSoftDeletes();
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

