<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymMemberSubscriptionOption extends GenericModel
{
    protected $table = 'sw_gym_member_subscription_options';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function memberSubscription()
    {
        return $this->belongsTo(GymMemberSubscription::class, 'member_subscription_id');
    }

    public function option()
    {
        return $this->belongsTo(GymSubscriptionOption::class, 'option_id');
    }
}
