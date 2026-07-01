<?php

namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Modules\Software\Models\GymSubscriptionProduct;

class GymSubscriptionProductRepository extends GenericRepository
{
    public function model()
    {
        return GymSubscriptionProduct::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
