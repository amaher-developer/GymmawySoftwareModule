<?php

namespace Modules\Software\Http\Controllers\Front;


use Modules\Generic\Models\Setting;
use Modules\Software\Repositories\GymMemberAttendeeRepository;
use Modules\Software\Repositories\GymUserLogRepository;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;


class GymWebsiteFrontController extends GymGenericFrontController
{


    public function __construct()
    {
        parent::__construct();

    }


    public function templates()
    {
        $title = env('APP_NAME_AR');

        return view('software::Website.templates', ['title' => $title]);
    }



    public function template()
    {
        $client = env('APP_URL');
        $title = env('APP_NAME_AR');
        $url = request('url');
        return view('software::Website.template', compact('client','title', 'url'));
    }

}
