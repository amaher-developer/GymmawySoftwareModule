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
        if (!Schema::hasTable('sw_gym_training_templates')) {
            Schema::create('sw_gym_training_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_setting_id')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->enum('type', ['training', 'diet'])->default('training');
                $table->string('template_name');
                $table->json('content')->nullable()->comment('structured template days/items');
                $table->text('notes')->nullable();
                $table->tinyInteger('is_public')->default(0);
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
        Schema::dropIfExists('sw_gym_training_templates');
    }
};
