<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymNonMemberTime extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_reservations';
    protected $guarded = ['id'];
    protected $appends = [];
    protected $casts = [];

    public static $uploads_path='uploads/activities/';
    public static $thumbnails_uploads_path='uploads/activities/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function non_member()
    {
        return $this->belongsTo(GymNonMember::class, 'non_member_id');
    }
    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }
    public function activity()
    {
        return $this->belongsTo(GymActivity::class, 'activity_id');
    }
    public function user()
    {
        return $this->belongsTo(GymUser::class, 'user_id');
    }


    public function toArray()
    {
        return parent::toArray();
        $to_array_attributes = [];
        foreach ($this->relations as $key => $relation) {
            $to_array_attributes[$key] = $relation;
        }
        foreach ($this->appends as $key => $append) {
            $to_array_attributes[$key] = $append;
        }
        return $to_array_attributes;
    }

}
