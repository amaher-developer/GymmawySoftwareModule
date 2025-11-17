<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymReservation extends GenericModel
{
    protected $table = 'sw_gym_reservations';

    protected $fillable = [
        'client_type',
        'member_id',
        'non_member_id',
        'activity_id',
        'reservation_date',
        'start_time',
        'end_time',
        'status',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'reservation_date' => 'date:Y-m-d',
        'cancelled_at'     => 'datetime',
    ];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }

    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id')->withTrashed();
    }

    public function nonMember()
    {
        return $this->belongsTo(GymNonMember::class, 'non_member_id')->withTrashed();
    }

    public function activity()
    {
        return $this->belongsTo(GymActivity::class, 'activity_id')->withTrashed();
    }
}
