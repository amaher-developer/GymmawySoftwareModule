<?php

namespace Modules\Software\Http\Resources;

use Modules\Generic\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreContentResource extends JsonResource
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
                "name" => $this->name,
                "image" => $this->image ? @$this->image : @env('APP_WEBSITE').'placeholder_black.png',
                "price" => @number_format($this->price + ( $this->price * (@$setting->vat_details['vat_percentage'] / 100)) , 2) . ' ' . env('APP_CURRENCY_'.strtoupper($this->lang)) . ' '  ,
//                "price_unit" => env('APP_CURRENCY_'.strtoupper($this->lang)) ,
                "content" => $this->content ?? ' ',
                "is_payment" => @env('APP_WEB_PAYMENT_STORE') == 1 ? 1 : 0,
                "payment_link" => @env('APP_WEB_PAYMENT_STORE') == 1 ? (@env('APP_WEBSITE'). $this->lang ."/"."store/".$this->id) : "",
            ];
    }
}


