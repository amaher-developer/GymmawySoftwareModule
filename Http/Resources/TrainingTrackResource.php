<?php

namespace Modules\Software\Http\Resources;

use Modules\Software\Classes\TypeConstants;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingTrackResource extends JsonResource
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
        return
            [
                "id" => $this->id,
                "title" => Carbon::parse(@$this->created_at)->translatedFormat('d F Y'),
                "image" =>  asset('resources/assets/new_front/images/report_track.png'),
                "short_content" => trans('sw.report_msg', ['name' => 'ahmed maher']),
                "is_new" => @$this->is_new ? $this->is_new : 0,
                "new_title" => @$this->is_new ? trans('sw.new') : '',
            ];
    }
}


