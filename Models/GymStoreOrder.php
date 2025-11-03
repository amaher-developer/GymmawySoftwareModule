<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymStoreOrder extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_store_orders';
    protected $guarded = ['id'];
    protected $appends = [];
    protected $casts = ['products' => 'json'];
    public static $uploads_path='uploads/products/';
    public static $thumbnails_uploads_path='uploads/products/thumbnails/';


    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }

    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    public function order_product()
    {
        return $this->hasMany(GymStoreOrderProduct::class, 'order_id');
    }

    public function pay_type(){
        return $this->belongsTo(GymPaymentType::class, 'payment_type', 'payment_id');
    }

    public function loyaltyRedemption()
    {
        return $this->hasOne(LoyaltyTransaction::class, 'source_id')
            ->where('source_type', 'store_order_redemption')
            ->where('type', 'redeem');
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
