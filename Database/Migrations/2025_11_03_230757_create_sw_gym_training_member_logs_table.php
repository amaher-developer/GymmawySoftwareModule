<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sw_gym_training_member_logs')) {
            Schema::create('sw_gym_training_member_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_setting_id')->nullable()->index();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->unsignedBigInteger('training_id')->nullable()->index();
                $table->string('training_type')->nullable()->comment('plan|task|file|track|medicine|note|assessment|ai_plan');
                $table->string('action')->nullable()->comment('e.g., added, updated, removed');
                $table->text('notes')->nullable();
                $table->json('meta')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sw_gym_training_member_logs');
    }
};
