<?php

namespace Modules\Software\Models;

use App\Modules\Access\Models\User;
use Illuminate\Support\Facades\Schema;
use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymUserLog extends GenericModel
{

//    protected $dates = ['deleted_at'];
/*
 * type:
 * 0: new membership client
 * 1: renew membership client
 * 2: edit membership client
 * 3: delete membership client
 * 4: new activity
 * 5: edit activity
 * 6: delete activity
 * 7: new block client
 * 8: edit block client
 * 9: delete block client
 * 10: new non client
 * 11: edit non client
 * 12: delete non client
 * 13: new membership
 * 14: edit membership
 * 15: delete membership
 * 16: new user
 * 17: edit user
 * 18: delete user
 * 19: send notification
 * 20: moneybox: add
 * 21: moneybox: sub
 * 22: moneybox: sub earning
 *
 * 23: export: PDF Activity
 * 24: export: Excel Activity
 * 25: export: PDF block client
 * 26: export: Excel block client
 * 27: export: PDF membership client
 * 28: export: Excel membership client
 * 29: export: PDF non client
 * 30: export: Excel non client
 * 31: export: PDF Moneybox report
 * 32: export: Excel Moneybox report
 * 33: export: PDF Membership
 * 34: export: Excel Membership
 * 35: export: PDF User
 * 35: export: Excel User
 *
 * 36: scan member
 *
 *
 *
 */
    protected $table = 'sw_gym_user_logs';
    protected $guarded = ['id'];
    protected $appends = [];
    public static $uploads_path='uploads/users/';
    public static $thumbnails_uploads_path='uploads/users/thumbnails/';

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
    public function user(){
        return $this->belongsTo(GymUser::class, 'user_id');
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

