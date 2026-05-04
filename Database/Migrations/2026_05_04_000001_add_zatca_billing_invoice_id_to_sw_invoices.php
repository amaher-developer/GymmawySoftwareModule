<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gym_sw_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('zatca_billing_invoice_id')->nullable()->after('pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('gym_sw_invoices', function (Blueprint $table) {
            $table->dropColumn('zatca_billing_invoice_id');
        });
    }
};
