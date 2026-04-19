<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentMethodToGymPaymentTypesTable extends Migration
{
    public function up()
    {
        Schema::table('sw_gym_payment_types', function (Blueprint $table) {
            // Links a gateway TypeConstant (Tabby=4, Paymob=5, Tamara=6, PayTabs=8)
            // to this payment type row, enabling gateway-specific payment type binding.
            $table->unsignedInteger('payment_method')->nullable()->after('payment_id');
        });
    }

    public function down()
    {
        Schema::table('sw_gym_payment_types', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
}
