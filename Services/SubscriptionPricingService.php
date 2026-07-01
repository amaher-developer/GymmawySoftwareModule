<?php

namespace Modules\Software\Services;

use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymSubscriptionOption;

class SubscriptionPricingService
{
    /**
     * Calculate the final price for a subscription given selected option IDs.
     *
     * Returns:
     *   base_price        — subscription base price (no options)
     *   options_total     — sum of price_modifier for selected options
     *   total             — base_price + options_total
     *   selected_options  — array of ['option_id', 'name', 'price_modifier'] for snapshot storage
     */
    public function calculate(GymSubscription $subscription, array $selectedOptionIds): array
    {
        $basePrice = (float) $subscription->price;

        if (empty($selectedOptionIds)) {
            return [
                'base_price'       => $basePrice,
                'options_total'    => 0,
                'total'            => $basePrice,
                'selected_options' => [],
            ];
        }

        $options = GymSubscriptionOption::with(['product', 'activity'])
            ->whereIn('id', $selectedOptionIds)
            ->whereHas('group', fn($q) => $q->where('subscription_id', $subscription->id))
            ->get();

        $optionsTotal = $options->sum('price_modifier');

        $selectedOptions = $options->map(function ($opt) {
            if ($opt->product_id && $opt->product) {
                $nameAr = $opt->product->getRawOriginal('display_name_ar') ?: $opt->product->name_ar ?? '';
                $nameEn = $opt->product->getRawOriginal('display_name_en') ?: $opt->product->name_en ?? $nameAr;
            } elseif ($opt->activity_id && $opt->activity) {
                $nameAr = $opt->activity->name_ar ?? '';
                $nameEn = $opt->activity->name_en ?? $nameAr;
            } else {
                $nameAr = $nameEn = '';
            }
            return [
                'option_id'      => $opt->id,
                'product_id'     => $opt->product_id,
                'activity_id'    => $opt->activity_id,
                'name_ar'        => $nameAr,
                'name_en'        => $nameEn,
                'price_modifier' => (float) $opt->price_modifier,
            ];
        })->values()->all();

        return [
            'base_price'       => $basePrice,
            'options_total'    => (float) $optionsTotal,
            'total'            => $basePrice + (float) $optionsTotal,
            'selected_options' => $selectedOptions,
        ];
    }

    /**
     * Persist selected options as a price snapshot on a member subscription.
     * Call this after the GymMemberSubscription record is created/updated.
     */
    public function saveSelectedOptions(\Modules\Software\Models\GymMemberSubscription $memberSubscription, array $selectedOptionIds, int $branchSettingId): void
    {
        $memberSubscription->selected_options()->delete();

        if (empty($selectedOptionIds)) {
            return;
        }

        $options = GymSubscriptionOption::whereIn('id', $selectedOptionIds)->get()->keyBy('id');

        $rows = [];
        foreach ($selectedOptionIds as $optionId) {
            if (!isset($options[$optionId])) {
                continue;
            }
            $rows[] = [
                'branch_setting_id'       => $branchSettingId,
                'member_subscription_id'  => $memberSubscription->id,
                'option_id'               => $optionId,
                'price_snapshot'          => (float) $options[$optionId]->price_modifier,
                'created_at'              => now(),
                'updated_at'              => now(),
            ];
        }

        if (!empty($rows)) {
            \Modules\Software\Models\GymMemberSubscriptionOption::insert($rows);
        }
    }

    public function buildOptionsNote(array $selectedOptions, string $lang = 'ar'): string
    {
        if (empty($selectedOptions)) {
            return '';
        }
        $nameKey = $lang === 'en' ? 'name_en' : 'name_ar';
        $label   = $lang === 'en' ? 'Options' : 'اختيارات';
        $names   = array_filter(array_column($selectedOptions, $nameKey));
        return empty($names) ? '' : $label . ': ' . implode('، ', $names);
    }
}
