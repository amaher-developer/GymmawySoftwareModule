<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class ConvertMoneyBoxTablesToInnodb extends Migration
{
    /**
     * sw_gym_money_boxes predates this app being explicit about storage
     * engine, and on servers whose default engine is MyISAM (this one
     * included) it was created as MyISAM - which has no transactions and no
     * row-level locking. GymMoneyBox::createWithBalance() relies on
     * lockForUpdate() inside a DB transaction to serialize concurrent writes
     * to the running-balance chain; on MyISAM that call is a silent no-op,
     * so the chain-break race this migration exists to close would otherwise
     * still happen. sw_gym_money_box_balances is included too in case its
     * own create-table migration ran before this one on a MyISAM-default
     * server (its migration was updated to declare InnoDB directly, but this
     * covers environments where it already exists).
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE sw_gym_money_boxes ENGINE=InnoDB');
        DB::statement('ALTER TABLE sw_gym_money_box_balances ENGINE=InnoDB');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE sw_gym_money_boxes ENGINE=MyISAM');
        DB::statement('ALTER TABLE sw_gym_money_box_balances ENGINE=MyISAM');
    }
}
