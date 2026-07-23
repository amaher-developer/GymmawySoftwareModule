<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymMoneyBoxBalancesTable extends Migration
{
    /**
     * One locked row per branch holding the current running money-box balance.
     * GymMoneyBox::createWithBalance() locks this row (SELECT ... FOR UPDATE)
     * before computing a new entry's amount_before, so concurrent inserts are
     * serialized instead of racing to read the same "latest" ledger row.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_money_box_balances', function (Blueprint $table) {
            // Explicit InnoDB: this table only exists to be locked with
            // lockForUpdate() inside a transaction (see GymMoneyBox::createWithBalance)
            // - MyISAM has no row locking or transactions, so on a server whose
            // default engine is MyISAM this table would silently provide no
            // concurrency protection at all.
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('branch_setting_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->unique('branch_setting_id');
        });

        // Seed one balance row per branch from the current true tail of its
        // chain (last non-deleted row by created_at, id), so existing branches
        // keep posting from where they already are instead of resetting to 0.
        $branchIds = DB::table('sw_gym_money_boxes')
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('branch_setting_id');

        foreach ($branchIds as $branchId) {
            if ($branchId === null) {
                continue;
            }

            $last = DB::table('sw_gym_money_boxes')
                ->where('branch_setting_id', $branchId)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $amount = 0;
            if ($last) {
                $amount = (int) $last->operation === 0
                    ? round((float) $last->amount_before + (float) $last->amount, 2)
                    : round((float) $last->amount_before - (float) $last->amount, 2);
            }

            DB::table('sw_gym_money_box_balances')->insert([
                'branch_setting_id' => $branchId,
                'amount'            => $amount,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sw_gym_money_box_balances');
    }
}
