<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymMoneyBoxTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_money_box_types', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('payment_type')->default(0)->nullable();
            $table->smallInteger('operation_type')->default(0)->nullable();
            $table->string('name_ar');
            $table->string('name_en');

            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('sw_gym_money_box_types')->insert([
            ['name_ar' => 'سند قبض', 'name_en' => 'Cash receipt', 'operation_type' => \Modules\Software\Classes\TypeConstants::Add, 'payment_type' => \Modules\Software\Classes\TypeConstants::CASH_RECEIPT],
            ['name_ar' => 'كمبيالة', 'name_en' => 'Promissory Receipt', 'operation_type' => \Modules\Software\Classes\TypeConstants::Add, 'payment_type' => \Modules\Software\Classes\TypeConstants::PROMISSORY_RECEIPT],
            ['name_ar' => 'التأمين', 'name_en' => 'Insurance Receipt', 'operation_type' => \Modules\Software\Classes\TypeConstants::Add, 'payment_type' => \Modules\Software\Classes\TypeConstants::INSURANCE_PAYMENT],
            ['name_ar' => 'مصروفات', 'name_en' => 'Expenses', 'operation_type' => \Modules\Software\Classes\TypeConstants::Sub, 'payment_type' => \Modules\Software\Classes\TypeConstants::EXPENSES_RECEIPT],
            ['name_ar' => 'تحويل من الصندوق للبنك', 'name_en' => 'Transfer from the fund to the bank', 'operation_type' => \Modules\Software\Classes\TypeConstants::Sub, 'payment_type' => \Modules\Software\Classes\TypeConstants::TRANSFER_RECEIPT],
            ['name_ar' => 'ترحيل رصيد البنك', 'name_en' => 'Bank balance transfer', 'operation_type' => \Modules\Software\Classes\TypeConstants::Sub, 'payment_type' => \Modules\Software\Classes\TypeConstants::BALANCE_PAYMENT],
            ['name_ar' => 'مصروفات', 'name_en' => 'Expenses', 'operation_type' => \Modules\Software\Classes\TypeConstants::SubEarning, 'payment_type' => \Modules\Software\Classes\TypeConstants::EXPENSES_RECEIPT],
            ['name_ar' => 'تحويل من الصندوق للبنك', 'name_en' => 'Transfer from the fund to the bank', 'operation_type' => \Modules\Software\Classes\TypeConstants::SubEarning, 'payment_type' => \Modules\Software\Classes\TypeConstants::TRANSFER_RECEIPT],
            ['name_ar' => 'ترحيل رصيد البنك', 'name_en' => 'Bank balance transfer', 'operation_type' => \Modules\Software\Classes\TypeConstants::SubEarning, 'payment_type' => \Modules\Software\Classes\TypeConstants::BALANCE_PAYMENT]
        ]
        );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('sw_gym_money_box_types');

    }
}
