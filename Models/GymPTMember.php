<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Modules\Software\Classes\TypeConstants;
use Carbon\Carbon;

class GymPTMember extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_pt_members';
    protected $guarded = ['id'];
    protected $appends = ['status_name', 'status'];
    protected $casts = ['contract_files' => 'json'];
    public static $uploads_path='uploads/members/';
    public static $thumbnails_uploads_path='uploads/members/thumbnails/';


    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }
    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    public function pt_subscription()
    {
        return $this->belongsTo(GymPTSubscription::class, 'pt_subscription_id');
    }

    public function pt_class()
    {
        return $this->belongsTo(GymPTClass::class, 'pt_class_id');
    }

    public function pt_subscription_trainer()
    {
        return $this->hasOne(GymPTSubscriptionTrainer::class, 'pt_class_id', 'pt_class_id');
    }
    public function pt_member_attendees()
    {
        return $this->hasMany(GymMemberAttendee::class, 'pt_subscription_id')->where('type', TypeConstants::ATTENDANCE_TYPE_PT);
    }

    public function pt_trainer()
    {
        return $this->belongsTo(GymPTTrainer::class, 'pt_trainer_id');
    }

    public function getSignatureFileAttribute()
    {
        $signature_file = $this->getRawOriginal('signature_file');
        if($signature_file)
            return asset(GymOrder::$uploads_path.$signature_file);

        return null;
    }

    public function getStatusNameAttribute()
    {
        $status = $this->getStatusAttribute();
        if($status == TypeConstants::Active){
            return trans('sw.active');
        }else if($status == TypeConstants::Coming){
            return trans('sw.coming');
//        }else if($status = TypeConstants::Expired){
        }else {
            return trans('sw.expire');
        }
    }
    public function getStatusAttribute()
    {
        if(($this->getRawOriginal('classes') <= $this->getRawOriginal('visits')) || (Carbon::parse($this->getRawOriginal('expire_date'))->toDateString() < Carbon::now()->toDateString()) ){
            return TypeConstants::Expired;
        } else if((Carbon::parse($this->getRawOriginal('joining_date'))->toDateString() > Carbon::now()->toDateString()) ) {
            return TypeConstants::Coming;
        }else{
            return TypeConstants::Active;
        }
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
