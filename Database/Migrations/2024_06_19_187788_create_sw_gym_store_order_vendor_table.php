<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymStoreOrderVendorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_store_order_vendor', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->unsignedInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')
                ->on('sw_gym_store_products')
                ->onDelete('cascade');

            $table->integer('quantity')->default(0)->nullable();

            $table->text('notes')->nullable();
            $table->integer('payment_method')->nullable(); // tabbt or mada or visa
            $table->float('amount')->nullable();

            $table->string('vendor_name')->nullable();
            $table->string('vendor_phone')->nullable();
            $table->string('vendor_address')->nullable();

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
