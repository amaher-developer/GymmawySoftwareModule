<?php

namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Modules\Software\Models\GymSubscriptionCategory;

class GymSubscriptionCategoryRepository extends GenericRepository
{
    public function __construct()
    {
        $this->model = new GymSubscriptionCategory();
    }
}

