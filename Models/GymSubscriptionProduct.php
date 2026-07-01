<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymSubscriptionProduct extends GenericModel
{
    protected $table = 'sw_gym_subscription_products';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function subscription()
    {
        return $this->belongsTo(GymSubscription::class, 'subscription_id');
    }

    public function product()
    {
        return $this->belongsTo(GymStoreProduct::class, 'product_id');
    }
}
