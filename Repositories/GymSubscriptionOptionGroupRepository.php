<?php

namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Modules\Software\Models\GymSubscriptionOptionGroup;

class GymSubscriptionOptionGroupRepository extends GenericRepository
{
    public function model()
    {
        return GymSubscriptionOptionGroup::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
