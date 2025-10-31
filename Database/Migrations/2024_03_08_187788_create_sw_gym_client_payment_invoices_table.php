<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymClientPaymentInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_sw_client_payment_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->unsignedInteger('subscription_id')->index()->nullable();
            $table->foreign('subscription_id')->references('id')
                ->on('sw_gym_subscriptions')
                ->onDelete('cascade');
            $table->integer('status')->nullable();
            $table->integer('payment_method')->nullable(); // tabbt or mada or visa
            $table->integer('duration')->nullable(); // tabbt or mada or visa
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

        Schema::dropIfExists('sw_gym_sw_client_payment_invoices');

    }
}
