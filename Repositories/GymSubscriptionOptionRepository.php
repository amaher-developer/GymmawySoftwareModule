<?php

namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Modules\Software\Models\GymSubscriptionOption;

class GymSubscriptionOptionRepository extends GenericRepository
{
    public function model()
    {
        return GymSubscriptionOption::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
