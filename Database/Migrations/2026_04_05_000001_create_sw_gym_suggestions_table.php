<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymSuggestionsTable extends Migration
{
    public function up()
    {
        Schema::create('sw_gym_suggestions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('member_id')->nullable()->index();
            $table->enum('type', ['suggestion', 'complaint'])->default('suggestion');
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->text('message');
            $table->enum('status', ['pending', 'reviewed', 'resolved'])->default('pending');
            $table->text('admin_reply')->nullable();
            $table->unsignedInteger('branch_setting_id')->default(1)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sw_gym_suggestions');
    }
}
