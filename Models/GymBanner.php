<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Modules\Software\Classes\TypeConstants;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymBanner extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_banners';
    protected $guarded = ['id'];
    protected $appends = ['image_name'];
    protected $casts = [];
    public static $uploads_path='uploads/banners/';
    public static $thumbnails_uploads_path='uploads/banners/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
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

        return asset('resources/assets/new_front/img/blank-image.svg');
    }
    public function user()
    {
        return $this->belongsTo(GymUser::class, 'user_id');
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

