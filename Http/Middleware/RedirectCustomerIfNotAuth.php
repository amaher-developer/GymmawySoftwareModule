<?php

namespace Modules\Software\Http\Middleware;

use Closure;
use http\Env\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RedirectCustomerIfNotAuth
{

    public function handle($request, Closure $next)
    {
        $customer = request()->session()->get('swCustomer');//Cache::store('file')->get('swCustomer');

        if(\Request::is(request()->segment(1).'/customer*') && (!\Request::is(request()->segment(1).'/customer/login')) && ($customer == null) ){
            return redirect()->route('sw.customerLogin');
        }

        return $next($request);
    }

}

