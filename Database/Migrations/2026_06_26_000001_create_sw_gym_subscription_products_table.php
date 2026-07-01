<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sw_gym_subscription_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('branch_setting_id')->nullable()->index();
            $table->foreign('branch_setting_id')->references('id')->on('settings')->onDelete('set null');
            $table->unsignedInteger('subscription_id')->index();
            $table->foreign('subscription_id')->references('id')->on('sw_gym_subscriptions')->onDelete('cascade');
            $table->unsignedInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('sw_gym_store_products')->onDelete('cascade');
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
