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
        if (!Schema::hasTable('sw_gym_training_feedback')) {
            Schema::create('sw_gym_training_feedback', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->index();
                $table->unsignedBigInteger('plan_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index()->comment('trainer or member id');
                $table->tinyInteger('feedback_type')->default(1)->comment('1=member,2=trainer');
                $table->tinyInteger('rating')->nullable();
                $table->text('comments')->nullable();
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
        Schema::dropIfExists('sw_gym_training_feedback');
    }
};
