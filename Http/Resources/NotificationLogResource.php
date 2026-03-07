<?php

namespace Modules\Software\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationLogResource extends JsonResource
{
    public function toArray($request)
    {
        $response = is_array($this->response) ? $this->response : json_decode($this->response, true);

        return [
            'id'         => $this->id,
            'title'      => $response['title']   ?? null,
            'content'    => $response['content'] ?? $response['body'] ?? $this->content,
            'image'      => $response['image']   ?? null,
            'url'        => $response['url']     ?? null,
            'type'       => $response['type']    ?? null,
            'created_at' => $this->created_at,
        ];
    }
}
