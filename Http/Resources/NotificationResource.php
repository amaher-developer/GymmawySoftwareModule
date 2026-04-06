<?php

namespace Modules\Software\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
                "title" => $this->title,
                "image" => @$this->body['image'] ?: null,
                "content" => @$this->body['body'] ?: null,
                "date" => $this->created_at ? $this->created_at->diffForHumans() : null,
            ];
    }
}


