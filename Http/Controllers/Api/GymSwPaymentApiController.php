<?php

namespace Modules\Software\Http\Controllers\Api;

use Modules\Generic\Http\Controllers\Api\GenericApiController;
use Modules\Generic\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class GymSwPaymentApiController extends GenericApiController
{

    public function successfulPayment()
    {
        $setting = Setting::first();
        $token = request('ct');
        $type = request('t');
        $package_duration = @(int)request('pd');
        if(($setting->token == $token) && ($package_duration) && ($type != 'false')){
            if(Carbon::parse($setting->sw_end_date)->toDateString() > Carbon::now()->toDateString()) {
                $setting_inputs['sw_end_date'] = Carbon::parse($setting->sw_end_date)->addDays($package_duration)->toDateString();
            }else{
                $setting_inputs['sw_end_date'] = Carbon::now()->addDays($package_duration)->toDateString();
            }
            $setting->update($setting_inputs);
            Cache::flush();
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_paid'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listSwPayment'));
    }
}

