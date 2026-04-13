<?php

namespace Modules\Software\Http\Resources;

use Modules\Generic\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

class PTContentResource extends JsonResource
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
        $lang = $request->get('lang') ?: env('DEFAULT_LANG', 'en');
        $nameKey = 'name_' . $lang;
        $contentKey = 'content_' . $lang;

        $name = $this->getRawOriginal($nameKey)
            ?? $this->getRawOriginal('name_en')
            ?? @$this->pt_subscription->getRawOriginal($nameKey)
            ?? @$this->pt_subscription->getRawOriginal('name_en')
            ?? '';

        $content = $this->getRawOriginal($contentKey)
            ?? $this->getRawOriginal('content_en')
            ?? '';

        // Fetch/classes and map detailed info
        $classes = [];
        if ($this->classes && is_iterable($this->classes)) {
            foreach ($this->classes as $class) {
                // pick localized fields
                $className    = $class->{'name_' . $lang} ?? $class->name_en ?? $class->name ?? '';
                $classTitle   = $class->{'title_' . $lang} ?? $class->title_en ?? $class->title ?? '';
                $classDesc    = $class->{'description_' . $lang} ?? $class->description_en ?? $class->description ?? '';
                $type         = $class->type ?? '';
                $price        = $class->price ?? null;
                $vatPercent   = @$setting->vat_details['vat_percentage'] ?? 0;

                $classPrice   = is_numeric($price)
                    ? number_format($price + ($price * ($vatPercent / 100)), 2) . ' ' . env('APP_CURRENCY_' . strtoupper($lang))
                    : '';

                // trainers for this class with their schedule (current method is via relation, fallback empty)
                $classTrainers = [];
                if (isset($class->activeClassTrainers) && is_iterable($class->activeClassTrainers)) {
                    foreach ($class->activeClassTrainers as $ct) {
                        $trainerArr = [
                            'id'   => $ct->trainer->id ?? null,
                            'name' => $ct->trainer->name ?? '',
                            'schedule' => [
                                'day'   => $ct->day ?? '',
                                'start' => $ct->from_time ?? '',
                                'end'   => $ct->to_time ?? '',
                            ]
                        ];
                        $classTrainers[] = $trainerArr;
                    }
                }

                $classes[] = [
                    'id'          => $class->id,
                    'name'        => $className,
                    'title'       => $classTitle,
                    'description' => $classDesc,
                    'type'        => $type,
                    'price'       => $classPrice,
                    'trainers'    => $classTrainers,
                    // add more fields as needed...
                ];
            }
        }

        return [
            "id"           => $this->id,
            "name"         => $name,
            "image"        => $this->pt_subscription->image_name
                                ? $this->pt_subscription->image
                                : @env('APP_URL').@env('APP_URL_ASSETS') . 'placeholder_black.png',
            "price"        => $this->price
                                ? number_format(
                                    $this->price + ($this->price * (@$setting->vat_details['vat_percentage'] / 100)), 2
                                  ) . ' ' . env('APP_CURRENCY_'.strtoupper($lang)) . ' '
                                : '',
            "classes"      => $classes,
            "content"      => $content,
            "is_reserved"  => $this->is_reserved ?? 0,
            "trainers"     => @$this->pt_subscription_trainer
                                ? PTTrainerContentResource::collection(@$this->pt_subscription_trainer)
                                : [],
            "is_payment"   => @env('APP_WEB_PAYMENT_PT_SUBSCRIPTION') == 1 ? 1 : 0,
            "payment_link" => @env('APP_WEB_PAYMENT_PT_SUBSCRIPTION') == 1
                                ? route('sw.pt-subscription-mobile', ['id' => $this->id, 'lang' => $lang])
                                : "",
        ];
    }
}


