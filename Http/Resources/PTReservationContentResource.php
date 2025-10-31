<?php

namespace Modules\Software\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PTReservationContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $records = [];
        if(@$this['work_days']) {
            foreach ($this['work_days'] as $key => $work_day) {
                if(@$work_day['status']) {
                    $records[$key]['day'] = week_name($key, request('lang'));
                    $records[$key]['time_from'] = Carbon::parse(@$work_day['start'])->format('g:i A');;
                    $records[$key]['time_to'] = Carbon::parse(@$work_day['end'])->format('g:i A');
                }
            }
        }
            return $records;

    }
}
