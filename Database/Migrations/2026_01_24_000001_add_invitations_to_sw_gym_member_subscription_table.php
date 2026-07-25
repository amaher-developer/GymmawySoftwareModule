<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: Add invitations column to subscription templates table
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_subscriptions', 'invitations')) {
                $table->integer('invitations')->default(0)->nullable()->after('max_freeze_extension_sum');
            }
        });

        // Step 2: Add invitations column to member subscription table
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_member_subscription', 'invitations')) {
                $table->integer('invitations')->default(0)->nullable()->after('notes');
            }
        });

        // Step 3: Migrate data from members to their latest subscription
        DB::statement('
            UPDATE sw_gym_member_subscription AS sub
            INNER JOIN (
                SELECT member_id, MAX(id) as latest_sub_id
                FROM sw_gym_member_subscription
                GROUP BY member_id
            ) AS latest ON sub.id = latest.latest_sub_id
            INNER JOIN sw_gym_members AS m ON m.id = sub.member_id
            SET sub.invitations = COALESCE(m.invitations, 0)
            WHERE m.invitations > 0
        ');
    }

    public function down(): void
    {
        // Copy back from subscription to member before dropping
        DB::statement('
            UPDATE sw_gym_members AS m
            INNER JOIN (
                SELECT member_id, MAX(id) as latest_sub_id
                FROM sw_gym_member_subscription
                GROUP BY member_id
            ) AS latest ON m.id = latest.member_id
            INNER JOIN sw_gym_member_subscription AS sub ON sub.id = latest.latest_sub_id
            SET m.invitations = COALESCE(sub.invitations, 0)
        ');

        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_member_subscription', 'invitations')) {
                $table->dropColumn('invitations');
            }
        });

        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_subscriptions', 'invitations')) {
                $table->dropColumn('invitations');
            }
        });
    }
};
