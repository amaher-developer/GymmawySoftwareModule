<?php

namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Modules\Software\Models\GymUserPermission;

class GymUserPermissionRepository extends GenericRepository
{
    public function model()
    {
        return GymUserPermission::class;
    }
}

