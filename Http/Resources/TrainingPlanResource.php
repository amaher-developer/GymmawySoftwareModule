<?php

namespace Modules\Software\Http\Resources;

use Modules\Software\Classes\TypeConstants;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->is_new = rand(0,1);
        return
            [
                "id" => $this->id,
                "title" => $this->title,
                "image" => $this->type = TypeConstants::DIET_PLAN_TYPE ? asset('resources/assets/front/images/diet_training.png') : asset('resources/assets/front/images/bar_training.png'),
                "short_content" => $this->title,
                "is_new" => @$this->is_new ? $this->is_new : 0,
                "new_title" => @$this->is_new ? trans('sw.new') : '',
            ];
    }
}
