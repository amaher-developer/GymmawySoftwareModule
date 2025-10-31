<?php
namespace Modules\Software\Repositories;

use Modules\Generic\Repositories\GenericRepository;
use Modules\Software\Models\GymBanner;
use Prettus\Repository\Criteria\RequestCriteria;


class GymBannerRepository extends GenericRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
//    public $model = GymBanner::class;

    public function model()
    {
        return GymBanner::class;
    }

//    public function branch(){
//        return $this->model->branch();
//    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
