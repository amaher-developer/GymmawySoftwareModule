<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymUserPermission extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_user_permissions';
    protected $guarded = ['id'];
    protected $appends = ['title'];
    protected $casts = ['permissions' => 'json'];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function getTitleAttribute()
    {
        $lang = 'title_'. $this->lang;
        return $this->$lang;
    }

    public function users(){
        return $this->hasMany(GymUser::class, 'permission_group_id');
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


