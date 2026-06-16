<?php

namespace Modules\Software\Http\Resources;

use Modules\Generic\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $setting = Setting::select('vat_details')->first();
        return
            [
                "id" => $this->id,
                "name" => $this->name,
                "image" => $this->image_name ? $this->image : @env('APP_URL').@env('APP_URL_ASSETS') . 'placeholder_black.png',
                "price" => $this->price ? number_format($this->price + ( $this->price * (@$setting->vat_details['vat_percentage'] / 100)) , 2) . ' ' . env('APP_CURRENCY_'.strtoupper($this->lang)) . ' ' : '0',
                "content" => $this->content ? $this->content : '',
                "is_payment" => @env('APP_WEB_PAYMENT_ACTIVITY') == 1 ? 1 : 0,
                "payment_link" => @env('APP_WEB_PAYMENT_ACTIVITY') == 1
                    ? route('sw.activity-mobile', ['id' => $this->id, 'lang' => $this->lang])
                    : "",
                "trainer_name" => $this->trainer ? $this->trainer->name : '',
                "trainer_image" => $this->trainer && $this->trainer->image_name ? asset('uploads/pt_trainers/' . $this->trainer->image_name) : '',
                "schedule" => $this->_getSchedule(),
            ];
    }
    private function _getSchedule(): array
    {
        $details = $this->reservation_details;
        if (!$details || !isset($details['work_days'])) return [];
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $result = [];
        foreach ($details['work_days'] as $index => $day) {
            if (!empty($day['status'])) {
                $result[] = [
                    'day'   => $days[$index] ?? '',
                    'start' => $day['start'] ?? '',
                    'end'   => $day['end'] ?? '',
                ];
            }
        }
        return $result;
    }
}


