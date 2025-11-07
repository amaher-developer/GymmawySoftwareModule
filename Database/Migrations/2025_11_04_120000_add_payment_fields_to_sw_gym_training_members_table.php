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
        if (Schema::hasTable('sw_gym_training_members')) {
            Schema::table('sw_gym_training_members', function (Blueprint $table) {
                if (!Schema::hasColumn('sw_gym_training_members', 'price')) {
                    $table->decimal('price', 10, 2)->nullable()->after('to_date')->comment('Plan price');
                }
                if (!Schema::hasColumn('sw_gym_training_members', 'discount')) {
                    $table->decimal('discount', 10, 2)->nullable()->after('price')->default(0)->comment('Discount amount');
                }
                if (!Schema::hasColumn('sw_gym_training_members', 'vat_percentage')) {
                    $table->decimal('vat_percentage', 5, 2)->nullable()->after('discount')->default(0)->comment('VAT percentage');
                }
                if (!Schema::hasColumn('sw_gym_training_members', 'vat')) {
                    $table->decimal('vat', 10, 2)->nullable()->after('vat_percentage')->default(0)->comment('VAT amount');
                }
                if (!Schema::hasColumn('sw_gym_training_members', 'total')) {
                    $table->decimal('total', 10, 2)->nullable()->after('vat')->comment('Total amount');
                }
                if (!Schema::hasColumn('sw_gym_training_members', 'amount_paid')) {
                    $table->decimal('amount_paid', 10, 2)->nullable()->after('total')->comment('Amount paid');
                }
                if (!Schema::hasColumn('sw_gym_training_members', 'payment_type')) {
                    $table->tinyInteger('payment_type')->nullable()->after('amount_paid')->comment('0=cash, 1=online, 2=bank');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sw_gym_training_members')) {
            Schema::table('sw_gym_training_members', function (Blueprint $table) {
                if (Schema::hasColumn('sw_gym_training_members', 'price')) {
                    $table->dropColumn('price');
                }
                if (Schema::hasColumn('sw_gym_training_members', 'discount')) {
                    $table->dropColumn('discount');
                }
                if (Schema::hasColumn('sw_gym_training_members', 'vat_percentage')) {
                    $table->dropColumn('vat_percentage');
                }
                if (Schema::hasColumn('sw_gym_training_members', 'vat')) {
                    $table->dropColumn('vat');
                }
                if (Schema::hasColumn('sw_gym_training_members', 'total')) {
                    $table->dropColumn('total');
                }
                if (Schema::hasColumn('sw_gym_training_members', 'amount_paid')) {
                    $table->dropColumn('amount_paid');
                }
                if (Schema::hasColumn('sw_gym_training_members', 'payment_type')) {
                    $table->dropColumn('payment_type');
                }
            });
        }
    }
};

