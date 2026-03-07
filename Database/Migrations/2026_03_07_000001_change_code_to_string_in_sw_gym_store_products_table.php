<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCodeToStringInSwGymStoreProductsTable extends Migration
{
    public function up()
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->string('code', 50)->change();
        });
    }

    public function down()
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->unsignedBigInteger('code')->change();
        });
    }
}
