<?php

namespace Modules\Software\Services;

use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymSubscriptionOption;

class SubscriptionPricingService
{
    /**
     * Subscription/member-subscription fields an option's `field_overrides` JSON is allowed to
     * set. Whitelisted to prevent a crafted option from overwriting arbitrary columns
     * (id, amount_paid, status, member_id, ...).
     */
    public const OVERRIDABLE_FIELDS = ['workouts', 'workouts_per_day', 'period', 'freeze_limit', 'number_times_freeze'];

    /**
     * Calculate the final price (and any option-driven field overrides) for a subscription
     * given selected option IDs.
     *
     * Returns:
     *   base_price        — subscription base price (no options)
     *   options_total     — sum of price_modifier for selected options
     *   total             — base_price + options_total
     *   overrides         — map of subscription-field => value, merged from each selected
     *                       option's `field_overrides` JSON (later option wins on conflict),
     *                       filtered to self::OVERRIDABLE_FIELDS. Empty when no option overrides
     *                       anything — callers should merge this over their own defaults.
     *   selected_options  — array of ['option_id', 'name', 'price_modifier', 'field_overrides'] for snapshot storage
     */
    public function calculate(GymSubscription $subscription, array $selectedOptionIds): array
    {
        $basePrice = (float) $subscription->price;

        if (empty($selectedOptionIds)) {
            return [
                'base_price'       => $basePrice,
                'options_total'    => 0,
                'total'            => $basePrice,
                'overrides'        => [],
                'selected_options' => [],
            ];
        }

        $options = GymSubscriptionOption::with(['product', 'activity'])
            ->whereIn('id', $selectedOptionIds)
            ->whereHas('group', fn($q) => $q->where('subscription_id', $subscription->id))
            ->get();

        $optionsTotal = $options->sum('price_modifier');

        $overrides = [];
        foreach ($options as $opt) {
            foreach ((array) $opt->field_overrides as $field => $value) {
                if (in_array($field, self::OVERRIDABLE_FIELDS, true)) {
                    $overrides[$field] = $value;
                }
            }
        }

        $selectedOptions = $options->map(function ($opt) {
            if ($opt->product_id && $opt->product) {
                $nameAr = $opt->product->getRawOriginal('display_name_ar') ?: $opt->product->name_ar ?? '';
                $nameEn = $opt->product->getRawOriginal('display_name_en') ?: $opt->product->name_en ?? $nameAr;
            } elseif ($opt->activity_id && $opt->activity) {
                $nameAr = $opt->activity->name_ar ?? '';
                $nameEn = $opt->activity->name_en ?? $nameAr;
            } else {
                $nameAr = $opt->name_ar ?? '';
                $nameEn = $opt->name_en ?? $nameAr;
            }
            return [
                'option_id'       => $opt->id,
                'product_id'      => $opt->product_id,
                'activity_id'     => $opt->activity_id,
                'name_ar'         => $nameAr,
                'name_en'         => $nameEn,
                'price_modifier'  => (float) $opt->price_modifier,
                'field_overrides' => $opt->field_overrides ?: null,
            ];
        })->values()->all();

        return [
            'base_price'       => $basePrice,
            'options_total'    => (float) $optionsTotal,
            'total'            => $basePrice + (float) $optionsTotal,
            'overrides'        => $overrides,
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

        $options = GymSubscriptionOption::with('group')->whereIn('id', $selectedOptionIds)->get()->keyBy('id');

        $rows = [];
        $usedSingleGroups = [];
        foreach ($selectedOptionIds as $optionId) {
            if (!isset($options[$optionId])) {
                continue;
            }
            $opt   = $options[$optionId];
            $group = $opt->group;
            if ($group && $group->selection_type === 'single') {
                if (isset($usedSingleGroups[$group->id])) {
                    continue; // only keep first selection for single-select groups
                }
                $usedSingleGroups[$group->id] = true;
            }
            $rows[] = [
                'branch_setting_id'       => $branchSettingId,
                'member_subscription_id'  => $memberSubscription->id,
                'option_id'               => $optionId,
                'price_snapshot'          => (float) $opt->price_modifier,
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
