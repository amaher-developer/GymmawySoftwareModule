<?php

namespace Modules\Software\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class InitializeUser
{
    /**
     * Handle an incoming request.
     * This runs AFTER authentication middleware
     */
    public function handle(Request $request, Closure $next)
    {
        // Get user once to avoid multiple queries
        $user = Auth::guard('sw')->user();
        $userPermissions = $user ? $user->permissions : null;
        // Convert permissions array to hash map for O(1) lookup instead of O(n) in_array
        $permissionsMap = [];
        $isSuperUser = false;
        if ($user) {
            $isSuperUser = (bool) ($user->is_super_user ?? false);
            if ($userPermissions && is_array($userPermissions)) {
                $permissionsMap = array_flip($userPermissions); // Convert to hash map for faster lookup
            }
        }
        
        // Optimize: Defer notification loading - only load if actually needed (not on every request)
        // This reduces database queries on every page load
        $unreadNotificationsCount = 0;
        $unreadNotifications = collect([]);
        if ($user) {
            // Only load notifications for pages that actually display them
            // For member list page, we don't need notifications immediately
            $routeName = $request->route()->getName();
            $needsNotifications = in_array($routeName, ['sw.dashboard', 'sw.dashboardMini', 'sw.dashboardPTMini']);
            
            if ($needsNotifications) {
                $cacheKey = 'user_unread_notifications_' . $user->id;
                $unreadNotificationsCount = Cache::remember($cacheKey, 30, function () use ($user) {
                    return $user->unreadNotifications()->count();
                });
                
                // Load notifications only if count > 0
                if ($unreadNotificationsCount > 0) {
                    $unreadNotifications = Cache::remember($cacheKey . '_list', 30, function () use ($user) {
                        $notifications = $user->unreadNotifications()->limit(10)->get();
                        // Pre-compute diffForHumans for each notification (move Carbon parsing from Blade to Middleware)
                        return $notifications->map(function($notification) {
                            if (isset($notification->data['data']['created_at'])) {
                                $notification->formatted_time = \Carbon\Carbon::createFromTimeStamp(
                                    strtotime($notification->data['data']['created_at'])
                                )->diffForHumans();
                            } else {
                                $notification->formatted_time = '';
                            }
                            return $notification;
                        });
                    });
                }
            }
        }
        
        // Share with views once
        View::share('swUser', $user);
        View::share('swUserPermission', $userPermissions);
        View::share('permissionsMap', $permissionsMap); // Hash map for O(1) permission checks
        View::share('isSuperUser', $isSuperUser); // Pre-calculated boolean
        View::share('unreadNotificationsCount', $unreadNotificationsCount);
        View::share('unreadNotifications', $unreadNotifications);
        
        // Get the controller instance
        $controller = $request->route()->getController();
        
        // Check if controller extends GymGenericFrontController
        if ($controller instanceof \Modules\Software\Http\Controllers\Front\GymGenericFrontController) {
            // Set user data directly instead of calling boot() which makes another query
            if (property_exists($controller, 'user_sw')) {
                $controller->user_sw = $user;
            }
            if (property_exists($controller, 'user_sw_permissions')) {
                $controller->user_sw_permissions = $userPermissions;
            }
            
            // Only call boot if it's needed (boot might do other initialization)
            // Commented out to avoid duplicate Auth::guard('sw')->user() calls
            // $controller->boot();
        }
        
        return $next($request);
    }
}
