<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymOnlinePaymentInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_online_payment_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->unsignedInteger('member_id')->index()->nullable();
            $table->foreign('member_id')->references('id')
                ->on('sw_gym_members')
                ->onDelete('cascade');
            $table->unsignedInteger('subscription_id')->index()->nullable();
            $table->foreign('subscription_id')->references('id')
                ->on('sw_gym_subscriptions')
                ->onDelete('cascade');

            $table->string('payment_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->integer('status')->nullable();
            $table->integer('payment_method')->nullable(); // tabbt or mada or visa
            $table->float('amount')->nullable();
            $table->string('vat')->nullable();
            $table->integer('vat_percentage')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->integer('gender')->nullable();
            $table->date('dob')->nullable();
            $table->jsonb('response_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('sw_gym_online_payment_invoices');

    }
}
