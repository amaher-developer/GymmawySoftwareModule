<?php

namespace Modules\Software\Models;

use App\Modules\Access\Models\User;
use Modules\Generic\Models\GenericModel;
use Modules\Software\Classes\TypeConstants;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class GymMember extends GenericModel
{
    use \Illuminate\Auth\Authenticatable;

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_members';
    protected $guarded = ['id'];
    protected $appends = [];
    public static $uploads_path='uploads/members/';
    public static $thumbnails_uploads_path='uploads/members/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
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
