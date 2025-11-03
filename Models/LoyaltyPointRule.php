<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

/**
 * LoyaltyPointRule Model
 * 
 * Defines conversion rates and expiry settings for the loyalty points system.
 * Each rule specifies how money converts to points and vice versa.
 * 
 * @property int $id
 * @property int $branch_setting_id
 * @property string $name
 * @property float $money_to_point_rate - Amount of money needed to earn 1 point
 * @property float $point_to_money_rate - Value of 1 point when redeemed
 * @property int|null $expires_after_days - Days until points expire (null = never)
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class LoyaltyPointRule extends GenericModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sw_loyalty_point_rules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'money_to_point_rate' => 'decimal:2',
        'point_to_money_rate' => 'decimal:2',
        'expires_after_days' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get all loyalty transactions using this rule
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'rule_id');
    }

    /**
     * Scope to get only active rules
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by branch
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }

    /**
     * Calculate points earned from money amount
     * 
     * @param float $amount Money amount
     * @return int Points earned
     */
    public function calculatePointsFromMoney($amount)
    {
        if ($this->money_to_point_rate <= 0) {
            return 0;
        }

        return (int) floor($amount / $this->money_to_point_rate);
    }

    /**
     * Calculate money value from points
     * 
     * @param int $points Number of points
     * @return float Money value
     */
    public function calculateMoneyFromPoints($points)
    {
        return $points * $this->point_to_money_rate;
    }

    /**
     * Get expiry date for points earned now
     * 
     * @return \Carbon\Carbon|null
     */
    public function getExpiryDate()
    {
        if ($this->expires_after_days === null) {
            return null;
        }

        return now()->addDays($this->expires_after_days);
    }
}

