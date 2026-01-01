<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GymEventNotification extends GenericModel
{

    protected $dates = [];
    protected $guarded = [];

    protected $table = 'sw_gym_event_notifications';
    public static $uploads_path='uploads/settings/';
    public static $thumbnails_uploads_path='uploads/settings/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function getTitleAttribute()
    {
        $lang = 'title_'. $this->lang;
        return $this->$lang;
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

