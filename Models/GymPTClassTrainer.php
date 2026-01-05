<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Support\Facades\Schema;

class GymPTClassTrainer extends GenericModel
{
    protected $table = 'sw_gym_pt_class_trainers';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'session_count' => 'integer',
        'commission_rate' => 'float',
        'schedule' => 'array',
        'date_from' => 'date',
        'date_to' => 'date',
    ];

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function class()
    {
        return $this->belongsTo(GymPTClass::class, 'class_id');
    }

    public function trainer()
    {
        return $this->belongsTo(GymPTTrainer::class, 'trainer_id');
    }

    public function members()
    {
        return $this->hasMany(GymPTMember::class, 'class_trainer_id');
    }

}


