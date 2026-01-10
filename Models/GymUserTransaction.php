<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Support\Facades\Schema;

class GymUserTransaction extends GenericModel
{
    protected $table = 'sw_gym_user_transactions';
    protected $guarded = ['id'];
    protected $appends = ['transaction_type_name', 'financial_month_formatted', 'deduction_month'];

    const TRANSACTION_TYPES = [
        'monthly_salary' => 'monthly_salary',
        'commission_private_training' => 'commission_private_training',
        'commission_subscription_sales' => 'commission_subscription_sales',
        'bonus' => 'bonus',
        'advance' => 'advance',
        'penalty_deduction' => 'penalty_deduction',
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

    public function employee()
    {
        return $this->belongsTo(GymUser::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(GymUser::class, 'user_id');
    }

    public function relatedTransaction()
    {
        return $this->belongsTo(GymUserTransaction::class, 'related_transaction_id');
    }

    public function moneyBox()
    {
        return $this->hasOne(GymMoneyBox::class, 'user_transaction_id');
    }

    public function getTransactionTypeNameAttribute()
    {
        return trans('sw.' . $this->transaction_type);
    }

    public function getFinancialMonthFormattedAttribute()
    {
        if (!$this->financial_month) {
            return '';
        }
        
        $date = \Carbon\Carbon::createFromFormat('Y-m', $this->financial_month);
        return $date->format('F Y');
    }

    public function getDeductionMonthAttribute()
    {
        // Map advance_discount_month to deduction_month for form compatibility
        return $this->attributes['advance_discount_month'] ?? null;
    }
}

