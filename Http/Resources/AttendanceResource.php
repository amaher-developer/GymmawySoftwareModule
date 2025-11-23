<?php

namespace Modules\Software\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
        return
            [
                "id" => $this->id,
                "date" => Carbon::parse(@$this->created_at)->translatedFormat('d F Y'),
                "time" => Carbon::parse(@$this->created_at)->translatedFormat('h:i a'),
                "title" => trans('sw.training_class'),
                "type" => trans('sw.gym')
            ];
    }
}


