<?php
namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymUserLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class GymGenericFrontController extends GenericFrontController
{

    public $barcode_type;
    public $user_sw;
    public $user_sw_permissions;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            $this->user_sw = Auth::guard('sw')->user();
            $this->user_sw_permissions = $this->user_sw ? $this->user_sw->permissions : null;
            View::share('swUser', $this->user_sw);
            View::share('swUserPermission', $this->user_sw_permissions);
            return $next($request);
        });
        
        $this->barcode_type = TypeConstants::BarcodeType;
        
        // Don't initialize user properties - let magic method handle it
        // This allows __get() to be triggered when accessing $this->user_sw
    }

    /**
     * Get the authenticated user (call this in controller methods)
     * This works because it's called AFTER middleware has run
     */
    protected function getAuthenticatedUser()
    {
    
        // Always get fresh user data from Auth (don't cache in constructor)
        $user = Auth::guard('sw')->user();
        
        \Log::info('User fetched', [
            'user' => $user ? $user->id : 'null',
            'user_name' => $user ? $user->name : 'null'
        ]);
        
        // Store it in the property for future use
        $this->user_sw = $user;
        $this->user_sw_permissions = $user ? $user->permissions : null;
        
        // Share with views
        View::share('swUser', $this->user_sw);
        View::share('swUserPermission', $this->user_sw_permissions);
        
        return $user;
    }

    /**
     * Initialize user data - call this in child controller methods
     * This ensures $this->user_sw is available in all child classes
     */
    protected function initializeUser()
    {
        if (!$this->user_sw) {
            $this->getAuthenticatedUser();
        }
        return $this->user_sw;
    }

    /**
     * Boot method that runs after middleware
     * This is called by the middleware after authentication
     */
    public function boot()
    {
        \Log::info('GymGenericFrontController boot method called', [
            'auth_check' => Auth::guard('sw')->check(),
            'auth_user_id' => Auth::guard('sw')->id()
        ]);
        
        // User is now available because middleware has run
        $this->user_sw = Auth::guard('sw')->user();
        $this->user_sw_permissions = $this->user_sw ? $this->user_sw->permissions : null;
        
        // Share with views
        View::share('swUser', $this->user_sw);
        View::share('swUserPermission', $this->user_sw_permissions);
        
        \Log::info('User initialized in boot method', [
            'user_id' => $this->user_sw ? $this->user_sw->id : 'null',
            'permissions' => $this->user_sw_permissions
        ]);
    }

    /**
     * Magic method to automatically initialize user when accessing properties
     * This makes $this->user_sw work automatically in all child classes
     */
    public function __get($property)
    {
        \Log::info('Magic method __get called', [
            'property' => $property,
            'user_sw_is_null' => is_null($this->user_sw),
            'auth_check' => Auth::guard('sw')->check()
        ]);
        
        // Handle user_sw property
        if ($property === 'user_sw') {
            if (!$this->user_sw) {
                \Log::info('Auto-initializing user via magic method');
                $this->getAuthenticatedUser();
            }
            return $this->user_sw;
        }
        
        // Handle user_sw_permissions property
        if ($property === 'user_sw_permissions') {
            if (!$this->user_sw_permissions) {
                \Log::info('Auto-initializing user permissions via magic method');
                $this->getAuthenticatedUser();
            }
            return $this->user_sw_permissions;
        }
        
        // Handle other properties
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        
        // Return null for undefined properties
        return null;
    }

    /**
     * Magic method to handle property assignment
     * This ensures properties are set correctly
     */
    public function __set($property, $value)
    {
        \Log::info('Magic method __set called', [
            'property' => $property,
            'value_type' => gettype($value)
        ]);
        
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    /**
     * Get the authenticated user with automatic initialization
     * Use this method instead of accessing $this->user_sw directly
     */
    public function getUserSw()
    {
        if (!$this->user_sw) {
            \Log::info('Auto-initializing user via getUserSw method');
            $this->getAuthenticatedUser();
        }
        return $this->user_sw;
    }

    /**
     * Get the authenticated user permissions with automatic initialization
     * Use this method instead of accessing $this->user_sw_permissions directly
     */
    public function getUserSwPermissions()
    {
        if (!$this->user_sw_permissions) {
            \Log::info('Auto-initializing user permissions via getUserSwPermissions method');
            $this->getAuthenticatedUser();
        }
        return $this->user_sw_permissions;
    }

    public function userLog($notes, $type = null){
        // Get authenticated user
        $user = $this->getAuthenticatedUser();
        
        GymUserLog::insert([
            'user_id' => $user ? $user->id : null,
            'branch_setting_id' => $user ? $user->branch_setting_id : null,
            'notes' => $notes,
            'type' => $type
        ]);
    }


}
