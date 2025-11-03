<?php

namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Modules\Software\Models\LoyaltyCampaign;
use Prettus\Repository\Criteria\RequestCriteria;
use Carbon\Carbon;

/**
 * LoyaltyCampaignRepository
 * 
 * Repository for handling LoyaltyCampaign data operations
 */
class LoyaltyCampaignRepository extends GenericRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return LoyaltyCampaign::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Get currently active campaign for a branch
     *
     * @param int $branchId
     * @return LoyaltyCampaign|null
     */
    public function getCurrentCampaign($branchId = 1)
    {
        $now = Carbon::now();
        
        return $this->model
            ->where('branch_setting_id', $branchId)
            ->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('multiplier', 'desc')
            ->first();
    }

    /**
     * Get all active campaigns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllActive()
    {
        return $this->model
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get upcoming campaigns
     *
     * @param int|null $branchId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUpcoming($branchId = null)
    {
        $query = $this->model
            ->where('is_active', true)
            ->where('start_date', '>', Carbon::now());

        if ($branchId) {
            $query->where('branch_setting_id', $branchId);
        }

        return $query->orderBy('start_date', 'asc')->get();
    }

    /**
     * Get expired campaigns
     *
     * @param int|null $branchId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExpired($branchId = null)
    {
        $query = $this->model
            ->where('end_date', '<', Carbon::now());

        if ($branchId) {
            $query->where('branch_setting_id', $branchId);
        }

        return $query->orderBy('end_date', 'desc')->get();
    }
}

