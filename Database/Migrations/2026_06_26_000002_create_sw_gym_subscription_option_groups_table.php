<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sw_gym_subscription_option_groups')) return;
        Schema::create('sw_gym_subscription_option_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('branch_setting_id')->nullable()->index();
            $table->unsignedInteger('subscription_id')->index();
            $table->string('name_ar');
            $table->string('name_en');
            $table->enum('selection_type', ['single', 'multiple'])->default('single');
            $table->boolean('is_required')->default(false);
            $table->integer('list_order')->default(0);
            $table->boolean('is_system')->default(true);
            $table->boolean('is_web')->default(true);
            $table->boolean('is_mobile')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sw_gym_subscription_option_groups');
    }
};
