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
        if (!Schema::hasTable('sw_gym_training_notifications')) {
            Schema::create('sw_gym_training_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('title');
                $table->text('message')->nullable();
                $table->tinyInteger('type')->default(1)->comment('1=plan_update,2=feedback,3=ai_suggestion');
                $table->tinyInteger('status')->default(0)->comment('0=unread,1=read');
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
        Schema::dropIfExists('sw_gym_training_notifications');
    }
};
