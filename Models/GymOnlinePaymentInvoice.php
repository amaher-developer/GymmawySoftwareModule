<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Modules\Software\Classes\TypeConstants;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymOnlinePaymentInvoice extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_online_payment_invoices';
    protected $guarded = ['id'];
    protected $appends = ['image', 'payment_gateway_name', 'payment_channel_name'];
    public static $uploads_path='uploads/subscriptions/';
    public static $thumbnails_uploads_path='uploads/subscriptions/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }
    public function getImageAttribute()
    {
        return asset('uploads/settings/default.jpg');
    }

    public function member(){
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    public function subscription(){
        return $this->belongsTo(GymSubscription::class, 'subscription_id');
    }

    public function getPaymentGatewayNameAttribute()
    {
        $gateways = [
            TypeConstants::TABBY_TRANSACTION => 'Tabby',
            TypeConstants::PAYMOB_TRANSACTION => 'Paymob',
            TypeConstants::TAMARA_TRANSACTION => 'Tamara',
            TypeConstants::PAYTABS_TRANSACTION => 'PayTabs',
            TypeConstants::PAYPAL_TRANSACTION_FEES => 'PayPal',
        ];

        return $gateways[$this->payment_method] ?? trans('sw.unknown');
    }

    public function getPaymentChannelNameAttribute()
    {
        $channels = [
            TypeConstants::CHANNEL_SYSTEM => trans('sw.channel_system'),
            TypeConstants::CHANNEL_WEBSITE => trans('sw.channel_website'),
            TypeConstants::CHANNEL_MOBILE_APP => trans('sw.channel_mobile_app'),
        ];

        return $channels[$this->payment_channel] ?? trans('sw.unknown');
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

