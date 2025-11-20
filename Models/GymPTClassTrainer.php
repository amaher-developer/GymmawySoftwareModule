<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymPTClassTrainer extends GenericModel
{
    protected $table = 'sw_gym_pt_class_trainers';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'session_count' => 'integer',
        'commission_rate' => 'float',
        'schedule' => 'array',
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function class()
    {
        return $this->belongsTo(GymPTClass::class, 'class_id');
    }

    public function trainer()
    {
        return $this->belongsTo(GymPTTrainer::class, 'trainer_id');
    }

    public function members()
    {
        return $this->hasMany(GymPTMember::class, 'class_trainer_id');
    }

}

