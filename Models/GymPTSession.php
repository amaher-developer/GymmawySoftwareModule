<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymPTSession extends GenericModel
{
    protected $table = 'sw_gym_pt_sessions';

    protected $guarded = ['id'];

    protected $casts = [
        'session_date' => 'datetime',
    ];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function class()
    {
        return $this->belongsTo(GymPTClass::class, 'class_id');
    }

    public function classTrainer()
    {
        return $this->belongsTo(GymPTClassTrainer::class, 'class_trainer_id');
    }

    public function trainer()
    {
        return $this->belongsTo(GymPTTrainer::class, 'trainer_id');
    }

    public function attendees()
    {
        return $this->hasMany(GymPTMemberAttendee::class, 'session_id');
    }

    public function commissions()
    {
        return $this->hasMany(GymPTCommission::class, 'session_id');
    }
}


