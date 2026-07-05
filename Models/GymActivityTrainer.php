<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymActivityTrainer extends GenericModel
{
    protected $table = 'sw_gym_activity_trainers';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'schedule' => 'array',
        'reservation_limit' => 'integer',
    ];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function activity()
    {
        return $this->belongsTo(GymActivity::class, 'activity_id');
    }

    public function trainer()
    {
        return $this->belongsTo(GymPTTrainer::class, 'trainer_id')->withTrashed();
    }
}
