<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixCodeUniqueIndexInSwGymStoreProductsTable extends Migration
{
    public function up()
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropUnique('sw_gym_store_products_code_unique');
            $table->unique(['code', 'branch_setting_id'], 'sw_gym_store_products_code_branch_unique');
        });
    }

    public function down()
    {
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropUnique('sw_gym_store_products_code_branch_unique');
            $table->unique('code', 'sw_gym_store_products_code_unique');
        });
    }
}
