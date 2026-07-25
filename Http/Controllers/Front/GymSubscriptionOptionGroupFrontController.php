<?php

namespace Modules\Software\Http\Controllers\Front;

use Illuminate\Container\Container as Application;
use Illuminate\Http\Request;
use Modules\Software\Models\GymMemberSubscriptionOption;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymSubscriptionOption;
use Modules\Software\Models\GymSubscriptionOptionGroup;
use Modules\Software\Repositories\GymSubscriptionOptionGroupRepository;
use Modules\Software\Classes\TypeConstants;

class GymSubscriptionOptionGroupFrontController extends GymGenericFrontController
{
    public $repo;

    public function __construct()
    {
        parent::__construct();
        $this->repo = new GymSubscriptionOptionGroupRepository(new Application);
    }

    /** List option groups with their options and linked category for a subscription */
    public function index(int $subscriptionId)
    {
        $groups = GymSubscriptionOptionGroup::branch()
            ->with([
                'options' => fn($q) => $q
                    ->orderBy('list_order')
                    ->with(['product' => fn($q2) => $q2->select(
                        'id', 'name_ar', 'name_en', 'display_name_ar', 'display_name_en',
                        'image', 'category_id'
                    )]),
                'category' => fn($q) => $q->select('id', 'name_ar', 'name_en'),
            ])
            ->where('subscription_id', $subscriptionId)
            ->orderBy('list_order')
            ->get();

        return response()->json(['data' => $groups]);
    }

    /** Create an option group */
    public function store(Request $request, int $subscriptionId)
    {
        $request->validate([
            'name_ar'        => 'required|string|max:191',
            'name_en'        => 'nullable|string|max:191',
            'selection_type' => 'nullable|in:' . GymSubscriptionOptionGroup::SELECTION_SINGLE . ',' . GymSubscriptionOptionGroup::SELECTION_MULTIPLE,
            'is_required'    => 'nullable|boolean',
            'list_order'     => 'nullable|integer',
            'is_system'      => 'nullable|boolean',
            'is_web'         => 'nullable|boolean',
            'is_mobile'      => 'nullable|boolean',
            'category_id'    => 'nullable|integer|exists:sw_gym_store_categories,id',
        ]);

        $subscription = GymSubscription::branch()->findOrFail($subscriptionId);

        $group = GymSubscriptionOptionGroup::create([
            'branch_setting_id' => $this->branchId(),
            'subscription_id'   => $subscription->id,
            'name_ar'           => $request->name_ar,
            'name_en'           => $request->name_en ?: $request->name_ar,
            'selection_type'    => $request->selection_type ?? GymSubscriptionOptionGroup::SELECTION_SINGLE,
            'is_required'       => $request->boolean('is_required', false),
            'list_order'        => $request->list_order ?? 0,
            'is_system'         => $request->boolean('is_system', true),
            'is_web'            => $request->boolean('is_web', true),
            'is_mobile'         => $request->boolean('is_mobile', true),
            'category_id'       => $request->input('category_id') ?: null,
        ]);

        $this->userLog(
            'Create option group for subscription #' . $subscriptionId,
            TypeConstants::CreateSubscriptionOptionGroup
        );

        return response()->json(['success' => true, 'data' => $group->load('category')]);
    }

    /** Update an option group */
    public function update(Request $request, int $subscriptionId, int $id)
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:sw_gym_store_categories,id',
        ]);

        $group = GymSubscriptionOptionGroup::branch()
            ->where('subscription_id', $subscriptionId)
            ->findOrFail($id);

        $data = $request->only([
            'name_ar', 'name_en', 'selection_type',
            'is_required', 'list_order', 'is_system', 'is_web', 'is_mobile',
        ]);
        $data['category_id'] = $request->input('category_id') ?: null;

        $group->update($data);

        $this->userLog(
            'Edit option group #' . $id,
            TypeConstants::EditSubscriptionOptionGroup
        );

        return response()->json(['success' => true, 'data' => $group->load('category')]);
    }

    /** Delete an option group — blocked if any of its options have member history */
    public function destroy(int $subscriptionId, int $id)
    {
        $group = GymSubscriptionOptionGroup::branch()
            ->where('subscription_id', $subscriptionId)
            ->findOrFail($id);

        $optionIds = GymSubscriptionOption::withTrashed()
            ->where('option_group_id', $id)
            ->pluck('id');

        if ($optionIds->isNotEmpty()) {
            $hasHistory = GymMemberSubscriptionOption::withTrashed()
                ->whereIn('option_id', $optionIds)
                ->exists();

            if ($hasHistory) {
                return response()->json([
                    'success' => false,
                    'message' => trans('sw.option_group_has_history_cannot_delete'),
                ], 422);
            }
        }

        $group->delete();

        $this->userLog(
            'Delete option group #' . $id,
            TypeConstants::DeleteSubscriptionOptionGroup
        );

        return response()->json(['success' => true]);
    }

    private function branchId(): int
    {
        return (int) (\Illuminate\Support\Facades\Auth::guard('sw')->user()?->branch_setting_id ?? 1);
    }
}
