<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sw_gym_member_subscription_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('branch_setting_id')->nullable()->index();
            $table->foreign('branch_setting_id')->references('id')->on('settings')->onDelete('set null');
            $table->unsignedBigInteger('member_subscription_id')->index();
            $table->foreign('member_subscription_id', 'fk_msopt_member_sub_id')->references('id')->on('sw_gym_member_subscription')->onDelete('cascade');
            $table->unsignedBigInteger('option_id')->index();
            $table->foreign('option_id')->references('id')->on('sw_gym_subscription_options')->onDelete('cascade');
            $table->decimal('price', 10, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sw_gym_member_subscription_options');
    }
};
