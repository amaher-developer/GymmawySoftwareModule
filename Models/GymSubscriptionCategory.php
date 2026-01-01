<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymSubscriptionCategory extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_subscription_categories';
    protected $guarded = ['id'];
    protected $appends = ['name', 'image_url'];

    public static $uploads_path='uploads/subscription_categories/';
    public static $thumbnails_uploads_path='uploads/subscription_categories/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function getNameAttribute()
    {
        $lang = 'name_'. $this->lang;
        return $this->$lang;
    }
    
    public function getImageUrlAttribute()
    {
        $image = $this->getRawOriginal('image');
        if($image)
            return asset(self::$uploads_path.$image);
        
        return asset('resources/assets/new_front/img/blank-image.svg');
    }
    
    public function subscriptions(){
        return $this->hasMany(GymSubscription::class, 'subscription_category_id');
    }
    
    public function pt_subscriptions(){
        return $this->hasMany(GymPTSubscription::class, 'subscription_category_id');
    }
    
    public function activities(){
        return $this->hasMany(GymActivity::class, 'subscription_category_id');
    }
    
    public function training_plans(){
        return $this->hasMany(GymTrainingPlan::class, 'subscription_category_id');
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


