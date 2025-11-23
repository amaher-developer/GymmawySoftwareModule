<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Http\Controllers\Front\GymGenericFrontController;

/**
 * Example controller showing how to use Magic Method in child classes
 */
class ExampleController extends GymGenericFrontController
{
    /**
     * Method 1: Using Magic Method (Recommended)
     * Just access $this->user_sw and it will auto-initialize!
     */
    public function method1()
    {
        // Magic happens here - just access the property!
        $user = $this->user_sw; // Auto-initializes!
        $permissions = $this->user_sw_permissions; // Auto-initializes!
        
        return response()->json([
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : null,
            'permissions' => $permissions,
            'method' => 'Magic Method'
        ]);
    }
    
    /**
     * Method 2: Using getUserSw() method
     * Explicit method call
     */
    public function method2()
    {
        $user = $this->getUserSw();
        $permissions = $this->getUserSwPermissions();
        
        return response()->json([
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : null,
            'permissions' => $permissions,
            'method' => 'Explicit Method'
        ]);
    }
    
    /**
     * Method 3: Using initializeUser() method
     * Initialize first, then access property
     */
    public function method3()
    {
        $this->initializeUser();
        
        // Now $this->user_sw is available
        $user = $this->user_sw;
        
        return response()->json([
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : null,
            'method' => 'Initialize First'
        ]);
    }
    
    /**
     * Method 4: Using getAuthenticatedUser() method
     * Always gets fresh data
     */
    public function method4()
    {
        $user = $this->getAuthenticatedUser();
        
        return response()->json([
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : null,
            'method' => 'Fresh Data'
        ]);
    }
    
    /**
     * Method 5: Using userLog (already handles user initialization)
     */
    public function method5()
    {
        $this->userLog('User accessed method5', 'info');
        
        return response()->json([
            'message' => 'Log created',
            'method' => 'User Log'
        ]);
    }
    
    /**
     * Method 6: Multiple property access (Magic Method)
     * Shows how magic method works with multiple properties
     */
    public function method6()
    {
        // All these will auto-initialize via magic method
        $user = $this->user_sw;
        $permissions = $this->user_sw_permissions;
        
        // Check if user is super user
        $isSuperUser = $user && $user->is_super_user;
        
        return response()->json([
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : null,
            'is_super_user' => $isSuperUser,
            'permissions' => $permissions,
            'method' => 'Magic Method Multiple Properties'
        ]);
    }
}

