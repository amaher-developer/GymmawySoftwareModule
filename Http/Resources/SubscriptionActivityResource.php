<?php

namespace Modules\Software\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionActivityResource extends JsonResource
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
                "name" => @$this->activity->name ?? '',
                "image" => @$this->activity->image_name ? $this->activity->image : @env('APP_WEBSITE').@env('APP_URL_ASSETS') . 'placeholder_black.png',
                "price" => @$this->activity->price ? $this->activity->price . ' ' . env('APP_CURRENCY_'.strtoupper($this->lang)) . ' ' : '',
                "training_times" => @$this->training_times ? $this->training_times . ' '. trans('sw.pt_class_2').' '   : 0,
            ];
    }
}


