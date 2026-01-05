<?php

namespace Modules\Software\Models;

use App\Modules\Access\Models\User;
use Modules\Generic\Models\GenericModel;
use Modules\Billing\Models\SwBillingInvoice;
use Modules\Software\Classes\TypeConstants;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;

class GymMember extends GenericModel
{
    use \Illuminate\Auth\Authenticatable;

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_members';
    protected $guarded = ['id'];
    protected $appends = ['loyalty_points_formatted'];
    public static $uploads_path='uploads/members/';
    public static $thumbnails_uploads_path='uploads/members/thumbnails/';

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'loyalty_points_balance' => 'integer',
        'last_points_update' => 'datetime',
    ];
    
    /**
     * Get formatted loyalty points balance
     */
    public function getLoyaltyPointsFormattedAttribute()
    {
        return number_format($this->loyalty_points_balance ?? 0);
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
    public function getCodeAttribute($key)
    {
        return sprintf('%012d',$key);
//        return str_pad($key, 14, 0, STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(GymUser::class, 'user_id');
    }

    public function gym_orders()
    {
        return $this->hasMany(GymOrder::class, 'member_id');
    }
    public function gym_reservations()
    {
        return $this->hasMany(GymReservation::class, 'member_id');
    }

    public function push_tokens()
    {
        return $this->hasMany(GymPushToken::class, 'member_id');
    }
    public function getImageAttribute()
    {
        $image = $this->getRawOriginal('image');
        if($image)
            return asset(self::$uploads_path.$image);

        return asset('uploads/settings/default.jpg');
    }

    public function member_subscription_info(){
        return $this->hasOne(GymMemberSubscription::class, 'member_id')
            ->orderBy('id', 'desc');
    }
    public function member_remain_amount_subscriptions(){
        return $this->hasMany(GymMemberSubscription::class, 'member_id')
            ->whereRaw('ROUND(amount_remaining, 0) > 0')->orderBy('id', 'desc');
    }

    public function member_subscription_info_active(){
        return $this->hasOne(GymMemberSubscription::class, 'member_id')
            ->where('expire_date', '>=', Carbon::now()->toDateString());
    }

    public function member_subscription_info_has_active(){
        return $this->hasOne(GymMemberSubscription::class, 'member_id')
            ->whereIn('status',  [TypeConstants::Active, TypeConstants::Freeze]);
    }
    public function member_balance(){
        $add = $this->hasOne(GymMemberCredit::class, 'member_id')
            ->where('operation',  0)->sum('amount');
        $refund = $this->hasOne(GymMemberCredit::class, 'member_id')
            ->where('operation',  '!=',0)->sum('amount');
        return round(($add - $refund), 2);
    }
    public function member_credits()
    {
        return $this->hasMany(GymMemberCredit::class,'member_id');
    }
    public function member_subscriptions()
    {
        return $this->hasMany(GymMemberSubscription::class,'member_id');
    }

    public function moneyBoxes()
    {
        return $this->hasMany(GymMoneyBox::class, 'member_id');
    }

    public function billingInvoices()
    {
        return $this->hasManyThrough(
            \Modules\Billing\Models\SwBillingInvoice::class,
            GymMoneyBox::class,
            'member_id',
            'money_box_id',
            'id',
            'id'
        );
    }

    public function member_attendees()
    {
        return $this->hasMany(GymMemberAttendee::class,'member_id');
    }
    public function pt_members()
    {
        return $this->hasMany(GymPTMember::class, 'member_id');
    }

    public function member_zk_fingerprints()
    {
        return $this->hasMany(GymZKFingerprint::class,'member_id');
    }
    public function member_zk_fingerprint(){
        return $this->hasOne(GymZKFingerprint::class, 'member_id')->orderBy('id', 'desc');
    }

    /**
     * Get all loyalty transactions for this member
     */
    public function loyalty_transactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'member_id');
    }

    /**
     * Get active (non-expired) loyalty transactions
     */
    public function active_loyalty_transactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'member_id')
            ->where('is_expired', false)
            ->where('points', '>', 0);
    }

    public function zatcaInvoice()
    {
        return $this->hasOne(SwBillingInvoice::class, 'member_id')->latest('id');
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

