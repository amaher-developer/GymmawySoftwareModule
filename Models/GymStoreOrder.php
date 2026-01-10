<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Support\Facades\Schema;

class GymStoreOrder extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_store_orders';
    protected $guarded = ['id'];
    protected $appends = [];
    protected $casts = ['products' => 'json'];
    public static $uploads_path='uploads/products/';
    public static $thumbnails_uploads_path='uploads/products/thumbnails/';

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
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    public function order_product()
    {
        return $this->hasMany(GymStoreOrderProduct::class, 'order_id');
    }

    public function pay_type(){
        return $this->belongsTo(GymPaymentType::class, 'payment_type', 'payment_id');
    }

    public function loyaltyRedemption()
    {
        return $this->hasOne(LoyaltyTransaction::class, 'source_id')
            ->where('source_type', 'store_order_redemption')
            ->where('type', 'redeem');
    }

    /**
     * Get the ZATCA invoice for this store order
     */
    public function zatcaInvoice()
    {
        return $this->hasOne(\Modules\Billing\Models\SwBillingInvoice::class, 'store_order_id');
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

