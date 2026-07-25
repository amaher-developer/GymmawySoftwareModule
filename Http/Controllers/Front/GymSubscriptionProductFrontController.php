<?php

namespace Modules\Software\Http\Controllers\Front;

use Illuminate\Container\Container as Application;
use Illuminate\Http\Request;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymSubscriptionProduct;
use Modules\Software\Models\GymStoreProduct;
use Modules\Software\Repositories\GymSubscriptionProductRepository;
use Modules\Software\Classes\TypeConstants;

class GymSubscriptionProductFrontController extends GymGenericFrontController
{
    public $repo;

    public function __construct()
    {
        parent::__construct();
        $this->repo = new GymSubscriptionProductRepository(new Application);
    }

    /** List products attached to a subscription — returns JSON */
    public function index(int $subscriptionId)
    {
        $items = GymSubscriptionProduct::branch()
            ->with('product')
            ->where('subscription_id', $subscriptionId)
            ->orderBy('list_order')
            ->get();

        return response()->json(['data' => $items]);
    }

    /** Add a product to a subscription */
    public function store(Request $request, int $subscriptionId)
    {
        $request->validate([
            'product_id'    => 'required|integer|exists:sw_gym_store_products,id',
            'list_order'    => 'nullable|integer',
            'is_replaceable'=> 'nullable|boolean',
        ]);

        $subscription = GymSubscription::branch()->findOrFail($subscriptionId);

        $item = GymSubscriptionProduct::create([
            'branch_setting_id' => $this->branchId(),
            'subscription_id'   => $subscription->id,
            'product_id'        => $request->product_id,
            'list_order'        => $request->list_order ?? 0,
            'is_replaceable'    => $request->boolean('is_replaceable', false),
        ]);

        $this->userLog(
            'Add product to subscription #' . $subscriptionId,
            TypeConstants::CreateSubscriptionProduct
        );

        return response()->json(['success' => true, 'data' => $item->load('product')]);
    }

    /** Update order / replaceable flag */
    public function update(Request $request, int $subscriptionId, int $id)
    {
        $item = GymSubscriptionProduct::branch()
            ->where('subscription_id', $subscriptionId)
            ->findOrFail($id);

        $item->update($request->only(['list_order', 'is_replaceable']));

        $this->userLog(
            'Edit subscription product #' . $id,
            TypeConstants::EditSubscriptionProduct
        );

        return response()->json(['success' => true, 'data' => $item]);
    }

    /** Remove a product from a subscription */
    public function destroy(int $subscriptionId, int $id)
    {
        $item = GymSubscriptionProduct::branch()
            ->where('subscription_id', $subscriptionId)
            ->findOrFail($id);

        $item->delete();

        $this->userLog(
            'Delete subscription product #' . $id,
            TypeConstants::DeleteSubscriptionProduct
        );

        return response()->json(['success' => true]);
    }

    /** Reorder products via drag-drop: expects [{id, list_order}] */
    public function reorder(Request $request, int $subscriptionId)
    {
        $request->validate(['items' => 'required|array', 'items.*.id' => 'required|integer', 'items.*.list_order' => 'required|integer']);

        foreach ($request->items as $row) {
            GymSubscriptionProduct::branch()
                ->where('subscription_id', $subscriptionId)
                ->where('id', $row['id'])
                ->update(['list_order' => $row['list_order']]);
        }

        return response()->json(['success' => true]);
    }

    private function branchId(): int
    {
        return (int) (\Illuminate\Support\Facades\Auth::guard('sw')->user()?->branch_setting_id ?? 1);
    }
}
