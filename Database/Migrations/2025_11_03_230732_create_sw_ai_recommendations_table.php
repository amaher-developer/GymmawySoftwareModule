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
        if (!Schema::hasTable('sw_ai_recommendations')) {
            Schema::create('sw_ai_recommendations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->unsignedBigInteger('trainer_id')->nullable()->index();
                $table->enum('type', ['training', 'diet'])->default('training');
                $table->json('context_data')->nullable()->comment('assessment + logs + files metadata');
                $table->longText('ai_response')->nullable()->comment('raw AI output');
                $table->enum('status', ['pending', 'approved', 'converted'])->default('pending');
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
        Schema::dropIfExists('sw_ai_recommendations');
    }
};
