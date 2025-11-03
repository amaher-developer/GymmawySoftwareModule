<?php

namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Modules\Software\Models\LoyaltyPointRule;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * LoyaltyPointRuleRepository
 * 
 * Repository for handling LoyaltyPointRule data operations
 */
class LoyaltyPointRuleRepository extends GenericRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return LoyaltyPointRule::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Get active rule for a branch
     *
     * @param int $branchId
     * @return LoyaltyPointRule|null
     */
    public function getActiveRule($branchId = 1)
    {
        return $this->model
            ->where('branch_setting_id', $branchId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active rules
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllActive()
    {
        return $this->model
            ->where('is_active', true)
            ->get();
    }
}

