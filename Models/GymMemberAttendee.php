<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GymMemberAttendee extends GenericModel
{

    protected $dates = [];
    protected $guarded = [];

    protected $table = 'sw_gym_member_attendees';
    public static $uploads_path='uploads/members/';
    public static $thumbnails_uploads_path='uploads/members/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }
    public function member(){
        return $this->belongsTo(GymMember::class, 'member_id');
    }
    public function user(){
        return $this->belongsTo(GymUser::class, 'user_id');
    }
    public function member_subscription(){
        return $this->belongsTo(GymMemberSubscription::class, 'subscription_id');
    }
    public function pt_member_subscription(){
        return $this->belongsTo(GymPTMember::class, 'pt_subscription_id');
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

