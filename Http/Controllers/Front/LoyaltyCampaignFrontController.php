<?php

namespace Modules\Software\Http\Controllers\Front;

use Illuminate\Container\Container as Application;
use Modules\Software\Repositories\LoyaltyCampaignRepository;
use Modules\Software\Models\LoyaltyCampaign;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * LoyaltyCampaignFrontController
 * 
 * Front controller for managing loyalty campaigns
 */
class LoyaltyCampaignFrontController extends GymGenericFrontController
{
    public $loyaltyCampaignRepository;

    public function __construct()
    {
        parent::__construct();
        $this->loyaltyCampaignRepository = new LoyaltyCampaignRepository(new Application);
    }

    /**
     * Display a listing of loyalty campaigns
     */
    public function index()
    {
        $title = trans('sw.loyalty_campaigns_list');
        
        if (request('trashed')) {
            $campaigns = $this->loyaltyCampaignRepository->onlyTrashed()->orderBy('id', 'DESC');
        } else {
            $campaigns = $this->loyaltyCampaignRepository->orderBy('start_date', 'DESC');
        }

        // Apply filters
        $campaigns->when(request('search'), function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });

        $campaigns->when(request('is_active'), function ($query, $isActive) {
            $query->where('is_active', $isActive == 'true' ? true : false);
        });

        $campaigns->when(request('status'), function ($query, $status) {
            $now = Carbon::now();
            if ($status == 'running') {
                $query->where('start_date', '<=', $now)
                      ->where('end_date', '>=', $now)
                      ->where('is_active', true);
            } elseif ($status == 'upcoming') {
                $query->where('start_date', '>', $now);
            } elseif ($status == 'expired') {
                $query->where('end_date', '<', $now);
            }
        });

        $search_query = request()->query();

        if ($this->limit) {
            $campaigns = $campaigns->paginate($this->limit);
            $total = $campaigns->total();
        } else {
            $campaigns = $campaigns->get();
            $total = $campaigns->count();
        }

        return view('software::Front.loyalty_campaign_list', compact('campaigns', 'title', 'total', 'search_query'));
    }

    /**
     * Show the form for creating a new campaign
     */
    public function create()
    {
        $title = trans('sw.create_loyalty_campaign');
        return view('software::Front.loyalty_campaign_form', compact('title'));
    }

    /**
     * Store a newly created campaign
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'multiplier' => 'required|numeric|min:0.01|max:99.99',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'applies_to' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['branch_setting_id'] = $this->user_sw->branch_setting_id ?? 1;

        $campaign = LoyaltyCampaign::create($validated);

        return redirect()
            ->route('sw.loyalty_campaigns.index')
            ->with('success', trans('sw.loyalty_campaign_created_successfully'));
    }

    /**
     * Show the form for editing the specified campaign
     */
    public function edit($id)
    {
        $title = trans('sw.edit_loyalty_campaign');
        $campaign = LoyaltyCampaign::findOrFail($id);
        
        return view('software::Front.loyalty_campaign_form', compact('title', 'campaign'));
    }

    /**
     * Update the specified campaign
     */
    public function update(Request $request, $id)
    {
        $campaign = LoyaltyCampaign::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'multiplier' => 'required|numeric|min:0.01|max:99.99',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'applies_to' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $campaign->update($validated);

        return redirect()
            ->route('sw.loyalty_campaigns.index')
            ->with('success', trans('sw.loyalty_campaign_updated_successfully'));
    }

    /**
     * Remove the specified campaign
     */
    public function destroy($id)
    {
        $campaign = LoyaltyCampaign::findOrFail($id);
        $campaign->delete();

        return redirect()
            ->route('sw.loyalty_campaigns.index')
            ->with('success', trans('sw.loyalty_campaign_deleted_successfully'));
    }

    /**
     * Toggle campaign active status
     */
    public function toggleActive($id)
    {
        $campaign = LoyaltyCampaign::findOrFail($id);
        $campaign->is_active = !$campaign->is_active;
        $campaign->save();

        return response()->json([
            'success' => true,
            'is_active' => $campaign->is_active,
            'message' => trans('sw.status_updated_successfully'),
        ]);
    }

    /**
     * Get current running campaign
     */
    public function getCurrentCampaign()
    {
        $branchId = $this->user_sw->branch_setting_id ?? 1;
        $campaign = $this->loyaltyCampaignRepository->getCurrentCampaign($branchId);

        return response()->json([
            'campaign' => $campaign,
        ]);
    }
}

