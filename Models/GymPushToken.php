<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymPushToken extends GenericModel
{
    protected $table = 'sw_gym_push_tokens';
    protected $guarded = [];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }
    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }
}

