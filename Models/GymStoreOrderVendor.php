<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymStoreOrderVendor extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_store_order_vendor';
    protected $guarded = ['id'];
    protected $appends = [];
    public static $uploads_path='uploads/products/';
    public static $thumbnails_uploads_path='uploads/products/thumbnails/';


    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function product()
    {
        return $this->belongsTo(GymStoreProduct::class, 'product_id');
    }

    public function pay_type(){
        return $this->belongsTo(GymPaymentType::class, 'payment_type', 'payment_id');
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

