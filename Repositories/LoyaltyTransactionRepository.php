<?php

namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Modules\Software\Models\LoyaltyTransaction;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * LoyaltyTransactionRepository
 * 
 * Repository for handling LoyaltyTransaction data operations
 */
class LoyaltyTransactionRepository extends GenericRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return LoyaltyTransaction::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Get member's transaction history
     *
     * @param int $memberId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMemberHistory($memberId, $limit = 50)
    {
        return $this->model
            ->where('member_id', $memberId)
            ->with(['rule', 'campaign', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get member's active points
     *
     * @param int $memberId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMemberActivePoints($memberId)
    {
        return $this->model
            ->where('member_id', $memberId)
            ->where('is_expired', false)
            ->where('points', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get transactions by type
     *
     * @param string $type
     * @param int|null $memberId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByType($type, $memberId = null)
    {
        $query = $this->model->where('type', $type);

        if ($memberId) {
            $query->where('member_id', $memberId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}

