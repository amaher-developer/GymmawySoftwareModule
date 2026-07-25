<?php

namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Modules\Software\Models\GymSubscriptionCategory;

class GymSubscriptionCategoryRepository extends GenericRepository
{
    public function model()
    {
        return GymSubscriptionCategory::class;
    }
}

