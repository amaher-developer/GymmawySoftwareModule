<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymMemberSubscriptionFreeze extends GenericModel
{
    protected $table = 'sw_gym_member_subscription_freezes';
    protected $guarded = ['id'];

    public function memberSubscription()
    {
        return $this->belongsTo(GymMemberSubscription::class, 'member_subscription_id');
    }
}


