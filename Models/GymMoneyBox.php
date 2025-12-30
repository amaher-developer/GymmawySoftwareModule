<?php

namespace Modules\Software\Models;

use App\Modules\Access\Models\User;
use Modules\Generic\Models\GenericModel;
use DateTime;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        $payment_type = $this->getRawOriginal('payment_type');
        if($payment_type == 0){
            return trans('sw.payment_cash');
        }else if($payment_type == 1){
            return trans('sw.payment_online');
        }else if($payment_type == 2){
            return trans('sw.payment_bank_transfer');
        }
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

