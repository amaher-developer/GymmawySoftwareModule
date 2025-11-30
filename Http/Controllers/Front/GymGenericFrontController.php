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
        
        // User initialization is now handled by InitializeUser middleware
        // No need to duplicate the logic here - middleware will set user_sw and user_sw_permissions
        
        $this->barcode_type = TypeConstants::BarcodeType;
    }

    /**
     * Get the authenticated user (call this in controller methods if needed)
     * User is already initialized by InitializeUser middleware, but this can be used
     * to refresh user data if needed
     */
    protected function getAuthenticatedUser()
    {
        // If user is already set by middleware, return it
        if ($this->user_sw) {
            return $this->user_sw;
        }
        
        // Otherwise, fetch from Auth (fallback)
        $user = Auth::guard('sw')->user();
        $this->user_sw = $user;
        $this->user_sw_permissions = $user ? $user->permissions : null;
        
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
     * This is called by the middleware after authentication (if needed)
     * Note: User is already initialized by InitializeUser middleware, so this is optional
     */
    public function boot()
    {
        // User is already set by InitializeUser middleware
        // This method can be used for additional initialization if needed
        // No need to fetch user again - it's already set
    }

    /**
     * Magic method to automatically initialize user when accessing properties
     * This makes $this->user_sw work automatically in all child classes
     * User should already be set by InitializeUser middleware, but this provides fallback
     */
    public function __get($property)
    {
        // Handle user_sw property
        if ($property === 'user_sw') {
            // If not set by middleware, fetch it (fallback)
            if (!$this->user_sw) {
                $this->getAuthenticatedUser();
            }
            return $this->user_sw;
        }
        
        // Handle user_sw_permissions property
        if ($property === 'user_sw_permissions') {
            // If not set by middleware, fetch it (fallback)
            if (!$this->user_sw_permissions) {
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
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    /**
     * Get the authenticated user with automatic initialization
     * Use this method instead of accessing $this->user_sw directly
     * User should already be set by InitializeUser middleware
     */
    public function getUserSw()
    {
        if (!$this->user_sw) {
            $this->getAuthenticatedUser();
        }
        return $this->user_sw;
    }

    /**
     * Get the authenticated user permissions with automatic initialization
     * Use this method instead of accessing $this->user_sw_permissions directly
     * Permissions should already be set by InitializeUser middleware
     */
    public function getUserSwPermissions()
    {
        if (!$this->user_sw_permissions) {
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

    /**
     * Normalize balance responses coming back from the different SMS gateways.
     */
    protected function formatSmsPoints($balance): string
    {
        return sprintf('%d %s', $this->resolveSmsPoints($balance), trans('sw.message_num'));
    }

    protected function resolveSmsPoints($balance): int
    {
        if (is_numeric($balance)) {
            return max(0, (int)$balance);
        }

        if (is_object($balance) || is_array($balance)) {
            $candidates = [
                'data.points',
                'points',
                'data.balance',
                'balance',
                'Balance',
                'total_balance',
                'data.total_balance',
                'available',
                'available_points',
                'ReturnData.Balance',
                'result.balance',
            ];

            foreach ($candidates as $path) {
                $value = data_get($balance, $path);
                if (is_numeric($value)) {
                    return max(0, (int)$value);
                }
            }
        }

        if (is_string($balance) && is_numeric(trim($balance))) {
            return max(0, (int)trim($balance));
        }

        return 0;
    }


}

