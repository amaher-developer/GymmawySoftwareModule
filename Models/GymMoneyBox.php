<?php

namespace Modules\Software\Models;

use App\Modules\Access\Models\User;
use Modules\Generic\Models\GenericModel;
use DateTime;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class GymMoneyBox extends GenericModel
{
    use SoftDeletes;

    protected $dates = [];

    protected $table = 'sw_gym_money_boxes';
    protected $guarded = ['id'];
    protected $appends  = ['operation_name', 'payment_type_name'];
    public static $uploads_path='uploads/gymorders/';
    public static $thumbnails_uploads_path='uploads/gymorders/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    /**
     * Every call site computes `amount_before` by reading GymMoneyBox::latest()
     * and deriving from it, then inserting - two un-locked steps, so concurrent
     * requests can read the same "latest" row and both insert with the same
     * amount_before, breaking the running-balance chain. Rather than touching
     * every call site, this intercepts every *new* row here and overwrites
     * amount_before with a race-free value: it locks a single, stable
     * per-branch balance row (sw_gym_money_box_balances, see GymMoneyBoxBalance)
     * with lockForUpdate() inside a transaction, so concurrent inserts for the
     * same branch are serialized instead of racing. Existing rows (updates,
     * restores) are untouched - only fresh inserts go through this path.
     *
     * Retroactive edits (delete/restore/rebuild/audit-fix) that change the
     * chain's tail must resync the balance row too - see
     * GymMoneyBoxFrontController::rebuildMoneyboxFromId().
     */
    public function save(array $options = [])
    {
        if ($this->exists) {
            return parent::save($options);
        }

        return DB::transaction(function () use ($options) {
            $branchId = $this->branch_setting_id ?? static::getCurrentBranchId();

            $balance = GymMoneyBoxBalance::where('branch_setting_id', $branchId)->lockForUpdate()->first();

            if (!$balance) {
                try {
                    GymMoneyBoxBalance::create(['branch_setting_id' => $branchId, 'amount' => 0]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Another concurrent request already created it - fall through and lock it below.
                }
                $balance = GymMoneyBoxBalance::where('branch_setting_id', $branchId)->lockForUpdate()->first();
            }

            $this->branch_setting_id = $branchId;
            $this->amount_before = round((float) $balance->amount, 2);

            $result = parent::save($options);

            $balance->amount = (int) $this->operation === 0
                ? round($balance->amount + round((float) $this->amount, 2), 2)
                : round($balance->amount - round((float) $this->amount, 2), 2);
            $balance->save();

            return $result;
        });
    }

    public function user()
    {
        return $this->belongsTo(GymUser::class, 'user_id');
    }
    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    public function pay_type(){
        return $this->belongsTo(GymPaymentType::class, 'payment_type', 'payment_id');
    }
    public function member_subscription(){
        return $this->belongsTo(GymMemberSubscription::class, 'member_subscription_id');
    }
    public function non_member_subscription(){
        return $this->belongsTo(GymNonMember::class, 'non_member_subscription_id');
    }
    public function store_order(){
        return $this->belongsTo(GymStoreOrder::class, 'store_order_id');
    }
    public function member_pt_subscription(){
        return $this->belongsTo(GymPTMember::class, 'member_pt_subscription_id');
    }

    public function user_transaction(){
        return $this->belongsTo(GymUserTransaction::class, 'user_transaction_id');
    }

    public function loyaltyRedemption()
    {
        return $this->hasOneThrough(
            LoyaltyTransaction::class,
            GymStoreOrder::class,
            'id', // Foreign key on store_orders table
            'source_id', // Foreign key on loyalty_transactions table
            'store_order_id', // Local key on money_boxes table
            'id' // Local key on store_orders table
        )->where('sw_loyalty_transactions.source_type', 'store_order_redemption')
         ->where('sw_loyalty_transactions.type', 'redeem');
    }

    public function swInvoice()
    {
        return $this->hasOne(\Modules\Billing\Models\SwBillingInvoice::class, 'money_box_id');
    }

    /** Invoice this payment entry is linked to. */
    public function invoice()
    {
        return $this->belongsTo(GymSwInvoice::class, 'invoice_id');
    }

    public function getOperationNameAttribute()
    {
        $operation = $this->getRawOriginal('operation');
        if($operation == 0){
            return '<i class="fa fa-plus-circle text-success"></i> '.trans('sw.addition');
        }else if($operation == 1){
            return '<i class="fa fa-minus-circle text-danger"></i> '.trans('sw.withdraw');
        }else if($operation == 2){
            return '<i class="fa fa-minus-circle text-success"></i> '.trans('sw.withdraw_earning');
        }
    }

    public function getPaymentTypeNameAttribute()
    {
        return $this->pay_type->name ?? '';
    }

    public function toArray()
    {
        return parent::toArray();
        $to_array_attributes = [];
        foreach ($this->relations as $key => $relation) {
            $to_array_attributes[$key] = $relation;
        }
        foreach ($this->appends as $key => $append) {
            $to_array_attributes[$key] = $append;
        }
        return $to_array_attributes;
    }

}

