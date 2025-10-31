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
        return
            [
                "id" => $this->id,
                "name" => (int)$this->period.' '.trans('sw.day_2'),//$this->name,
                "image" => $this->image,
                "price" => number_format($this->price + ( $this->price * (@$setting->vat_details['vat_percentage'] / 100)) , 2).' '.env('APP_CURRENCY_'.strtoupper($this->lang)) ,
                "price_unit" => env('APP_CURRENCY_'.strtoupper($this->lang)) ,
                "is_discount" => $this->discount ? 1 : 0,
                "discount" => $this->discount ? trans('sw.app_discount_msg', ['discount' => $this->discount, 'unit' => env('APP_CURRENCY_'.strtoupper($this->lang))]) : '',
                "shor_description" =>  ''.$this->name.', '.trans('sw.app_subscription_shor_description', ['training_num' => $this->period])
            ];
    }
}
