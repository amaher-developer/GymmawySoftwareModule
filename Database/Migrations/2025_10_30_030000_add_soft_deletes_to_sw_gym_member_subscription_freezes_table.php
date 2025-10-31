<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sw_gym_member_subscription_freezes', function (Blueprint $table) {
            if (!Schema::hasColumn('sw_gym_member_subscription_freezes', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_member_subscription_freezes', function (Blueprint $table) {
            if (Schema::hasColumn('sw_gym_member_subscription_freezes', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};


