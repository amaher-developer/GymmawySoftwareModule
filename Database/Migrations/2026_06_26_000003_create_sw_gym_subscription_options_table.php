<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sw_gym_subscription_options')) return;
        Schema::create('sw_gym_subscription_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('branch_setting_id')->nullable()->index();
            $table->foreign('branch_setting_id')->references('id')->on('settings')->onDelete('set null');
            $table->unsignedBigInteger('option_group_id')->index();
            $table->foreign('option_group_id')->references('id')->on('sw_gym_subscription_option_groups')->onDelete('cascade');
            $table->string('name_ar');
            $table->string('name_en');
            $table->decimal('price_modifier', 10, 2)->default(0);
            $table->integer('list_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sw_gym_subscription_options');
    }
};
