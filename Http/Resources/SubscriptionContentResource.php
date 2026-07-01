<?php

namespace Modules\Software\Http\Resources;

use Modules\Generic\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class SubscriptionContentResource extends JsonResource
{
    public function toArray($request)
    {
        $setting = Setting::select('vat_details')->first();
        $currency = env('APP_CURRENCY_'.strtoupper($this->lang));
        $vatPct = (float)@$setting->vat_details['vat_percentage'];

        $basePrice = (float)$this->price;
        $discountValue = (float)$this->default_discount_value;
        $discountType  = $this->default_discount_type; // 1 = percentage, 0 = fixed

        if ($discountValue > 0) {
            $discountedPrice = $discountType == 1
                ? $basePrice - ($basePrice * $discountValue / 100)
                : $basePrice - $discountValue;
            $discountedPrice = max(0, $discountedPrice);
        } else {
            $discountedPrice = $basePrice;
        }

        $finalPrice = $discountedPrice + ($discountedPrice * $vatPct / 100);

        return [
            "id"                   => $this->id,
            "name"                 => Str::limit(@$this->name, 30),
            "image"                => $this->image_name ? $this->image : @env('APP_URL').@env('APP_URL_ASSETS') . 'placeholder_black.png',
            "price"                => number_format($finalPrice, 2). ' ' . $currency . ' ',
            "base_price_raw"       => $basePrice,
            "content"              => strip_tags(@$this->content),
            "period"               => $this->period . ' '. trans('sw.day_2'),
            "workouts"             => $this->workouts,
            "freeze_limit"         => $this->freeze_limit,
            "number_times_freeze"  => $this->number_times_freeze,
            "activities"           => @$this->activities ? SubscriptionActivityResource::collection($this->activities) : [],
            "option_groups"        => $this->buildOptionGroupsForApi(),
            "products"             => $this->buildProductsForApi(),
            "is_payment"           => @env('APP_WEB_PAYMENT_SUBSCRIPTION') == 1 ? 1 : 0,
            "payment_link"         => @env('APP_WEB_PAYMENT_SUBSCRIPTION') == 1 ? route('sw.subscription-mobile', ['id' => $this->id]) : "",
        ];
    }

    private function buildOptionGroupsForApi(): array
    {
        try {
            $groups = $this->option_groups()
                ->with(['options' => fn($q) => $q->orderBy('list_order')])
                ->where('is_mobile', true)
                ->orderBy('list_order')
                ->get();

            return $groups->map(fn($group) => [
                'id'             => $group->id,
                'name'           => $group->name,
                'name_ar'        => $group->name_ar,
                'name_en'        => $group->name_en,
                'selection_type' => $group->selection_type,
                'is_required'    => (bool) $group->is_required,
                'options'        => $group->options->map(fn($opt) => [
                    'id'             => $opt->id,
                    'name'           => $opt->name,
                    'name_ar'        => $opt->name_ar,
                    'name_en'        => $opt->name_en,
                    'price_modifier' => (float) $opt->price_modifier,
                ])->values()->all(),
            ])->values()->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function buildProductsForApi(): array
    {
        try {
            $subscriptionProducts = $this->subscription_products()
                ->with('product')
                ->orderBy('list_order')
                ->get();

            return $subscriptionProducts->map(fn($sp) => [
                'id'             => $sp->product->id,
                'display_name'   => $sp->product->display_name,
                'display_name_ar'=> $sp->product->getRawOriginal('display_name_ar') ?: $sp->product->name_ar,
                'display_name_en'=> $sp->product->getRawOriginal('display_name_en') ?: $sp->product->name_en,
                'image'          => $sp->product->image,
                'is_replaceable' => (bool) $sp->is_replaceable,
                'is_meal'        => (bool) $sp->product->is_meal,
                'calories'       => $sp->product->calories,
                'protein'        => $sp->product->protein,
                'carbs'          => $sp->product->carbs,
                'fat'            => $sp->product->fat,
            ])->values()->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
