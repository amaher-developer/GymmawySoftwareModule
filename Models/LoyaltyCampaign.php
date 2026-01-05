<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * LoyaltyCampaign Model
 * 
 * Defines promotional campaigns that multiply points earned during specific periods.
 * Campaigns can boost customer engagement by offering bonus points.
 * 
 * @property int $id
 * @property int $branch_setting_id
 * @property string $name
 * @property float $multiplier - Points multiplier (e.g., 2.00 = double points)
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property string|null $applies_to - Optional targeting criteria
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class LoyaltyCampaign extends GenericModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sw_loyalty_campaigns';

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
        'multiplier' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get all loyalty transactions associated with this campaign
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'campaign_id');
    }

    /**
     * Scope to get only active campaigns
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get currently running campaigns
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrent($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
                     ->where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now);
    }

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

    /**
     * Check if campaign is currently running
     * 
     * @return bool
     */
    public function isRunning()
    {
        $now = Carbon::now();
        return $this->is_active 
               && $this->start_date <= $now 
               && $this->end_date >= $now;
    }

    /**
     * Apply multiplier to points
     * 
     * @param int $points Base points
     * @return int Multiplied points
     */
    public function applyMultiplier($points)
    {
        return (int) floor($points * $this->multiplier);
    }

    /**
     * Get campaign status as string
     * 
     * @return string
     */
    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        $now = Carbon::now();
        
        if ($this->start_date > $now) {
            return 'upcoming';
        }
        
        if ($this->end_date < $now) {
            return 'expired';
        }
        
        return 'running';
    }
}


