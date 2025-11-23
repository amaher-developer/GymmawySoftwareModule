<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymPushNotification extends GenericModel
{
    protected $table = 'sw_gym_push_notifications';
    protected $guarded = [];
    public $casts = ['body' => 'json'];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

}

