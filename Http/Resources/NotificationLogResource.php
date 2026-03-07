<?php

namespace Modules\Software\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationLogResource extends JsonResource
{
    public function toArray($request)
    {
        $response = is_array($this->response) ? $this->response : json_decode($this->response, true);

        $lang = request('lang', config('app.locale', 'en'));
        $date = Carbon::parse($this->created_at)->locale($lang)->isoFormat('D MMMM YYYY');

        return [
            'id'        => $this->id,
            'title'     => $response['title']   ?? $response['body'] ?? $this->content,
            'image'     => $response['image']   ?? '',
            'date'      => $date,
            'is_new'    => Carbon::parse($this->created_at)->isAfter(Carbon::now()->subDay()) ? 1 : 0,
            'new_title' => ""
        ];
    }
}
