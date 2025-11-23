<?php

namespace Modules\Software\Http\Resources;

use Modules\Software\Classes\TypeConstants;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerTrackContentResource extends JsonResource
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
                "title" => Carbon::parse(@$this->created_at)->translatedFormat('d F Y'),
                "image" => asset('resources/assets/new_front/images/report_track.png'),
                "height" => @$this->height .' cm ',
                "weight" => @$this->weight . ' kg ',
                "report" => @$this->notes,
                "date" => Carbon::parse(@$this->created_at)->translatedFormat('d F Y'),
                "short_content" => trans('sw.report_msg', ['name' => 'ahmed maher'])
            ];
    }
}


