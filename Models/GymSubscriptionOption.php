<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymSubscriptionOption extends GenericModel
{
    protected $table = 'sw_gym_subscription_options';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'product_id'      => 'integer',
        'activity_id'     => 'integer',
        'field_overrides' => 'array',
    ];
    protected $appends = ['name'];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function getNameAttribute(): string
    {
        if ($this->product_id)  return $this->product?->display_name  ?? '';
        if ($this->activity_id) return $this->activity?->name          ?? '';
        return $this->getRawOriginal('name_ar') ?? '';
    }

    public function product()
    {
        return $this->belongsTo(GymStoreProduct::class, 'product_id');
    }

    public function activity()
    {
        return $this->belongsTo(GymActivity::class, 'activity_id');
    }

    public function group()
    {
        return $this->belongsTo(GymSubscriptionOptionGroup::class, 'option_group_id');
    }

    public function memberSubscriptionOptions()
    {
        return $this->hasMany(GymMemberSubscriptionOption::class, 'option_id');
    }
}
