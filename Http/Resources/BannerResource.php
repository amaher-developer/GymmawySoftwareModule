<?php

namespace Modules\Software\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        Carbon::setLocale($request->lang);
        $this->is_new = rand(0,1);
        $displayDate  = $this->type == 2 && $this->event_date
            ? Carbon::parse($this->event_date)->translatedFormat('d F Y')
            : Carbon::parse($this->created_at)->translatedFormat('d F Y');
        return
            [
                "id"         => $this->id,
                "title"      => $this->title,
                "image"      => $this->image_name ? $this->image : @env('APP_URL').@env('APP_URL_ASSETS') . 'placeholder_black.png',
                "date"       => $displayDate,
                "event_date" => $this->event_date ? Carbon::parse($this->event_date)->toDateString() : null,
                "is_new"     => @$this->is_new ? $this->is_new : 0,
                "new_title"  => @$this->is_new ? trans('sw.new') : '',
                'type'       => (int) ($this->type ?? 1),
            ];
    }
}


