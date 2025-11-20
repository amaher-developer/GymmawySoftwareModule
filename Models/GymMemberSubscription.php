<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use App\Modules\Gym\Models\GymBrand;
use Modules\Software\Classes\TypeConstants;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymMemberSubscription extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_member_subscription';
    protected $guarded = ['id'];
    protected $appends = ['status_name', 'status_value'];
    public static $uploads_path='uploads/members/';
    protected $casts = ['activities' => 'json', 'time_week' => 'json', 'contract_files' => 'json'];
    public static $thumbnails_uploads_path='uploads/members/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }
    public function getStatusNameAttribute()
    {
        $status = $this->getStatusAttribute($this->getRawOriginal('status'));
        if($status == TypeConstants::Active){
            return trans('sw.active');
        }else if($status == TypeConstants::Freeze){
            return trans('sw.frozen');
        }else if($status == TypeConstants::Expired){
            return trans('sw.expire');
        }else if($status == TypeConstants::Coming){
            return trans('sw.coming');
        }
    }
    public function getSignatureFileAttribute()
    {
        $signature_file = $this->getRawOriginal('signature_file');
        if($signature_file)
            return asset(GymOrder::$uploads_path.$signature_file);

        return null;
    }
    public function getStatusValueAttribute()
    {
        $status = $this->getRawOriginal('status');
        return $status;
    }
    public function getContractAttribute($contract)
    {
        return asset(GymMoneyBox::$uploads_path.$contract);
    }
    public function getStatusAttribute($status)
    {
//        return $status;

        $expireDate = Carbon::parse($this->getRawOriginal('expire_date'))->toDateString();
        $joiningDate = Carbon::parse($this->getRawOriginal('joining_date'))->toDateString();
//        $checkForMemberVisits = true;
//        if(($this->getRawOriginal('workouts') > 0) && ($this->getRawOriginal('workouts') < $this->getRawOriginal('visits'))){ $checkForMemberVisits = false; }
//
        if(
            ($this->getRawOriginal('start_freeze_date')) && ($this->getRawOriginal('end_freeze_date')) &&
            (Carbon::parse($this->getRawOriginal('start_freeze_date'))->toDateString() <= Carbon::now()->toDateString())
            &&
            (Carbon::parse($this->getRawOriginal('end_freeze_date'))->toDateString() > Carbon::now()->toDateString())
        ){
            return TypeConstants::Freeze;
        }else if($expireDate < Carbon::now()->toDateString() || (($this->getRawOriginal('workouts') > 0) && ($this->getRawOriginal('workouts') < $this->getRawOriginal('visits')))){
            return TypeConstants::Expired;
        }else if(($expireDate >= Carbon::now()->toDateString()) && ($joiningDate > Carbon::now()->toDateString())){
            return TypeConstants::Coming;
        }else if($expireDate >= Carbon::now()->toDateString()){
            return TypeConstants::Active;
        }
    }
    public function setUpdatedAtAttribute()
    {
        @request('typeRequest') == 'renew_request' ?  $expireDate = Carbon::now()->toDateString() : $expireDate = Carbon::parse($this->getRawOriginal('expire_date'))->toDateString();
        $joiningDate = Carbon::parse($this->getRawOriginal('joining_date'))->toDateString();
        $checkForMemberVisits = true;
        if((@request('typeRequest') != 'renew_request') && ($this->getRawOriginal('workouts') > 0) && ($this->getRawOriginal('workouts') < $this->getRawOriginal('visits'))){
            $checkForMemberVisits = false;
        }

        //        $this->attributes['status'] = TypeConstants::Expired;
        if(
            ($this->getRawOriginal('start_freeze_date')) && ($this->getRawOriginal('end_freeze_date')) &&
            (Carbon::parse($this->getRawOriginal('start_freeze_date'))->toDateString() <= Carbon::now()->toDateString())
            &&
            (Carbon::parse($this->getRawOriginal('end_freeze_date'))->toDateString() > Carbon::now()->toDateString())
        ){
            $this->attributes['status'] = TypeConstants::Freeze;
        }else if(($expireDate >= Carbon::now()->toDateString()) && ($joiningDate > Carbon::now()->toDateString())){
            $this->attributes['status'] = TypeConstants::Coming;
        }else if($expireDate >= Carbon::now()->toDateString() && ($checkForMemberVisits)){
            $this->attributes['status'] = TypeConstants::Active;
        } else if($expireDate < Carbon::now()->toDateString() || (!$checkForMemberVisits)){
            $this->attributes['status'] = TypeConstants::Expired;
        }


    }

    public function member(){
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    public function subscription(){
        return $this->belongsTo(GymSubscription::class, 'subscription_id');
    }
    public function pay_type(){
        return $this->belongsTo(GymPaymentType::class, 'payment_type', 'payment_id');
    }

    public function freezes()
    {
        return $this->hasMany(GymMemberSubscriptionFreeze::class, 'member_subscription_id');
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
