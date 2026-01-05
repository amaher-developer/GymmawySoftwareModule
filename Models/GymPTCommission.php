<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Support\Facades\Schema;

class GymPTCommission extends GenericModel
{
    protected $table = 'sw_gym_pt_commissions';

    protected $guarded = ['id'];

    protected $casts = [
        'commission_rate' => 'float',
        'commission_amount' => 'float',
        'paid_at' => 'datetime',
        'session_date' => 'datetime',
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

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function trainer()
    {
        return $this->belongsTo(GymPTTrainer::class, 'trainer_id');
    }

    public function member()
    {
        return $this->belongsTo(GymPTMember::class, 'pt_member_id');
    }

    public function attendee()
    {
        return $this->belongsTo(GymPTMemberAttendee::class, 'pt_member_attendee_id');
    }

    public function paidByUser()
    {
        return $this->belongsTo(GymUser::class, 'paid_by');
    }
}


