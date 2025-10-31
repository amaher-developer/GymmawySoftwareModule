<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymStoreCategory extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_store_categories';
    protected $guarded = ['id'];
    protected $appends = ['name', 'image_url'];

    public static $uploads_path='uploads/store_categories/';
    public static $thumbnails_uploads_path='uploads/store_categories/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
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
        
        return asset('resources/assets/front/img/blank-image.svg');
    }
    
    public function products(){
        return $this->hasMany(GymStoreProduct::class, 'store_category_id');
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

