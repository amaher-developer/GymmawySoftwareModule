<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymPaymentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_payment_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('payment_id')->nullable();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('image')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        \Modules\Software\Models\GymPaymentType::insert(['payment_id' => 0, 'name_ar'=> 'دفع نقدي', 'name_en' => 'Cash']);
        \Modules\Software\Models\GymPaymentType::insert(['payment_id' => 1, 'name_ar'=> 'دفع اليكتروني', 'name_en' => 'Online']);
        \Modules\Software\Models\GymPaymentType::insert(['payment_id' => 2, 'name_ar'=> 'تحويل بنكي', 'name_en' => 'Bank Transfer']);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('sw_gym_payment_types');

    }
}
