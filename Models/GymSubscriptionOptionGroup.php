<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymSubscriptionOptionGroup extends GenericModel
{
    const SELECTION_SINGLE   = 'single';
    const SELECTION_MULTIPLE = 'multiple';

    protected $table = 'sw_gym_subscription_option_groups';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'is_required' => 'boolean',
        'is_system'   => 'boolean',
        'is_web'      => 'boolean',
        'is_mobile'   => 'boolean',
        'category_id' => 'integer',
    ];
    protected $appends = ['name'];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function getNameAttribute()
    {
        $lang = 'name_' . $this->lang;
        return $this->$lang;
    }

    public function subscription()
    {
        return $this->belongsTo(GymSubscription::class, 'subscription_id');
    }

    public function options()
    {
        return $this->hasMany(GymSubscriptionOption::class, 'option_group_id')->orderBy('list_order');
    }

    /**
     * Optional product category that drives what products belong to this group.
     * When set, the customer UI shows products from this category as the choices.
     * When null, choices are the manually defined options().
     */
    public function category()
    {
        return $this->belongsTo(GymStoreCategory::class, 'category_id');
    }
}
