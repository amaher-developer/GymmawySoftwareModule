<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Modules\Software\Classes\TypeConstants;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymTrainingPlan extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_training_plans';
    protected $guarded = ['id'];
    protected $appends = ['type_name'];
    public static $uploads_path='uploads/gymtrainingmembers/';
    public static $thumbnails_uploads_path='uploads/gymtrainingmembers/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }
    public function getTypeNameAttribute($key)
    {
        if($this->getRawOriginal('type') == TypeConstants::DIET_PLAN_TYPE)
            return trans('sw.plan_diet');
        else
            return trans('sw.plan_training');
    }
    
    public function category(){
        return $this->belongsTo(GymSubscriptionCategory::class, 'subscription_category_id');
    }
    
    public function subscription_category(){
        return $this->belongsTo(GymSubscriptionCategory::class, 'subscription_category_id');
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

    /**
     * Get the tasks for the training plan
     */
    public function tasks()
    {
        return $this->hasMany(GymTrainingTask::class, 'plan_id', 'id');
    }

}
