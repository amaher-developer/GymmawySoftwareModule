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
                "id" => @$this->pt_trainer->id ?? $this->id,
                "name" => @$this->pt_trainer->name,
                "phone" => @$this->pt_trainer->phone,
                "specialization" => @$this->pt_trainer->specialization ?? '',
                "bio" => @$this->pt_trainer->bio ?? '',
                "is_completed" => (int) ($this->is_completed ?? 0),
                "is_complete_msg" => $this->is_completed ? trans('sw.reservation_completed') : '',
                "image" => @$this->pt_trainer->image_name ? @$this->pt_trainer->image : @env('APP_WEBSITE').@env('APP_URL_ASSETS') . 'placeholder_black.png',
                "reservations" => $this->reservation_details ? ((@isset($this->reservation_details['is_mobile']) && ($this->reservation_details['is_mobile'] == 0)) ? []  : array_values((new PTReservationContentResource($this->reservation_details))->toArray(request()))) : []
            ];
            
    }
}


