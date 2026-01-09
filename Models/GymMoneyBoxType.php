<?php

namespace Modules\Software\Models;

use App\Modules\Access\Models\User;
use Modules\Generic\Models\GenericModel;
use DateTime;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class GymMoneyBoxType extends GenericModel
{

    protected $dates = [];

    protected $table = 'sw_gym_money_box_types';
    protected $guarded = ['id'];
    protected $appends  = ['name'];
    public static $uploads_path='uploads/gymorders/';
    public static $thumbnails_uploads_path='uploads/gymorders/thumbnails/';

    // This is a shared lookup table - no branch/tenant scoping needed
    // public static function booted()
    // {
    //     static::addGlobalScope('branch', function ($query) {
    //         $branchId = parent::getCurrentBranchId();
    //         $query->where('branch_setting_id', $branchId);
    //     });
    // }

    /**
     * Manual branch and tenant scope (no-op for this shared table)
     * This table doesn't have branch_setting_id or tenant_id columns
     * It's a shared lookup table across all branches/tenants
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $branchId - Default: 1
     * @param int $tenantId - Default: 1
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBranch($query, $branchId = 1, $tenantId = 1)
    {
        // No-op: This is a shared lookup table
        // Don't filter by branch_setting_id or tenant_id
        return $query;
    }

    public function getNameAttribute()
    {
        $lang = 'name_'. $this->lang;
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

