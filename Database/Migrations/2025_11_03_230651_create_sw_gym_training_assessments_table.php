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
        if (!Schema::hasTable('sw_gym_training_assessments')) {
            Schema::create('sw_gym_training_assessments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_setting_id')->nullable()->index();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->unsignedBigInteger('trainer_id')->nullable()->index();
                $table->json('answers')->nullable()->comment('dynamic assessment fields');
                $table->text('notes')->nullable();
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
        Schema::dropIfExists('sw_gym_training_assessments');
    }
};
