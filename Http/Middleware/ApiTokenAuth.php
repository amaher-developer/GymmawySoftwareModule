<?php

namespace Modules\Software\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Modules\Software\Models\GymMember;

class ApiTokenAuth
{
    /**
     * Handle an incoming request for API token authentication
     * Token is stored hashed (sha256) in database
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            // No token provided - continue (some endpoints don't require auth)
            return $next($request);
        }
        
        // Hash the token to match database storage
        $hashedToken = hash('sha256', $token);
        
        // Find member by hashed token
        $member = GymMember::where('api_token', $hashedToken)->first();
        
        if ($member) {
            // Set the authenticated user for 'api' guard
            Auth::guard('api')->setUser($member);
        }
        
        return $next($request);
    }
}


