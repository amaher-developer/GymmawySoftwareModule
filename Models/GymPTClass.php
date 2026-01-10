<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Support\Facades\Schema;

class GymPTClass extends GenericModel
{
    protected $table = 'sw_gym_pt_classes';

    protected $guarded = ['id'];

    protected $appends = ['name'];

    protected $casts = [
        'is_system' => 'boolean',
        'reservation_details' => 'json',
        'schedule' => 'array',
        'is_mixed' => 'boolean',
        'total_sessions' => 'integer',
        'max_members' => 'integer',
        'is_active' => 'boolean',
    ];

    public static $uploads_path = 'uploads/classes/';
    public static $thumbnails_uploads_path = 'uploads/classes/thumbnails/';

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeIsSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function getNameAttribute()
    {
        $lang = 'name_' . $this->lang;
        return $this->$lang;
    }

    public function getContentAttribute()
    {
        $lang = 'content_' . $this->lang;
        return $this->$lang;
    }

    public function subscription()
    {
        return $this->belongsTo(GymPTSubscription::class, 'pt_subscription_id');
    }

    public function pt_subscription()
    {
        return $this->subscription();
    }

    public function classTrainers()
    {
        return $this->hasMany(GymPTClassTrainer::class, 'class_id');
    }

    public function activeClassTrainers()
    {
        return $this->classTrainers()->where('is_active', true);
    }

    public function members()
    {
        return $this->hasMany(GymPTMember::class, 'class_id');
    }

    public function pt_members()
    {
        return $this->members();
    }

    /**
     * Legacy relation retained for backward compatibility during the refactor.
     * Once all references are migrated to GymPTClassTrainer it can be removed safely.
     */
    public function pt_subscription_trainer()
    {
        return $this->hasMany(GymPTSubscriptionTrainer::class, 'pt_class_id');
    }

    public function toArray()
    {
        return parent::toArray();
    }
}

