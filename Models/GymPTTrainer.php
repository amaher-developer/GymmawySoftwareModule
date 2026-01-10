<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Support\Facades\Schema;

class GymPTTrainer extends GenericModel
{
    protected $table = 'sw_gym_pt_trainers';

    protected $guarded = ['id'];

    protected $appends = ['image_name'];

    public static $uploads_path = 'uploads/trainers/';
    public static $thumbnails_uploads_path = 'uploads/trainers/thumbnails/';

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

    public function getImageNameAttribute()
    {
        return $this->getRawOriginal('image');
    }

    public function getImageAttribute()
    {
        $image = $this->getRawOriginal('image');
        if ($image) {
            return asset(self::$uploads_path . $image);
        }

        return asset('resources/assets/new_front/img/blank-image.svg');
    }

    public function classAssignments()
    {
        return $this->hasMany(GymPTClassTrainer::class, 'trainer_id');
    }

    public function classTrainers()
    {
        return $this->classAssignments();
    }

    public function activeClassAssignments()
    {
        return $this->classAssignments()->where('is_active', true);
    }

    public function pt_members()
    {
        return $this->hasMany(GymPTMember::class, 'pt_trainer_id');
    }

    public function members()
    {
        return $this->pt_members();
    }

    public function commissions()
    {
        return $this->hasMany(GymPTCommission::class, 'trainer_id');
    }

    public function pendingCommissions()
    {
        return $this->commissions()->where('status', 'pending');
    }

    /**
     * Legacy relation kept until the legacy pivot is fully retired.
     */
    public function pt_subscriptions()
    {
        return $this->belongsToMany(
            GymPTSubscription::class,
            'sw_gym_pt_subscription_trainer',
            'pt_trainer_id',
            'pt_subscription_id'
        )->withTimestamps();
    }

    /**
     * Legacy relation kept until the legacy pivot is fully retired.
     */
    public function pt_subscription_trainer()
    {
        return $this->hasMany(GymPTSubscriptionTrainer::class, 'pt_trainer_id');
    }

    public function pt_members_trainer_amount_status_false()
    {
        return $this->hasMany(GymPTMember::class, 'pt_trainer_id')->where('trainer_amount_status', 0);
    }

    public function toArray()
    {
        return parent::toArray();
    }
}
