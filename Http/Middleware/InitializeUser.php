<?php

namespace Modules\Software\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class InitializeUser
{
    /**
     * Handle an incoming request.
     * This runs AFTER authentication middleware
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the controller instance
        $controller = $request->route()->getController();
        
        // Check if controller extends GymGenericFrontController
        if ($controller instanceof \Modules\Software\Http\Controllers\Front\GymGenericFrontController) {
            // Call the boot method which runs after middleware
            $controller->boot();
            $this->user_sw = @Auth::guard('sw')->user();
            $this->user_sw_permissions = @$this->user_sw->permissions;
            View::share('swUser',$this->user_sw);
            View::share('swUserPermission',$this->user_sw_permissions);

           
        }
        
        return $next($request);
    }
}
