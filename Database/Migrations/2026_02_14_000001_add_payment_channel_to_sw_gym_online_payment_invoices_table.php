<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentChannelToSwGymOnlinePaymentInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('sw_gym_online_payment_invoices', function (Blueprint $table) {
            $table->tinyInteger('payment_channel')->nullable()->after('payment_method');
        });
    }

    public function down()
    {
        Schema::table('sw_gym_online_payment_invoices', function (Blueprint $table) {
            $table->dropColumn('payment_channel');
        });
    }
}
