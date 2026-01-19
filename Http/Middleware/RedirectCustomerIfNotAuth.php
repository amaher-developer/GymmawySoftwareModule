<?php

namespace Modules\Software\Http\Middleware;

use Closure;
use http\Env\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Software\Models\GymMember;

class RedirectCustomerIfNotAuth
{

    public function handle($request, Closure $next)
    {
        $customer_id = session()->get('sw_customer_id');//Cache::store('file')->get('swCustomer');
        $customer = GymMember::where('id', $customer_id)->first();
        if(\Request::is(request()->segment(1).'/customer*') && (!\Request::is(request()->segment(1).'/customer/login')) && ($customer == null) ){
            return redirect()->route('sw.customerLogin');
        }

        return $next($request);
    }

}

