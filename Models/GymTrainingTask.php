<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymTrainingTask extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_training_tasks';
    protected $guarded = ['id'];
    protected $appends = [];
    public static $uploads_path='uploads/gymtrainingmembers/';
    public static $thumbnails_uploads_path='uploads/gymtrainingmembers/thumbnails/';


    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }
//    public function member()
//    {
//        return $this->belongsTo(GymMember::class, 'member_id');
//    }


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
