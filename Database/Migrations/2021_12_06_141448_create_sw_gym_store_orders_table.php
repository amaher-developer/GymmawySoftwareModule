<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymStoreOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_store_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('member_id')->nullable()->index();
            $table->foreign('member_id')->references('id')
                ->on('sw_gym_members')
                ->onDelete('cascade');
            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')
                ->on('sw_gym_users')
                ->onDelete('cascade');


            $table->jsonb('products')->nullable();
            $table->string('price')->default(0);

            $table->integer('amount_paid')->default(0)->nullable();
            $table->integer('amount_remaining')->default(0);
            $table->integer('amount_before_discount')->default(0);
            $table->integer('discount_value')->default(0);
            $table->tinyInteger('discount_type')->default(1)->nullable();

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
        Schema::dropIfExists('sw_gym_store_orders');
    }
}
