<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymCategory extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_categories';
    protected $guarded = ['id'];
    protected $appends = ['name'];

    public static $uploads_path='uploads/categories/';
    public static $thumbnails_uploads_path='uploads/categories/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function getNameAttribute()
    {
        $lang = 'name_'. $this->lang;
        return $this->$lang;
    }
    public function subscriptions(){
        return $this->hasMany(GymSubscription::class, 'category_id');
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

