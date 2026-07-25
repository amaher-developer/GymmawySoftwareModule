<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceTypeToGymOnlinePaymentInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('sw_gym_online_payment_invoices', function (Blueprint $table) {
            // Differentiates the payment category so the report can link to the correct invoice view.
            // Values: subscription, pt_subscription, activity, store
            $table->string('invoice_type', 30)->nullable()->after('member_subscription_id');
        });
    }

    public function down()
    {
        Schema::table('sw_gym_online_payment_invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_type');
        });
    }
}
