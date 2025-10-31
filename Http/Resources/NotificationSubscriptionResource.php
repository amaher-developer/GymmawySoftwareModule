<?php

namespace Modules\Software\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Generic\Models\Setting;

class NotificationSubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $setting = Setting::first();
        return
            [
                'id' => $this->id,
                'name' => @$this->member->name,
                'phone' => @$this->member->phone,
                'code' => @$this->member->code,
                'gym_name' => @$setting->name,
                'gym_logo' => @$setting->logo ,
                'gym_code' => asset('uploads/barcodes/' . sprintf("%020d", $this->code) . '.png') ,
            ];
    }
}
