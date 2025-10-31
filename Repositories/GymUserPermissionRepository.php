<?php

namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Modules\Software\Models\GymUserPermission;

class GymUserPermissionRepository extends GenericRepository
{
    public function __construct()
    {
        $this->model = new GymUserPermission();
    }
}

