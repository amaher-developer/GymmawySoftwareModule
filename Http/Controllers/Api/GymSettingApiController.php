<?php

namespace Modules\Software\Http\Controllers\Api;

use Modules\Generic\Http\Controllers\Api\GenericApiController;
use Modules\Generic\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class GymSettingApiController extends GenericApiController
{

    public function migrate()
    {
        $command = "php artisan migrate";
        $result = shell_exec($command);
        $last_migrate = @DB::table('migrations')->orderBy('id', 'desc')->first()->migration;


        return  Response::json(['status' => true, 'last_migrate' => $last_migrate], 200);
    }

    public function lastMigrate()
    {
        $last_migrate = @Migration::orderBy('id', 'desc')->first()->migration;
        return  Response::json(['last_migrate' => $last_migrate], 200);
    }
}

