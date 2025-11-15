<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

            $table->foreign('class_id')
                ->references('id')
                ->on('sw_gym_pt_classes')
                ->onDelete('cascade');

            $table->foreign('trainer_id')
                ->references('id')
                ->on('sw_gym_pt_trainers')
                ->onDelete('cascade');
        });
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

            $table->foreign('class_id')
                ->references('id')
                ->on('sw_gym_pt_classes')
                ->onDelete('cascade');

            $table->foreign('class_trainer_id')
                ->references('id')
                ->on('sw_gym_pt_class_trainers')
                ->onDelete('set null');

            $table->foreign('trainer_id')
                ->references('id')
                ->on('sw_gym_pt_trainers')
                ->onDelete('set null');
        });
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

            $table->foreign('trainer_id')
                ->references('id')
                ->on('sw_gym_pt_trainers')
                ->onDelete('cascade');

            $table->foreign('pt_member_id')
                ->references('id')
                ->on('sw_gym_pt_members')
                ->onDelete('cascade');

            $table->foreign('session_id')
                ->references('id')
                ->on('sw_gym_pt_sessions')
                ->onDelete('set null');

            $table->foreign('paid_by')
                ->references('id')
                ->on('sw_gym_users')
                ->onDelete('set null');
        });
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

        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_pt_members', 'class_id')) {
                $table->foreign('class_id')
                    ->references('id')
                    ->on('sw_gym_pt_classes')
                    ->onDelete('set null');
            }

            if (Schema::hasColumn('sw_gym_pt_members', 'class_trainer_id')) {
                $table->foreign('class_trainer_id')
                    ->references('id')
                    ->on('sw_gym_pt_class_trainers')
                    ->onDelete('set null');
            }
        });
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

        Schema::table('sw_gym_pt_member_attendees', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_pt_member_attendees', 'session_id')) {
                $table->foreign('session_id')
                    ->references('id')
                    ->on('sw_gym_pt_sessions')
                    ->onDelete('set null');
            }
        });
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
                $table->dropForeign(['class_trainer_id']);
                $table->dropColumn('class_trainer_id');
            }

            if (Schema::hasColumn('sw_gym_pt_members', 'class_id')) {
                $table->dropForeign(['class_id']);
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
                $table->dropForeign(['session_id']);
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
}

