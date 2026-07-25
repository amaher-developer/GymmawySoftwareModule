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
        $memberName = @$this->member->name ?: '-';
        $summary = trim(strip_tags((string) @$this->notes));
        if (mb_strlen($summary) > 120) {
            $summary = mb_substr($summary, 0, 120) . '...';
        }
        if ($summary === '') {
            $summary = trans('sw.report_msg', ['name' => $memberName]);
        }
        return
            [
                "id" => $this->id,
                "title" => Carbon::parse(@$this->created_at)->translatedFormat('d F Y'),
                "image" =>  asset('resources/assets/new_front/images/report_track.png'),
                "short_content" => $summary,
                "is_new" => @$this->is_new ? $this->is_new : 0,
                "new_title" => @$this->is_new ? trans('sw.new') : '',
            ];
    }
}


