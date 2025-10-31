<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymPTTrainer extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_pt_trainers';
    protected $guarded = ['id'];
    protected $appends = ['image_name'];
    public static $uploads_path='uploads/trainers/';
    public static $thumbnails_uploads_path='uploads/trainers/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }
    public function getImageNameAttribute()
    {
        return $this->getRawOriginal('image');
    }

    public function getImageAttribute()
    {
        $image = $this->getRawOriginal('image');
        if($image)
            return asset(self::$uploads_path.$image);

        return asset('resources/assets/front/img/blank-image.svg');
    }
    public function pt_subscriptions()
    {
        return $this->belongsToMany(GymPTSubscription::class,'sw_gym_pt_subscription_trainer', 'pt_trainer_id', 'pt_subscription_id')->withTimestamps();
    }
    public function pt_subscription_trainer()
    {
        return $this->hasMany(GymPTSubscriptionTrainer::class, 'pt_trainer_id');
    }
    public function pt_members()
    {
        return $this->hasMany(GymPTMember::class, 'pt_trainer_id');
    }
    public function pt_members_trainer_amount_status_false()
    {
        return $this->hasMany(GymPTMember::class, 'pt_trainer_id')->where('trainer_amount_status', 0);
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
