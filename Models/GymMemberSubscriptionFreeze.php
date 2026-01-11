<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymMemberSubscriptionFreeze extends GenericModel
{
    /**
     * NOTE: This table does NOT have branch_setting_id or tenant_id columns
     * Tenant isolation is achieved through the relationship with GymMemberSubscription
     * which has the branch_setting_id column and global scope.
     *
     * Always query freezes through the memberSubscription relationship to ensure proper tenant isolation.
     */

    protected $table = 'sw_gym_member_subscription_freezes';
    protected $guarded = ['id'];

    public function memberSubscription()
    {
        return $this->belongsTo(GymMemberSubscription::class, 'member_subscription_id');
    }
}



