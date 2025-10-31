<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveSolidQuantityToSwGymStoreProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropColumn('solid_quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_store_orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sw_gym_store_products` ADD `solid_quantity` INT(11) NOT NULL DEFAULT '0' AFTER `quantity`;");
        });
    }
}
