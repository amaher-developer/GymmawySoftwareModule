<?php

namespace Modules\Software\Http\Controllers\Front;

use Illuminate\Container\Container as Application;
use Illuminate\Http\Request;
use Modules\Software\Models\GymMemberSubscriptionOption;
use Modules\Software\Models\GymStoreProduct;
use Modules\Software\Models\GymSubscriptionOption;
use Modules\Software\Models\GymSubscriptionOptionGroup;
use Modules\Software\Repositories\GymSubscriptionOptionRepository;
use Modules\Software\Classes\TypeConstants;

class GymSubscriptionOptionFrontController extends GymGenericFrontController
{
    public $repo;

    public function __construct()
    {
        parent::__construct();
        $this->repo = new GymSubscriptionOptionRepository(new Application);
    }

    /** Resolve a group while verifying it belongs to the given subscription (prevents cross-branch access) */
    private function resolveGroup(int $subscriptionId, int $groupId): GymSubscriptionOptionGroup
    {
        return GymSubscriptionOptionGroup::branch()
            ->where('subscription_id', $subscriptionId)
            ->findOrFail($groupId);
    }

    /** Resolve an option while verifying it belongs to the given group AND subscription */
    private function resolveOption(int $subscriptionId, int $groupId, int $optionId): GymSubscriptionOption
    {
        $this->resolveGroup($subscriptionId, $groupId); // confirms group ownership first
        return GymSubscriptionOption::branch()
            ->where('option_group_id', $groupId)
            ->findOrFail($optionId);
    }

    /** Create an option inside an option group */
    public function store(Request $request, int $subscriptionId, int $groupId)
    {
        $request->validate([
            'product_id'     => 'required|integer|exists:sw_gym_store_products,id',
            'price_modifier' => 'nullable|numeric',
            'list_order'     => 'nullable|integer',
        ]);

        $group   = $this->resolveGroup($subscriptionId, $groupId);
        $product = GymStoreProduct::findOrFail($request->product_id);

        // When the group is category-linked, only allow products from that category
        if ($group->category_id && (int) $product->category_id !== (int) $group->category_id) {
            return response()->json([
                'success' => false,
                'message' => trans('sw.product_not_in_group_category'),
            ], 422);
        }

        $option = GymSubscriptionOption::create([
            'branch_setting_id' => $this->branchId(),
            'option_group_id'   => $group->id,
            'product_id'        => $product->id,
            'price_modifier'    => $request->price_modifier ?? 0,
            'list_order'        => $request->list_order ?? 0,
        ]);

        $this->userLog(
            'Create option for group #' . $groupId,
            TypeConstants::CreateSubscriptionOption
        );

        return response()->json(['success' => true, 'data' => $option->load('product')]);
    }

    /** Update an option */
    public function update(Request $request, int $subscriptionId, int $groupId, int $id)
    {
        $request->validate([
            'product_id'     => 'nullable|integer|exists:sw_gym_store_products,id',
            'price_modifier' => 'nullable|numeric',
            'list_order'     => 'nullable|integer',
        ]);

        $option = $this->resolveOption($subscriptionId, $groupId, $id);

        if ($request->filled('product_id')) {
            $group   = $this->resolveGroup($subscriptionId, $groupId);
            $product = GymStoreProduct::findOrFail($request->product_id);

            if ($group->category_id && (int) $product->category_id !== (int) $group->category_id) {
                return response()->json([
                    'success' => false,
                    'message' => trans('sw.product_not_in_group_category'),
                ], 422);
            }

            $option->product_id = $product->id;
        }

        $option->fill($request->only(['price_modifier', 'list_order']));
        $option->save();

        $this->userLog(
            'Edit option #' . $id,
            TypeConstants::EditSubscriptionOption
        );

        return response()->json(['success' => true, 'data' => $option->load('product')]);
    }

    /** Delete an option — blocked if any member has selected it (application-level RESTRICT) */
    public function destroy(int $subscriptionId, int $groupId, int $id)
    {
        $option = $this->resolveOption($subscriptionId, $groupId, $id);

        $hasHistory = GymMemberSubscriptionOption::withTrashed()
            ->where('option_id', $id)
            ->exists();

        if ($hasHistory) {
            return response()->json([
                'success' => false,
                'message' => trans('sw.option_has_history_cannot_delete'),
            ], 422);
        }

        $option->delete();

        $this->userLog(
            'Delete option #' . $id,
            TypeConstants::DeleteSubscriptionOption
        );

        return response()->json(['success' => true]);
    }

    private function branchId(): int
    {
        return (int) (\Illuminate\Support\Facades\Auth::guard('sw')->user()?->branch_setting_id ?? 1);
    }
}
