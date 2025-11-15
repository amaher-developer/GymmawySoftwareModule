<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymPTSubscription extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_pt_subscriptions';
    protected $guarded = ['id'];
    protected $appends = ['name', 'image_name'];
    public static $uploads_path='uploads/subscriptions/';
    public static $thumbnails_uploads_path='uploads/subscriptions/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }
    public function getNameAttribute()
    {
        $lang = 'name_'. $this->lang;
        return $this->$lang;
    }
    public function getImageNameAttribute()
    {
        return $this->getRawOriginal('image');
    }
    public function getImageAttribute()
    {
        $image = $this->getRawOriginal('image');
        if (!$image) {
            return asset('resources/assets/front/img/blank-image.svg');
        }

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        $normalized = str_replace('\\', '/', $image);

        if (str_starts_with($normalized, '/')) {
            return asset(ltrim($normalized, '/'));
        }

        if (str_starts_with($normalized, self::$uploads_path)) {
            return asset($normalized);
        }

        $basename = basename($normalized);
        if ($basename && $basename !== '.' && $basename !== '..') {
            $relativePath = self::$uploads_path.$basename;
            $absolutePublicPath = public_path($relativePath);
            $absoluteBasePath = base_path($relativePath);

            if (file_exists($absolutePublicPath) || file_exists($absoluteBasePath)) {
                return asset($relativePath);
            }

            return asset($relativePath);
        }

        return asset('resources/assets/front/img/blank-image.svg');
    }

    public function classes()
    {
        return $this->hasMany(GymPTClass::class, 'pt_subscription_id');
    }

    public function pt_classes()
    {
        return $this->classes();
    }

    public function pt_members()
    {
        return $this->hasMany(GymPTMember::class, 'member_id');
    }

    public function classTrainers()
    {
        return $this->hasManyThrough(
            GymPTClassTrainer::class,
            GymPTClass::class,
            'pt_subscription_id',
            'class_id'
        );
    }

    public function assignedTrainers()
    {
        return $this->classTrainers()->with('trainer')->get()->pluck('trainer')->filter()->unique('id')->values();
    }

    public function pt_trainers()
    {
        return $this->belongsToMany(
            GymPTTrainer::class,
            'sw_gym_pt_subscription_trainer',
            'pt_subscription_id',
            'pt_trainer_id'
        )->withTimestamps();
    }
    
    public function getTrainerAttribute()
    {
        return $this->pt_trainers()->first();
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

}
