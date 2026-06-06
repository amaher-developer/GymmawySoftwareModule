<?php

namespace Modules\Software\Repositories;

use Illuminate\Container\Container as Application;
use Modules\Generic\Repositories\GenericRepository;
use Modules\Software\Models\GymStoreCategory;

class GymStoreCategoryRepository extends GenericRepository
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->model = new GymStoreCategory();
    }
}

