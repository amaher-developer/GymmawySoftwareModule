<?php

namespace Modules\Software\Http\Middleware;

use Closure;
use http\Env\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAuth
{

    public function handle($request, Closure $next)
    {

        if(\Request::is(request()->segment(1).'/sw*') && !\Request::is(request()->segment(1).'/sw/login') && !Auth::guard('sw')->user()){
            return redirect()->route('sw.login');
        }
//        dd(Auth::guard('sw')->user());
        return $next($request);
    }

}
