<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymReservationUsage extends GenericModel
{
    protected $table = 'sw_gym_reservation_usage';

    protected $fillable = [
        'reservation_id',
        'client_type',
        'member_id',
        'non_member_id',
        'activity_id',
        'staff_id',
        'used_at',
        'notes',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];
}

