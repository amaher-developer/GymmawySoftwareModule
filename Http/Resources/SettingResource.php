<?php

namespace Modules\Software\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return
            [
                "phone" => $this->phone,
                "facebook" => $this->facebook,
                "twitter" => $this->twitter,
                "instagram" => $this->instagram,
                "tiktok" => $this->tiktok,
                "snapchat" => $this->snapchat,
                "support_email" => " ",//$this->support_email,
                //"about" => $this->about,
                'latitude' => $this->latitude ,
                'longitude' => $this->longitude ,
                "terms" => $this->terms,
                "ios_version" => $this->ios_version,
                "ios_url" => $this->ios_app,
                "android_version" => $this->android_version,
                "android_url" => $this->android_app,
                "map_location_image" => @env('APP_URL_ASSETS') ? @env('APP_URL') . @env('APP_URL_ASSETS') . 'map_location.png' : '',
                "map_link" => "https://maps.google.com/?q=".$this->latitude.",".$this->longitude,
                "enable_dynamic_qr" => (bool) $this->enable_dynamic_qr,
                "qr_expiry_seconds" => (int) ($this->qr_expiry_seconds ?: 60),
            ];
    }
}


