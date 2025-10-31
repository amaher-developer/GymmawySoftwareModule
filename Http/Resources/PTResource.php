<?php

namespace Modules\Software\Http\Resources;

use Modules\Generic\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

class PTResource extends JsonResource
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
                "name" => $this->name ?? $this->pt_subscription->name ,
                "image" => @$this->pt_subscription->image_name ? @$this->pt_subscription->image : @env('APP_WEBSITE').'placeholder_black.png',
                "price" => @$this->price  ? (string)number_format($this->price + ( $this->price * (@$setting->vat_details['vat_percentage'] / 100)) , 2) : '',
                "price_unit" =>  env('APP_CURRENCY_'.strtoupper($this->lang)),
                "currency" =>  env('APP_CURRENCY_'.strtoupper($this->lang)),
                "classes" => $this->classes ? $this->classes . ' '. trans('sw.pt_class_2').' ' : 0,
            ];
    }
}
