<?php

namespace Modules\Software\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PTTrainerContentResource extends JsonResource
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
                "id" => $this->id,
                "name" => @$this->pt_trainer->name,
                "phone" => @$this->pt_trainer->phone,
                "is_completed" => $this->is_completed,
                "is_complete_msg" => $this->is_completed ? trans('sw.reservation_completed') : '',
//                "work_hours" => $this->pt_trainer->work_hours,
                "image" => @$this->pt_trainer->image_name ? @$this->pt_trainer->image : @env('APP_WEBSITE').'placeholder_black.png',
                "reservations" => $this->reservation_details ? ((@isset($this->reservation_details['is_mobile']) && ($this->reservation_details['is_mobile'] == 0)) ? []  : new PTReservationContentResource($this->reservation_details)) : []
            ];
    }
}
