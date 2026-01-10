<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Support\Facades\Schema;
use Modules\Software\Classes\TypeConstants;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymTrainingPlan extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_training_plans';
    protected $guarded = ['id'];
    protected $appends = ['type_name'];
    public static $uploads_path='uploads/gymtrainingmembers/';
    public static $thumbnails_uploads_path='uploads/gymtrainingmembers/thumbnails/';

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
        // Automatically set tenant_id and branch_setting_id when creating
        static::creating(function ($model) {
            $user = parent::getCurrentSwUser();
            if ($user) {
                if (!isset($model->branch_setting_id)) {
                    $model->branch_setting_id = $user->branch_setting_id ?? 1;
                }
                if (!isset($model->tenant_id) && Schema::hasColumn($model->getTable(), 'tenant_id')) {
                    $model->tenant_id = $user->tenant_id ?? 1;
                }
            }
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
    public function getTypeNameAttribute($key)
    {
        if($this->getRawOriginal('type') == TypeConstants::DIET_PLAN_TYPE)
            return trans('sw.plan_diet');
        else
            return trans('sw.plan_training');
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

    /**
     * Get the tasks for the training plan
     */
    public function tasks()
    {
        return $this->hasMany(GymTrainingTask::class, 'plan_id', 'id');
    }

}

