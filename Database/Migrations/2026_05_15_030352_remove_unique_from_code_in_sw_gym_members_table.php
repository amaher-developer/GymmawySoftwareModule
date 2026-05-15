<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM sw_gym_members'))
            ->pluck('Key_name');

        Schema::table('sw_gym_members', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('sw_gym_members_code_unique')) {
                $table->dropUnique('sw_gym_members_code_unique');
            }
            if ($indexes->contains('code')) {
                $table->dropIndex('code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->unique('code');
        });
    }
};
