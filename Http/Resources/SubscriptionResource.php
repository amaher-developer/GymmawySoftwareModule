<?php

namespace Modules\Software\Http\Resources;

use Modules\Generic\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
                "title" => $this->name,
                "name" => (int)$this->period.' '.trans('sw.day_2'),
                "image" => $this->image,
                "price" => number_format($finalPrice, 2).' '.$currency,
                "price_unit" => $currency,
                "is_discount" => $discountValue > 0 ? 1 : 0,
                "discount" => $discountValue > 0 ? trans('sw.app_discount_msg', ['discount' => $discountValue, 'unit' => $currency]) : '',
                "shor_description" =>  ''.$this->name.', '.trans('sw.app_subscription_shor_description', ['training_num' => $this->period])
            ];
    }
}


