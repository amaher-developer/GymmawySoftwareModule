<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

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

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', @$this->user_sw->branch_setting_id ?? 1);
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

