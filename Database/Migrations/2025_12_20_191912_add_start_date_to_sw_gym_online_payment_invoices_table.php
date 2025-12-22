<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sw_gym_online_payment_invoices', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sw_gym_online_payment_invoices', function (Blueprint $table) {
            $table->dropColumn('start_date');
        });
    }
};
