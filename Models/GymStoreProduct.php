<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Modules\Software\Classes\TypeConstants;
use Milon\Barcode\DNS1D;
use Illuminate\Support\Facades\Log;

class GymStoreProduct extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_store_products';
    protected $guarded = ['id'];
    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function scopeIsSystem($query)
    {
        return $query->where('is_system', true);
    }
    protected $appends = ['name', 'image_name', 'content'];
    public static $uploads_path='uploads/products/';
    public static $thumbnails_uploads_path='uploads/products/thumbnails/';


    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function order_product()
    {
        return $this->hasMany(GymStoreOrderProduct::class, 'product_id');
    }
    
    public function category(){
        // Support both column names for backward compatibility
        $foreignKey = isset($this->attributes['store_category_id']) ? 'store_category_id' : 'category_id';
        return $this->belongsTo(GymStoreCategory::class, $foreignKey);
    }
    
    public function store_category(){
        // Support both column names for backward compatibility
        $foreignKey = isset($this->attributes['store_category_id']) ? 'store_category_id' : 'category_id';
        return $this->belongsTo(GymStoreCategory::class, $foreignKey);
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
            if (@env('APP_WEBSITE')) {
                return @env('APP_WEBSITE') . @env('APP_URL_ASSETS') . 'placeholder_black.png';
            }
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

        if (@env('APP_WEBSITE')) {
            return @env('APP_WEBSITE') . @env('APP_URL_ASSETS') . 'placeholder_black.png';
        }

        return asset('resources/assets/new_front/img/blank-image.svg');
    }

    public function getBarcodeImageAttribute()
    {
        $value = $this->code;
        if (!$value) {
            return null;
        }

        try {
            $generator = new DNS1D();
            $barcode = $generator->getBarcodePNG((string)$value, TypeConstants::BarcodeType, 2, 60);

            return $barcode ? 'data:image/png;base64,' . $barcode : null;
        } catch (\Throwable $exception) {
            Log::error('Failed to render store product barcode', [
                'product_id' => $this->id,
                'barcode_value' => $value,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }
    public function getContentAttribute()
    {
        $lang = 'content_'. $this->lang;
        return $this->$lang;
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

