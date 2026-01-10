<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymTrainingNotification extends GenericModel
{
    use SoftDeletes;
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

    
    protected $table = 'sw_gym_training_notifications';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    /**
     * Get the member
     */
    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(GymUser::class, 'user_id');
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 0);
    }
}


