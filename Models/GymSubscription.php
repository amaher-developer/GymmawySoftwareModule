<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use App\Modules\Gym\Models\GymBrand;
use Illuminate\Database\Eloquent\SoftDeletes;
use PhpParser\Builder\Class_;

class GymSubscription extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_subscriptions';
    protected $guarded = ['id'];
    protected $casts = [
        'is_system' => 'boolean',
        'time_week' => 'json',
    ];

    public function scopeIsSystem($query)
    {
        return $query->where('is_system', true);
    }
    protected $appends = ['name', 'content', 'image_name'];
    public static $uploads_path='uploads/subscriptions/';
    public static $thumbnails_uploads_path='uploads/subscriptions/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }
    public function getNameAttribute()
    {
        $lang = 'name_'. $this->lang;
        return $this->$lang;
    }
    public function getContentAttribute()
    {
        $lang = 'content_'. $this->lang;
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
            return asset('resources/assets/new_front/img/blank-image.svg');
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
            $absolutePath = asset($relativePath);

            if (file_exists($absolutePath)) {
                return asset($relativePath);
            }
            return asset($relativePath);
        }

        return asset('resources/assets/new_front/img/blank-image.svg');
    }

    public function member_subscriptions(){
        return $this->hasMany(GymMemberSubscription::class, 'subscription_id');
    }

    public function category(){
        return $this->belongsTo(GymSubscriptionCategory::class, 'subscription_category_id');
    }
    
    public function subscription_category(){
        return $this->belongsTo(GymSubscriptionCategory::class, 'subscription_category_id');
    }

    public function activities(){
        return $this->hasMany(GymActivitySubscription::class, 'subscription_id');
    }

//    public function gym()
//    {
//        return $this->belongsTo(GymBrand::class, 'gym_id');
//    }

    public function members()
    {
        return $this->belongsToMany(GymMember::class,'sw_gym_member_subscription', 'subscription_id', 'member_id')->withPivot('member_id')->withTimestamps();
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

