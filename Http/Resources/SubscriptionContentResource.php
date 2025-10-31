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
        return
            [
                "id" => $this->id,
                "name" => Str::limit(@$this->name, 30),
                "image" => $this->image_name ? $this->image : @env('APP_WEBSITE').'placeholder_black.png',
                "price" => number_format($this->price + ( $this->price * (@$setting->vat_details['vat_percentage'] / 100)) , 2). ' ' . env('APP_CURRENCY_'.strtoupper($this->lang)) . ' ',
                "content" => strip_tags(@$this->content),
                "period" => $this->period . ' '. trans('sw.day_2'),
                "workouts" => $this->workouts,
                "freeze_limit" => $this->freeze_limit,
                "number_times_freeze" => $this->number_times_freeze,
                "activities" => @$this->activities ? SubscriptionActivityResource::collection($this->activities) : [],
                "is_payment" => @env('APP_WEB_PAYMENT_SUBSCRIPTION') == 1 ? 1 : 0,
                "payment_link" => @env('APP_WEB_PAYMENT_SUBSCRIPTION') == 1 ? (@env('APP_WEBSITE'). $this->lang ."/"."subscription/".$this->id) : "",
            ];
    }
}
