<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Carbon\Carbon;

/**
 * LoyaltyTransaction Model
 * 
 * Records all points movements including earning, redemption, and manual adjustments.
 * Maintains a complete audit trail of all loyalty point activities.
 * 
 * @property int $id
 * @property int $member_id
 * @property int|null $rule_id
 * @property int|null $campaign_id
 * @property int $points - Positive for earn/add, negative for redeem/deduct
 * @property string $type - 'earn', 'redeem', or 'manual'
 * @property string|null $source_type - Type of source (e.g., "subscription", "order")
 * @property int|null $source_id - ID of the source record
 * @property string|null $reason - Reason for transaction
 * @property float|null $amount_spent - Money amount that generated points
 * @property \Carbon\Carbon|null $expires_at
 * @property bool $is_expired
 * @property int|null $created_by - Admin user who created (for manual transactions)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class LoyaltyTransaction extends GenericModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sw_loyalty_transactions';

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
        'points' => 'integer',
        'amount_spent' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_expired' => 'boolean',
    ];

    /**
     * Transaction type constants
     */
    const TYPE_EARN = 'earn';
    const TYPE_REDEEM = 'redeem';
    const TYPE_MANUAL = 'manual';

    /**
     * Get the member who owns this transaction
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    /**
     * Get the rule associated with this transaction
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rule()
    {
        return $this->belongsTo(LoyaltyPointRule::class, 'rule_id');
    }

    /**
     * Get the campaign associated with this transaction
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaign()
    {
        return $this->belongsTo(LoyaltyCampaign::class, 'campaign_id');
    }

    /**
     * Get the admin user who created this transaction (for manual transactions)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(GymUser::class, 'created_by');
    }

    /**
     * Scope to filter by member
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $memberId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    /**
     * Scope to get only earn transactions
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEarned($query)
    {
        return $query->where('type', self::TYPE_EARN);
    }

    /**
     * Scope to get only redeem transactions
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRedeemed($query)
    {
        return $query->where('type', self::TYPE_REDEEM);
    }

    /**
     * Scope to get only manual transactions
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeManual($query)
    {
        return $query->where('type', self::TYPE_MANUAL);
    }

    /**
     * Scope to get non-expired transactions
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_expired', false);
    }

    /**
     * Scope to get expired transactions
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('is_expired', true);
    }

    /**
     * Scope to get transactions that should be expired
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShouldExpire($query)
    {
        return $query->where('is_expired', false)
                     ->whereNotNull('expires_at')
                     ->where('expires_at', '<=', Carbon::now());
    }

    /**
     * Check if this transaction has expired
     * 
     * @return bool
     */
    public function hasExpired()
    {
        if ($this->is_expired) {
            return true;
        }

        if ($this->expires_at === null) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Mark this transaction as expired
     * 
     * @return bool
     */
    public function markAsExpired()
    {
        $this->is_expired = true;
        return $this->save();
    }

    /**
     * Get absolute points value (always positive)
     * 
     * @return int
     */
    public function getAbsolutePointsAttribute()
    {
        return abs($this->points);
    }

    /**
     * Check if transaction is positive (earning points)
     * 
     * @return bool
     */
    public function isPositive()
    {
        return $this->points > 0;
    }

    /**
     * Check if transaction is negative (redeeming points)
     * 
     * @return bool
     */
    public function isNegative()
    {
        return $this->points < 0;
    }
}

