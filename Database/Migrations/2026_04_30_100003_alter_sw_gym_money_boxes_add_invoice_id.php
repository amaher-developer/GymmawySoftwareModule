<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->nullable()->after('id')->index();
            $table->foreign('invoice_id')
                ->references('id')->on('gym_sw_invoices')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropIndex(['invoice_id']);
            $table->dropColumn('invoice_id');
        });
    }
};
