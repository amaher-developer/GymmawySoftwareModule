<?php

namespace Modules\Software\Http\Resources;

use Modules\Generic\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

class PTContentResource extends JsonResource
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
                "image" => $this->pt_subscription->image_name ? $this->pt_subscription->image : @env('APP_WEBSITE').'placeholder_black.png',
                "price" => $this->price ? number_format($this->price + ( $this->price * (@$setting->vat_details['vat_percentage'] / 100)) , 2)  . ' ' . env('APP_CURRENCY_'.strtoupper($this->lang)) . ' '  : '',
                "classes" => $this->classes ? $this->classes : 0,
                "content" => $this->content ? $this->content : '',
                "is_reserved" => $this->is_reserved ?? 0,
                "trainers" => @$this->pt_subscription_trainer ? PTTrainerContentResource::collection(@$this->pt_subscription_trainer) : [],
//                "reservations" => $this->pt_subscription->pt_trainers ? new PTReservationContentResource($this->pt_subscription->pt_trainers->pt_subscription_trainer->reservation_details) : []
                "is_payment" => @env('APP_WEB_PAYMENT_PT_SUBSCRIPTION') == 1 ? 1 : 0,
                "payment_link" => @env('APP_WEB_PAYMENT_PT_SUBSCRIPTION') == 1 ? (@env('APP_WEBSITE'). $this->lang ."/"."pt-subscription/".$this->id) : "",

            ];
    }
}


