<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymNonMember extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_non_members';
    protected $guarded = ['id'];
    protected $appends = [];
    protected $casts = ['activities' => 'json', 'contract_files' => 'json'];
    public static $uploads_path='uploads/nonmembers/';
    public static $thumbnails_uploads_path='uploads/nonmembers/thumbnails/';

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

    public function non_member_times()
    {
        return $this->hasMany(GymNonMemberTime::class, 'non_member_id');
    }

    /**
     * Get the ZATCA invoice associated with the non-member purchase.
     */
    public function zatcaInvoice()
    {
        return $this->hasOne(\Modules\Billing\Models\SwBillingInvoice::class, 'non_member_id');
    }

    public function getSignatureFileAttribute()
    {
        $signature_file = $this->getRawOriginal('signature_file');
        if($signature_file)
            return asset(GymOrder::$uploads_path.$signature_file);

        return null;
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
