<?php

namespace Modules\Software\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per branch holding the current running money-box balance.
 * Locked via lockForUpdate() in GymMoneyBox::createWithBalance() to serialize
 * concurrent writes to the running-balance chain. Plain Eloquent Model (not
 * GenericModel) - no soft deletes, no branch/lang concerns, just a lock target.
 */
class GymMoneyBoxBalance extends Model
{
    protected $table = 'sw_gym_money_box_balances';
    protected $guarded = ['id'];
}
