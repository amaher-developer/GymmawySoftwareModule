<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSwGymPaymentTypeMethodsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sw_gym_payment_type_methods')) {
            return;
        }

        Schema::create('sw_gym_payment_type_methods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('payment_type_id');
            $table->unsignedInteger('payment_method');
            $table->unique(['payment_type_id', 'payment_method'], 'sw_gym_ptm_unique');
            $table->index('payment_method', 'sw_gym_ptm_method_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sw_gym_payment_type_methods');
    }
}
