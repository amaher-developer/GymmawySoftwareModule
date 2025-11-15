<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
                $table->foreign('pt_member_attendee_id')
                    ->references('id')
                    ->on('sw_gym_pt_member_attendees')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('sw_gym_pt_commissions', 'session_date')) {
                $table->dateTime('session_date')->nullable()->after('session_id');
                $table->index('session_date');
            }
        });
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
                $table->dropForeign(['pt_member_attendee_id']);
                $table->dropColumn('pt_member_attendee_id');
            }
        });
    }

};





