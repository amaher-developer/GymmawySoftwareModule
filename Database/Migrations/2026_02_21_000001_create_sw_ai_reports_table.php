<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * sw_ai_reports — Generic AI report log table.
 *
 * Designed to store any type of AI-generated report (executive, sales,
 * members, attendance, …) from any AI method (chatgpt, n8n, …).
 * The `type` column distinguishes report categories so a single table
 * serves all future report types without additional migrations.
 */
class CreateSwAiReportsTable extends Migration
{
    public function up(): void
    {
        Schema::create('sw_ai_reports', function (Blueprint $table) {
            $table->id();

            // ── Scope ──────────────────────────────────────────────────
            $table->unsignedBigInteger('branch_setting_id')->default(1)->index();

            // ── Classification ─────────────────────────────────────────
            $table->string('type', 50)->index()
                ->comment('executive | sales | members | attendance | custom');
            $table->string('method', 50)->default('chatgpt')
                ->comment('chatgpt | n8n | gemini | …');
            $table->string('model_used', 60)->nullable()
                ->comment('gpt-4o | gpt-4-turbo | …');
            $table->char('lang', 2)->default('ar')
                ->comment('ar | en');

            // ── Period ─────────────────────────────────────────────────
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();

            // ── Payload ────────────────────────────────────────────────
            $table->json('gym_data')->nullable()
                ->comment('Raw KPI data sent to AI');
            $table->json('report')->nullable()
                ->comment('Structured AI-generated JSON report');

            // ── Delivery tracking ──────────────────────────────────────
            $table->boolean('email_sent')->default(false);
            $table->string('email_sent_to')->nullable();
            $table->timestamp('email_sent_at')->nullable();

            $table->boolean('sms_sent')->default(false);
            $table->string('sms_sent_to')->nullable();
            $table->timestamp('sms_sent_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sw_ai_reports');
    }
}
