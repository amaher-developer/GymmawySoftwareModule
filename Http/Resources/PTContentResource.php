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

        // Build classes from the subscription's classes relation
        $classes = [];
        $vatPercent = @$setting->vat_details['vat_percentage'] ?? 0;
        $ptClasses = $this->pt_subscription ? ($this->pt_subscription->classes ?? collect()) : collect();
        foreach ($ptClasses as $class) {
            $className  = $class->{'name_' . $lang} ?? $class->name_en ?? $class->name ?? '';
            $classTitle = $class->{'title_' . $lang} ?? $class->title_en ?? $class->title ?? '';
            $classDesc  = $class->{'description_' . $lang} ?? $class->description_en ?? $class->description ?? '';
            $price      = $class->price ?? null;

            $classPrice = is_numeric($price)
                ? number_format($price + ($price * ($vatPercent / 100)), 2) . ' ' . env('APP_CURRENCY_' . strtoupper($lang))
                : '';

            $classTrainers = [];
            if ($class->activeClassTrainers && is_iterable($class->activeClassTrainers)) {
                foreach ($class->activeClassTrainers as $ct) {
                    $classTrainers[] = [
                        'id'       => $ct->trainer->id ?? null,
                        'name'     => $ct->trainer->name ?? '',
                        'schedule' => [
                            'day'   => $ct->day ?? '',
                            'start' => $ct->from_time ?? '',
                            'end'   => $ct->to_time ?? '',
                        ],
                    ];
                }
            }

            $classes[] = [
                'id'          => $class->id,
                'name'        => $className,
                'title'       => $classTitle,
                'description' => $classDesc,
                'type'        => $class->type ?? '',
                'price'       => $classPrice,
                'trainers'    => $classTrainers,
            ];
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
            "trainers"     => $this->_buildTrainers($lang),
            "is_payment"   => @env('APP_WEB_PAYMENT_PT_SUBSCRIPTION') == 1 ? 1 : 0,
            "payment_link" => @env('APP_WEB_PAYMENT_PT_SUBSCRIPTION') == 1
                                ? route('sw.pt-subscription-mobile', ['id' => $this->id, 'lang' => $lang])
                                : "",
        ];
    }

    private function _buildTrainers($lang)
    {
        // Try old pt_subscription_trainer relation first
        $oldTrainers = $this->pt_subscription_trainer ?? collect();
        if ($oldTrainers && $oldTrainers->isNotEmpty()) {
            return PTTrainerContentResource::collection($oldTrainers);
        }

        // Fall back to new activeClassTrainers relation
        $trainers = [];
        foreach ($this->activeClassTrainers ?? [] as $ct) {
            $t = $ct->trainer;
            if (!$t) continue;
            $trainers[] = [
                'id'             => $t->id,
                'name'           => $t->name ?? '',
                'phone'          => $t->phone ?? '',
                'specialization' => $t->specialization ?? '',
                'bio'            => $t->bio ?? '',
                'is_completed'   => 0,
                'is_complete_msg'=> '',
                'image'          => $t->image_name
                    ? $t->image
                    : (env('APP_URL') . env('APP_URL_ASSETS') . 'placeholder_black.png'),
                'reservations'   => [],
            ];
        }
        return $trainers;
    }
}


