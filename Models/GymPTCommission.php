<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymPTCommission extends GenericModel
{
    protected $table = 'sw_gym_pt_commissions';

    protected $guarded = ['id'];

    protected $casts = [
        'commission_rate' => 'float',
        'commission_amount' => 'float',
        'paid_at' => 'datetime',
        'session_date' => 'datetime',
    ];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function trainer()
    {
        return $this->belongsTo(GymPTTrainer::class, 'trainer_id');
    }

    public function member()
    {
        return $this->belongsTo(GymPTMember::class, 'pt_member_id');
    }

    public function attendee()
    {
        return $this->belongsTo(GymPTMemberAttendee::class, 'pt_member_attendee_id');
    }

    public function paidByUser()
    {
        return $this->belongsTo(GymUser::class, 'paid_by');
    }
}

