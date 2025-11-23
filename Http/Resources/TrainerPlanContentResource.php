<?php

namespace Modules\Software\Http\Resources;

use Modules\Software\Classes\TypeConstants;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerPlanContentResource extends JsonResource
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
                "title" => $this->title,
                "image" => $this->type = TypeConstants::DIET_PLAN_TYPE ? asset('resources/assets/new_front/images/diet_training.png') : asset('resources/assets/new_front/images/bar_training.png'),
                "plan_details" => $this->plan_details,
                "date" => Carbon::parse(@$this->created_at)->translatedFormat('d F Y'),
            ];
    }
}


