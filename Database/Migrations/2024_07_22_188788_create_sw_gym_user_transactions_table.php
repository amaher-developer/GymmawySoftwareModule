<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymUserTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_user_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->unsignedInteger('user_id')->index()->default(1)->nullable();
            $table->foreign('user_id')->references('id')
                ->on('sw_gym_users')
                ->onDelete('cascade');


            $table->string('financial_month');
            $table->string('financial_year');
            $table->string('amount')->default(0);
            $table->tinyInteger('operation')->default(0)->nullable();
            $table->tinyInteger('type')->nullable();
            $table->text('note')->nullable();
            $table->string('advance_discount_month')->nullable();
            $table->string('advance_discount_year')->nullable();

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

        Schema::dropIfExists('sw_gym_user_transactions');

    }
}
