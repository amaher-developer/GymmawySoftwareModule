<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

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

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
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

