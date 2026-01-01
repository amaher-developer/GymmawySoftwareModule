<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymActivity extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_activities';
    protected $guarded = ['id'];
    protected $casts = [
        'is_system' => 'boolean',
        'reservation_details' => 'json',
    ];

    public function scopeIsSystem($query)
    {
        return $query->where('is_system', true);
    }
    protected $appends = ['name', 'content', 'image_name'];

    public static $uploads_path='uploads/activities/';
    public static $thumbnails_uploads_path='uploads/activities/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
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
            return asset('resources/assets/new_front/img/blank-image.svg');
        }

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        $normalized = str_replace('\\', '/', $image);

        if (str_starts_with($normalized, '/')) {
            return asset(ltrim($normalized, '/'));
        }

        $basename = basename($normalized);
        if ($basename && $basename !== '.' && $basename !== '..') {
            $relativePath = self::$uploads_path.$basename;
            $absolutePublicPath = asset($relativePath);
            $absoluteBasePath = base_path($relativePath);

            if (file_exists($absolutePublicPath) || file_exists($absoluteBasePath)) {
                return asset($relativePath);
            }

            return asset($relativePath);
        }

        return asset('resources/assets/new_front/img/blank-image.svg');
    }

    public function getContentAttribute()
    {
        $lang = 'content_'. $this->lang;
        return $this->$lang;
    }

    public function non_member_times()
    {
        return $this->hasMany(GymNonMemberTime::class, 'activity_id');
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

