<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMemberSubscriptionIdToSwGymOnlinePaymentInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_online_payment_invoices', function (Blueprint $table) {
            $table->unsignedInteger('member_subscription_id')->nullable()->index();
            $table->foreign('member_subscription_id')->references('id')
                ->on('sw_gym_member_subscription')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_online_payment_invoices', function (Blueprint $table) {
            $table->dropColumn('member_subscription_id');
        });
    }
}
