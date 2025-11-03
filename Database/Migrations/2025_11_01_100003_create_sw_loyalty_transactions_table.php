<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration to create loyalty transactions table
 * 
 * This table records all points movements (earn/redeem/manual adjustments)
 */
class CreateSwLoyaltyTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_loyalty_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Member association
            $table->unsignedInteger('member_id')->index();
            $table->foreign('member_id')->references('id')
                ->on('sw_gym_members')
                ->onDelete('cascade');

            // Rule association (optional - may not apply to manual transactions)
            $table->unsignedInteger('rule_id')->nullable();
            $table->foreign('rule_id')->references('id')
                ->on('sw_loyalty_point_rules')
                ->onDelete('set null');

            // Campaign association (optional - only when campaign is active)
            $table->unsignedInteger('campaign_id')->nullable();
            $table->foreign('campaign_id')->references('id')
                ->on('sw_loyalty_campaigns')
                ->onDelete('set null');

            // Points and transaction details
            $table->integer('points')->comment('Positive for earn/add, negative for redeem/deduct');
            
            $table->enum('type', ['earn', 'redeem', 'manual'])->default('earn')
                ->comment('Transaction type: earn (from payment), redeem (discount), manual (admin adjustment)');

            // Source tracking (polymorphic - can be subscription, order, etc.)
            $table->string('source_type')->nullable()
                ->comment('Type of source (e.g., "subscription", "order", "manual")');
            
            $table->unsignedBigInteger('source_id')->nullable()
                ->comment('ID of the source record');

            // Additional information
            $table->text('reason')->nullable()
                ->comment('Reason for transaction (especially for manual adjustments)');

            $table->decimal('amount_spent', 10, 2)->nullable()
                ->comment('Amount of money spent that generated these points (for earn type)');

            // Expiry tracking
            $table->dateTime('expires_at')->nullable()
                ->comment('When these points expire (null = never)');

            $table->boolean('is_expired')->default(false)
                ->comment('Whether these points have been marked as expired');

            // Admin tracking (for manual transactions)
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')
                ->on('sw_gym_users')
                ->onDelete('set null');

            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['member_id', 'type']);
            $table->index(['member_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sw_loyalty_transactions');
    }
}

