<?php

namespace Modules\Software\Database\Seeds;

use Illuminate\Database\Seeder;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymSubscriptionCategory;
use Modules\Software\Models\GymSubscriptionOption;
use Modules\Software\Models\GymSubscriptionOptionGroup;

/**
 * DietPlateMenuSeeder
 *
 * Seeds the "Diet Plate" opening-offer menu (عرض افتتاح) exactly as printed in the
 * official price sheet: for every protein weight (100 / 150 / 200 / 250 جرام) and every
 * meal count (1 to 5 meals/day) there is a subscription whose "عدد الأيام" option group
 * offers 14 / 20 / 26 / 30 day durations at the exact printed price.
 *
 * The same full menu is repeated identically under both categories:
 *   - التضخيم (Amplification / bulking)
 *   - التنشيف (Cutting / drying)
 *
 * Run:
 *   php artisan db:seed --class=Modules\\Software\\Database\\Seeds\\DietPlateMenuSeeder
 *
 * Safe to re-run (idempotent — uses firstOrCreate throughout).
 */
class DietPlateMenuSeeder extends Seeder
{
    private int $branchId = 1;
    private int $userId   = 1;

    // SAR price per meal per day, keyed by protein weight (grams) — derived from the price sheet.
    private array $ratePerMealPerDayByWeight = [
        100 => 14,
        150 => 16.5,
        200 => 18,
        250 => 20,
    ];

    private array $weights = [100, 150, 200, 250];

    private array $dayOptions = [14, 20, 26, 30];

    private array $mealTiers = [
        1 => ['ar' => 'وجبة واحدة',  'en' => '1 Meal'],
        2 => ['ar' => 'وجبتان',      'en' => '2 Meals'],
        3 => ['ar' => 'ثلاث وجبات',  'en' => '3 Meals'],
        4 => ['ar' => 'أربع وجبات',  'en' => '4 Meals'],
        5 => ['ar' => 'خمس وجبات',   'en' => '5 Meals'],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    public function run(): void
    {
        $this->clearOldData();

        $categories = $this->seedSubscriptionCategories();
        $this->seedMenu($categories);

        $this->command->info('✅ DietPlateMenuSeeder completed successfully.');
    }

    // ── 0. Clear previously seeded data ──────────────────────────────────────

    private function clearOldData(): void
    {
        $this->command->warn('  Clearing old Diet Plate menu data...');

        $catNames   = ['التضخيم', 'التنشيف'];
        $categories = GymSubscriptionCategory::withTrashed()
            ->where('branch_setting_id', $this->branchId)
            ->whereIn('name_ar', $catNames)
            ->get();

        $categoryIds     = $categories->pluck('id');
        $subscriptionIds = GymSubscription::withTrashed()
            ->whereIn('subscription_category_id', $categoryIds)
            ->pluck('id');
        $groupIds = GymSubscriptionOptionGroup::withTrashed()
            ->whereIn('subscription_id', $subscriptionIds)
            ->pluck('id');

        GymSubscriptionOption::withTrashed()->whereIn('option_group_id', $groupIds)->forceDelete();
        GymSubscriptionOptionGroup::withTrashed()->whereIn('subscription_id', $subscriptionIds)->forceDelete();
        GymSubscription::withTrashed()->whereIn('id', $subscriptionIds)->forceDelete();
        GymSubscriptionCategory::withTrashed()->whereIn('id', $categoryIds)->forceDelete();

        $this->command->info('  ✓ Old data cleared.');
    }

    // ── 1. Subscription categories ───────────────────────────────────────────

    private function seedSubscriptionCategories(): array
    {
        $defs = [
            'amplification' => ['name_ar' => 'التضخيم', 'name_en' => 'Amplification'],
            'cutting'       => ['name_ar' => 'التنشيف', 'name_en' => 'Cutting'],
        ];

        $result = [];
        foreach ($defs as $key => $d) {
            $result[$key] = GymSubscriptionCategory::firstOrCreate(
                ['branch_setting_id' => $this->branchId, 'name_ar' => $d['name_ar']],
                array_merge($d, [
                    'user_id'           => $this->userId,
                    'branch_setting_id' => $this->branchId,
                ])
            );
        }
        return $result;
    }

    // ── 2. Full menu (weight × meal-count subscriptions) per category ───────

    private function seedMenu(array $categories): void
    {
        foreach ($categories as $category) {
            foreach ($this->weights as $weight) {
                $rate = $this->ratePerMealPerDayByWeight[$weight];

                foreach ($this->mealTiers as $mealCount => $tier) {
                    $nameAr = "{$category->name_ar} - وزن {$weight} جرام - {$tier['ar']}";
                    $nameEn = "{$category->name_en} - {$weight}g - {$tier['en']}";

                    $subscription = GymSubscription::firstOrCreate(
                        ['branch_setting_id' => $this->branchId, 'name_ar' => $nameAr],
                        [
                            'user_id'                   => $this->userId,
                            'branch_setting_id'         => $this->branchId,
                            'name_ar'                   => $nameAr,
                            'name_en'                   => $nameEn,
                            'price'                      => 0,
                            'period'                     => 30,
                            'workouts'                   => 30,
                            'workouts_per_day'           => 1,
                            'freeze_limit'               => 0,
                            'number_times_freeze'        => 0,
                            'is_expire_changeable'       => true,
                            'is_system'                  => true,
                            'is_web'                     => true,
                            'is_mobile'                  => true,
                            'subscription_category_id'  => $category->id,
                            'content_ar'                 => "وزن {$weight} جرام - {$tier['ar']} يومياً",
                            'content_en'                 => "{$weight}g - {$tier['en']} daily",
                        ]
                    );

                    $this->seedDaysGroup($subscription, $mealCount, $rate);
                }
            }
        }
    }

    // ── Option group: عدد الأيام (14 / 20 / 26 / 30) ─────────────────────────

    private function seedDaysGroup(GymSubscription $subscription, int $mealCount, float $ratePerMealPerDay): void
    {
        $group = GymSubscriptionOptionGroup::firstOrCreate(
            [
                'subscription_id' => $subscription->id,
                'name_ar'         => 'عدد الأيام',
            ],
            [
                'branch_setting_id' => $this->branchId,
                'subscription_id'   => $subscription->id,
                'name_ar'           => 'عدد الأيام',
                'name_en'           => 'Number of Days',
                'selection_type'    => GymSubscriptionOptionGroup::SELECTION_SINGLE,
                'is_required'       => true,
                'list_order'        => 1,
                'source_type'       => 'fixed',
                'is_system'         => true,
                'is_web'            => true,
                'is_mobile'         => true,
            ]
        );

        foreach ($this->dayOptions as $i => $days) {
            $price  = (int) round($mealCount * $ratePerMealPerDay * $days);
            $nameAr = "{$days} يوم";
            $nameEn = "{$days} Days";

            GymSubscriptionOption::firstOrCreate(
                [
                    'option_group_id' => $group->id,
                    'name_ar'         => $nameAr,
                ],
                [
                    'branch_setting_id' => $this->branchId,
                    'option_group_id'   => $group->id,
                    'name_ar'           => $nameAr,
                    'name_en'           => $nameEn,
                    'price_modifier'    => $price,
                    'field_overrides'   => ['workouts' => $days],
                    'list_order'        => $i + 1,
                ]
            );
        }
    }
}
