<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_sw_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['sales', 'purchase', 'credit_note']);
            $table->string('invoice_number', 20)->unique();
            $table->unsignedInteger('sequence_number');
            $table->string('prefix', 10);

            $table->unsignedInteger('branch_setting_id')->nullable()->index();
            $table->foreign('branch_setting_id')
                ->references('id')->on('settings')
                ->onDelete('set null');

            $table->unsignedInteger('member_id')->nullable()->index();
            $table->unsignedInteger('supplier_id')->nullable()->index();

            $table->unsignedBigInteger('reference_invoice_id')->nullable()->index();
            $table->foreign('reference_invoice_id')
                ->references('id')->on('gym_sw_invoices')
                ->onDelete('set null');

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(14.00);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);

            $table->string('pdf_path', 255)->nullable();
            $table->enum('status', ['draft', 'partial', 'paid', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();

            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_sw_invoices');
    }
};
