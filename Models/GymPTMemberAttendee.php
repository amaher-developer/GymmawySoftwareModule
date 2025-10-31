<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GymPTMemberAttendee extends GenericModel
{

    protected $dates = [];
    protected $fillable = ['user_id', 'pt_member_id'];
    protected $table = 'sw_gym_pt_member_attendees';
    public static $uploads_path='uploads/members/';
    public static $thumbnails_uploads_path='uploads/members/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }
    public function pt_member(){
        return $this->belongsTo(GymPTMember::class, 'pt_member_id');
    }
    public function user(){
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
