<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_sw_invoice_sequences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['sales', 'purchase', 'credit_note']);
            $table->string('prefix', 10);
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        DB::table('gym_sw_invoice_sequences')->insert([
            ['type' => 'sales',       'prefix' => 'INV',  'last_number' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'purchase',    'prefix' => 'PINV', 'last_number' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'credit_note', 'prefix' => 'CN',   'last_number' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_sw_invoice_sequences');
    }
};
