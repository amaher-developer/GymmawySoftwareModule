<?php

namespace Modules\Software\Http\Resources;

use Modules\Generic\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class SubscriptionContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $setting = Setting::select('vat_details')->first();
        $currency = env('APP_CURRENCY_'.strtoupper($this->lang));
        $vatPct = (float)@$setting->vat_details['vat_percentage'];

        $basePrice = (float)$this->price;
        $discountValue = (float)$this->default_discount_value;
        $discountType  = $this->default_discount_type; // 1 = percentage, 0 = fixed

        if ($discountValue > 0) {
            $discountedPrice = $discountType == 1
                ? $basePrice - ($basePrice * $discountValue / 100)
                : $basePrice - $discountValue;
            $discountedPrice = max(0, $discountedPrice);
        } else {
            $discountedPrice = $basePrice;
        }

        $finalPrice = $discountedPrice + ($discountedPrice * $vatPct / 100);

        return
            [
                "id" => $this->id,
                "name" => Str::limit(@$this->name, 30),
                "image" => $this->image_name ? $this->image : @env('APP_URL').@env('APP_URL_ASSETS') . 'placeholder_black.png',
                "price" => number_format($finalPrice, 2). ' ' . $currency . ' ',
                "content" => strip_tags(@$this->content),
                "period" => $this->period . ' '. trans('sw.day_2'),
                "workouts" => $this->workouts,
                "freeze_limit" => $this->freeze_limit,
                "number_times_freeze" => $this->number_times_freeze,
                "activities" => @$this->activities ? SubscriptionActivityResource::collection($this->activities) : [],
                "is_payment" => @env('APP_WEB_PAYMENT_SUBSCRIPTION') == 1 ? 1 : 0, 
                //"payment_link" => @env('APP_WEB_PAYMENT_SUBSCRIPTION') == 1 ? (@env('APP_WEBSITE'). $this->lang ."/"."subscription-mobile/".$this->id) : "",
                "payment_link" => @env('APP_WEB_PAYMENT_SUBSCRIPTION') == 1 ? @env('APP_URL').'/'.route('sw.subscription-mobile', ['id' => $this->id]) : "",

            ];
    }
}


