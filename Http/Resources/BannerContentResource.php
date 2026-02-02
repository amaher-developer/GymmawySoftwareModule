<?php

namespace Modules\Software\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerContentResource extends JsonResource
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
                "id" => $this->id,
                "title" => $this->title,
                "image" => $this->image_name ? $this->image : @env('APP_WEBSITE').@env('APP_URL_ASSETS') . 'placeholder_black.png',
                "url" => $this->url ? $this->url : '',
                "phone" => $this->phone ? $this->phone : '',
                "content" => $this->content ? $this->content : ''
            ];
    }
}


