<?php

namespace Modules\Software\Http\Resources;

use Modules\Generic\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityContentResource extends JsonResource
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
                "name" => $this->name,
                "image" => $this->image_name ? $this->image : @env('APP_WEBSITE').'placeholder_black.png',
                "price" => $this->price ? number_format($this->price + ( $this->price * (@$setting->vat_details['vat_percentage'] / 100)) , 2) . ' ' . env('APP_CURRENCY_'.strtoupper($this->lang)) . ' ' : '0',
                "content" => $this->content ? $this->content : '',
                "is_payment" => @env('APP_WEB_PAYMENT_ACTIVITY') == 1 ? 1 : 0,
                "payment_link" => @env('APP_WEB_PAYMENT_ACTIVITY') == 1 ? (@env('APP_WEBSITE'). $this->lang ."/"."activity/".$this->id) : "",
            ];
    }
}


