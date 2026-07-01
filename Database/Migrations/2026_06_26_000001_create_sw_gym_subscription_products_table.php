<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sw_gym_subscription_products')) return;
        Schema::create('sw_gym_subscription_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('branch_setting_id')->nullable()->index();
            $table->unsignedInteger('subscription_id')->index();
            $table->unsignedInteger('product_id')->index();
            $table->integer('list_order')->default(0);
            $table->boolean('is_replaceable')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sw_gym_subscription_products');
    }
};
