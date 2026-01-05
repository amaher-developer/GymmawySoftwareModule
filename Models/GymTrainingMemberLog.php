<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class GymTrainingMemberLog extends GenericModel
{
    use SoftDeletes;
use Illuminate\Support\Facades\Schema;
    
    protected $table = 'sw_gym_training_member_logs';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'branch_setting_id',
        'member_id',
        'training_id',
        'training_type',
        'action',
        'notes',
        'reference_id',
        'meta',
        'created_by',
    ];

    /**
     * Get the member
     */
    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    /**
     * Get the user who created the log
     */
    public function creator()
    {
        return $this->belongsTo(GymUser::class, 'created_by');
    }

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
}


