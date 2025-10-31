<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymStoreOrderProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_store_order_product', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id')->nullable()->index();
            $table->foreign('product_id')->references('id')
                ->on('sw_gym_store_products')
                ->onDelete('cascade');
            $table->unsignedInteger('order_id')->index();
            $table->foreign('order_id')->references('id')
                ->on('sw_gym_store_orders')
                ->onDelete('cascade');

            $table->smallInteger('quantity')->default(0);
            $table->double('price')->default(0);

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
        Schema::dropIfExists('sw_gym_store_order_product');
    }
}
