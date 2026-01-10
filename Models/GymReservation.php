<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Support\Facades\Schema;

class GymReservation extends GenericModel
{
    protected $table = 'sw_gym_reservations';

    protected $fillable = [
        'client_type',
        'member_id',
        'non_member_id',
        'activity_id',
        'reservation_date',
        'start_time',
        'end_time',
        'status',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'reservation_date' => 'date:Y-m-d',
        'cancelled_at'     => 'datetime',
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

    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id')->withTrashed();
    }

    public function nonMember()
    {
        return $this->belongsTo(GymNonMember::class, 'non_member_id')->withTrashed();
    }

    public function activity()
    {
        return $this->belongsTo(GymActivity::class, 'activity_id')->withTrashed();
    }
}

