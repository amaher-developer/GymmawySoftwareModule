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
                "id"         => $this->id,
                "title"      => $this->title,
                "image"      => $this->image_name ? $this->image : @env('APP_URL').@env('APP_URL_ASSETS') . 'placeholder_black.png',
                "url"        => $this->url    ?? '',
                "phone"      => $this->phone  ?? '',
                "content"    => $this->content ?? '',
                "type"       => (int) ($this->type ?? 1),
                "event_date" => $this->event_date ? \Carbon\Carbon::parse($this->event_date)->toDateString() : null,
            ];
    }
}


