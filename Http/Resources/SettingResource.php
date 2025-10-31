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
                "android_version" => $this->android_version,
                "map_location_image" => @env('APP_WEBSITE') ? @env('APP_WEBSITE').'map_location.png' : '',
                "map_link" => "https://maps.google.com/?q=".$this->latitude.",".$this->longitude
            ];
    }
}
