<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymPTTrainer extends GenericModel
{
    protected $table = 'sw_gym_pt_trainers';

    protected $guarded = ['id'];

    protected $appends = ['image_name'];

    public static $uploads_path = 'uploads/trainers/';
    public static $thumbnails_uploads_path = 'uploads/trainers/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
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

        return asset('resources/assets/front/img/blank-image.svg');
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