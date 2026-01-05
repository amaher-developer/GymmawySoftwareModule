<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Modules\Software\Classes\TypeConstants;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class GymBanner extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_banners';
    protected $guarded = ['id'];
    protected $appends = ['image_name'];
    protected $casts = [];
    public static $uploads_path='uploads/banners/';
    public static $thumbnails_uploads_path='uploads/banners/thumbnails/';

    /**
     * Apply global scope to ALL queries for tenant isolation
     * This prevents IDOR (Insecure Direct Object Reference) attacks
     */
    public static function booted()
    {
        static::addGlobalScope('branch', function ($query) {
            $branchId = parent::getCurrentBranchId();
            $query->where('branch_setting_id', $branchId);
        });
    }

    /**
     * Manual branch and tenant scope
     * Filters by branch_setting_id and optionally tenant_id
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $branchId - Default: 1
     * @param int $tenantId - Default: 1
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBranch($query, $branchId = 1, $tenantId = 1)
    {
        $query->where('branch_setting_id', $branchId);

        // Only filter by tenant_id if the column exists in the table
        if (Schema::hasColumn($this->getTable(), 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
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

