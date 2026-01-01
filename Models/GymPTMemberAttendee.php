<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymPTMemberAttendee extends GenericModel
{
    protected $table = 'sw_gym_pt_member_attendees';

    protected $guarded = ['id'];

    protected $casts = [
        'attended' => 'boolean',
        'session_date' => 'datetime',
    ];

    public static $uploads_path = 'uploads/members/';
    public static $thumbnails_uploads_path = 'uploads/members/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function pt_member()
    {
        return $this->belongsTo(GymPTMember::class, 'pt_member_id');
    }

    public function user()
    {
        return $this->belongsTo(GymUser::class, 'user_id');
    }

    public function commission()
    {
        return $this->hasOne(GymPTCommission::class, 'pt_member_attendee_id');
    }

}

