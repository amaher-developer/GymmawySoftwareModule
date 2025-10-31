<?php

namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Modules\Software\Models\GymStoreCategory;

class GymStoreCategoryRepository extends GenericRepository
{
    public function __construct()
    {
        $this->model = new GymStoreCategory();
    }
}

