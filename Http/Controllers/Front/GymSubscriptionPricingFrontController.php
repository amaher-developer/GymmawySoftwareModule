<?php

namespace Modules\Software\Http\Controllers\Front;

use Illuminate\Http\Request;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Services\SubscriptionPricingService;

class GymSubscriptionPricingFrontController extends GymGenericFrontController
{
    // Channel constants — mirrors TypeConstants::CHANNEL_* for readability
    const CHANNEL_SYSTEM  = TypeConstants::CHANNEL_SYSTEM;   // 1 — admin/system
    const CHANNEL_WEBSITE = TypeConstants::CHANNEL_WEBSITE;  // 2 — customer web
    const CHANNEL_MOBILE  = TypeConstants::CHANNEL_MOBILE_APP; // 3 — mobile app

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * POST /sw/subscriptions/{subscriptionId}/calculate-price
     * Body: { "option_ids": [1, 3, 5] }
     * Returns live price breakdown usable by Admin, Web and Mobile.
     */
    public function calculate(Request $request, int $subscriptionId)
    {
        $request->validate([
            'option_ids'   => 'nullable|array',
            'option_ids.*' => 'integer',
        ]);

        $subscription = GymSubscription::branch()->findOrFail($subscriptionId);
        $service      = new SubscriptionPricingService();
        $result       = $service->calculate($subscription, $request->option_ids ?? []);

        return response()->json($result);
    }

    /**
     * GET /sw/subscriptions/{subscriptionId}/options?channel=1|2|3
     *
     * channel=1 (system/admin) → filter by is_system = true
     * channel=2 (web)          → filter by is_web = true
     * channel=3 (mobile)       → filter by is_mobile = true
     * default                  → system (admin view)
     *
     * Returns option groups with their options for the customer selection UI.
     */
    public function options(Request $request, int $subscriptionId)
    {
        $channel = (int) $request->query('channel', self::CHANNEL_SYSTEM);

        $subscription = GymSubscription::branch()
            ->with(['option_groups' => function ($q) use ($channel) {
                $q->where($this->channelFilter($channel), true)
                  ->orderBy('list_order')
                  ->with([
                      'options' => fn($q2) => $q2
                          ->orderBy('list_order')
                          ->with([
                              'product'  => fn($q3) => $q3->select(
                                  'id', 'name_ar', 'name_en', 'display_name_ar', 'display_name_en',
                                  'image', 'is_meal', 'calories', 'protein', 'carbs', 'fat', 'category_id'
                              ),
                              'activity' => fn($q3) => $q3->select('id', 'name_ar', 'name_en', 'image'),
                          ]),
                      'category' => fn($q2) => $q2->select('id', 'name_ar', 'name_en'),
                  ]);
            }])
            ->findOrFail($subscriptionId);

        return response()->json([
            'subscription' => [
                'id'    => $subscription->id,
                'name'  => $subscription->name,
                'price' => (float) $subscription->price,
            ],
            'option_groups' => $subscription->option_groups,
            'channel'       => $channel,
        ]);
    }

    /**
     * GET /sw/subscriptions/{subscriptionId}/member-activities
     * Returns the membership's allowed activities (+ activity_limit) for
     * staff to pick from when creating/renewing a specific member's
     * subscription, including which activities need an explicit trainer
     * choice (more than one active trainer assigned).
     */
    public function memberActivities(int $subscriptionId)
    {
        $subscription = GymSubscription::branch()
            ->with(['activities.activity.activeActivityTrainers.trainer'])
            ->findOrFail($subscriptionId);

        $availabilityService = new \Modules\Software\Services\ActivityAvailabilityService();

        $activities = $subscription->activities->map(function ($entry) use ($availabilityService) {
            $activity = $entry->activity;

            return [
                'activity_id' => $entry->activity_id,
                'name' => $activity->name ?? null,
                'training_times' => $entry->training_times,
                'requires_trainer_selection' => $activity ? $availabilityService->requiresTrainerSelection($activity) : false,
                'trainers' => $activity ? $activity->activeActivityTrainers->map(fn ($at) => [
                    'activity_trainer_id' => $at->id,
                    'trainer_name' => optional($at->trainer)->name,
                ])->values() : [],
            ];
        })->values();

        return response()->json([
            'subscription_id' => $subscription->id,
            'activity_limit' => $subscription->activity_limit,
            'activities' => $activities,
        ]);
    }

    /**
     * Returns the DB column name that corresponds to the requested channel.
     */
    private function channelFilter(int $channel): string
    {
        return match ($channel) {
            self::CHANNEL_WEBSITE => 'is_web',
            self::CHANNEL_MOBILE  => 'is_mobile',
            default               => 'is_system',
        };
    }
}
