<?php

namespace Modules\Software\Http\Controllers\Front;

use Illuminate\Container\Container as Application;
use Modules\Software\Repositories\LoyaltyPointRuleRepository;
use Modules\Software\Models\LoyaltyPointRule;
use Illuminate\Http\Request;

/**
 * LoyaltyPointRuleFrontController
 * 
 * Front controller for managing loyalty point rules
 */
class LoyaltyPointRuleFrontController extends GymGenericFrontController
{
    public $loyaltyPointRuleRepository;

    public function __construct()
    {
        parent::__construct();
        $this->loyaltyPointRuleRepository = new LoyaltyPointRuleRepository(new Application);
    }

    /**
     * Display a listing of loyalty point rules
     */
    public function index()
    {
        $title = trans('sw.loyalty_point_rules_list');
        
        if (request('trashed')) {
            $rules = $this->loyaltyPointRuleRepository->onlyTrashed()->orderBy('id', 'DESC');
        } else {
            $rules = $this->loyaltyPointRuleRepository->orderBy('id', 'DESC');
        }

        // Apply filters
        $rules->when(request('search'), function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });

        $rules->when(request('is_active'), function ($query, $isActive) {
            $query->where('is_active', $isActive == 'true' ? true : false);
        });

        $search_query = request()->query();

        if ($this->limit) {
            $rules = $rules->paginate($this->limit);
            $total = $rules->total();
        } else {
            $rules = $rules->get();
            $total = $rules->count();
        }

        return view('software::Front.loyalty_point_rule_list', compact('rules', 'title', 'total', 'search_query'));
    }

    /**
     * Show the form for creating a new rule
     */
    public function create()
    {
        $title = trans('sw.create_loyalty_point_rule');
        return view('software::Front.loyalty_point_rule_form', compact('title'));
    }

    /**
     * Store a newly created rule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'money_to_point_rate' => 'required|numeric|min:0.01',
            'point_to_money_rate' => 'required|numeric|min:0.01',
            'expires_after_days' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['branch_setting_id'] = $this->user_sw->branch_setting_id ?? 1;

        $rule = LoyaltyPointRule::create($validated);

        return redirect()
            ->route('sw.loyalty_point_rules.index')
            ->with('success', trans('sw.loyalty_point_rule_created_successfully'));
    }

    /**
     * Show the form for editing the specified rule
     */
    public function edit($id)
    {
        $title = trans('sw.edit_loyalty_point_rule');
        $rule = LoyaltyPointRule::findOrFail($id);
        
        return view('software::Front.loyalty_point_rule_form', compact('title', 'rule'));
    }

    /**
     * Update the specified rule
     */
    public function update(Request $request, $id)
    {
        $rule = LoyaltyPointRule::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'money_to_point_rate' => 'required|numeric|min:0.01',
            'point_to_money_rate' => 'required|numeric|min:0.01',
            'expires_after_days' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $rule->update($validated);

        return redirect()
            ->route('sw.loyalty_point_rules.index')
            ->with('success', trans('sw.loyalty_point_rule_updated_successfully'));
    }

    /**
     * Remove the specified rule
     */
    public function destroy($id)
    {
        $rule = LoyaltyPointRule::findOrFail($id);
        $rule->delete();

        return redirect()
            ->route('sw.loyalty_point_rules.index')
            ->with('success', trans('sw.loyalty_point_rule_deleted_successfully'));
    }

    /**
     * Toggle rule active status
     */
    public function toggleActive($id)
    {
        $rule = LoyaltyPointRule::findOrFail($id);
        $rule->is_active = !$rule->is_active;
        $rule->save();

        return response()->json([
            'success' => true,
            'is_active' => $rule->is_active,
            'message' => trans('sw.status_updated_successfully'),
        ]);
    }
}

