<?php

namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Modules\Software\Models\GymUserTransaction;

class GymUserTransactionRepository extends GenericRepository
{
    public function __construct()
    {
        $this->model = new GymUserTransaction();
    }
}

