<?php

namespace Modules\Software\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Modules\Generic\Models\Setting;
use App\Helpers\CurrentSwUser;
class SwPermission
{
    /**
     * Ensure mainSettings is loaded (cached for performance)
     */
    private function ensureMainSettings($user = null)
    {
        //$startedAt = microtime(true);
        // Use Laravel's default cache driver instead of 'file' store for better performance
        $branchId = $user ? ($user->branch_setting_id ?? 1) : 1;
        $cacheKey = 'mainSettings_' . $branchId;
        $mainSettings = null;
        $source = 'database';
        
        // Try to get from cache, but handle unserialize errors gracefully
        try {
            $mainSettings = Cache::get($cacheKey);
            if ($mainSettings) {
                $source = 'cache';
            }
        } catch (\Exception $e) {
            // Cache entry is corrupted (likely due to charset change), clear it
            Cache::forget($cacheKey);
            if (config('app.debug')) {
                Log::warning('Cache unserialize error for ' . $cacheKey . ': ' . $e->getMessage());
            }
        }
        
        if (!$mainSettings) {
            // Direct query using branch ID (faster than using scope)
            $mainSettings = Setting::where('id', $branchId)->first();
            
            if (!$mainSettings) {
                $mainSettings = new \stdClass();
                $mainSettings->name = 'Gym System';
                $mainSettings->description = 'Gym Management System';
                $mainSettings->logo_white = asset('resources/assets/new_front/images/logo.png');
                $mainSettings->sw_end_date = '2099-12-31';
                $mainSettings->active_store = false;
                $mainSettings->active_pt = false;
                $mainSettings->active_training = false;
            } else {
                // Decode JSON columns if they exist
                $jsonColumns = ['features', 'vat_details', 'reservation_details', 'wa_details', 'integrations', 'billing', 'limits', 'social_media', 'images', 'cover_images'];
                foreach ($jsonColumns as $column) {
                    if (isset($mainSettings->$column) && is_string($mainSettings->$column)) {
                        $decoded = json_decode($mainSettings->$column, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $mainSettings->$column = $decoded;
                        }
                    }
                }
            }
            
            // Cache for 10 minutes (600 seconds)
            try {
                Cache::put($cacheKey, $mainSettings, 600);
            } catch (\Exception $e) {
                // If caching fails, log but don't fail the request
                if (config('app.debug')) {
                    Log::warning('Failed to cache mainSettings: ' . $e->getMessage());
                }
            }
        }

        // if (config('app.debug')) {
        //     $durationMs = round((microtime(true) - $startedAt) * 1000, 2);
        //     Log::debug('mainSettings load timing', [
        //         'branch_id' => $branchId,
        //         'source' => $source,
        //         'duration_ms' => $durationMs,
        //     ]);
        // }
        
        return $mainSettings;
    }

    public function handle($request, Closure $next)
    {
        // Get user once and reuse it throughout the middleware
        $user = Auth::guard('sw')->user();
        config(['sw.current_sw_user' => $user]);
        
        // Cache user in helper to avoid repeated Auth calls in models
        CurrentSwUser::set($user);

        // If no user is authenticated, allow to continue (other middleware will handle auth)
        if (!$user) {
            return $next($request);
        }
        
        // Check work time for non-super users
        // This ensures users with time bounds are logged out if outside their work hours
        if (!$user->is_super_user && $user->start_time_work && $user->end_time_work) {
            $start_time_work = Carbon::parse($user->start_time_work)->toDateTimeString();
            $end_time_work = str_replace('00:00:00', '24:00:00', Carbon::parse($user->end_time_work)->toDateTimeString());
            
            // If same time, allow access (24/7 access - no time restriction)
            $isSameTime = Carbon::parse($user->start_time_work)->toDateTimeString() == Carbon::parse($user->end_time_work)->toDateTimeString();
            
            // Check if current time is outside work hours
            if (!$isSameTime) {
                $now = Carbon::now()->toDateTimeString();
                $isOutsideWorkHours = !(($start_time_work <= $now) && ($end_time_work >= $now));
                
                if ($isOutsideWorkHours) {
                    // Force logout and redirect to login page
                    Auth::guard('sw')->logout();
                    Session::flush();
                    return redirect()->route('sw.login')->withErrors(['error' => trans('auth.failed_time')]);
                }
            }
        }
        
        $route = \Request::route()->getName();
        $route = str_replace('sw.', '', $route);
        $default_permissions = ['dashboard', 'dashboardMini', 'dashboardPTMini', 'showStoreOrder', 'showOrderSubscriptionNonMember', 'showOrderSubscriptionPOSNonMember', 'showStoreOrderVendor', 'showOrder', 'memberAttendees', 'membersRefresh', 'showMemberProfile', 'creditMemberBalance'
            , 'pendingPTTrainerCommissions', 'memberPTAttendees', 'getPTMemberAjax', 'getStoreMemberAjax', 'getPTTrainerAjax'
            , 'memberActivityMembershipAttendees'
            , 'memberInvitationAttendees', 'editUserProfile', 'listUserJson'
            , 'listUserLog', 'downloadCard', 'memberSubscriptionRenew', 'downloadQRCode', 'downloadCode', 'downloadStoreProductBarcode', 'downloadMemberBarcode'
            , 'showOrderSubscription', 'showOrderSubscriptionPOS'
            , 'showOrderPTSubscription', 'showOrderPTSubscriptionPOS'
            , 'storeStoreOrderPOS', 'showStoreOrderPOS', 'showOrderPOS', 'showStoreOrderVendorPOS'
            , 'listSwPayment', 'updatePotentialMember'
            , 'listHelperTools',  'calculateCalories', 'calculateCaloriesResult', 'calculateBMI', 'calculateBMIResult', 'calculateIBW', 'calculateIBWResult', 'calculateWater', 'calculateWaterResult', 'calculateVatPercentage', 'calculateVatPercentageResult'
            , 'storePTMember', 'listPTMemberCalendar', 'listPTMemberInClassCalendar'
            , 'listPTSessions', 'showPTSession'
            , 'listTrainingMedicine', 'createTrainingMedicine', 'editTrainingMedicine', 'deleteTrainingMedicine'
            , 'listTrainingMemberLog', 'showTrainingMemberLog', 'addTrainingAssessment', 'addMemberTrainingPlan'
            , 'addMemberTrainingMedicine', 'addMemberTrainingFile', 'addMemberTrainingTrack', 'addMemberTrainingNote'
            , 'generateMemberAiPlan', 'generateAiPlan', 'saveAiPlanTemplate', 'assignAiPlanToMember', 'downloadPlanPDF'
            , 'userAttendees', 'userAttendeesStore'
            , 'fingerprintRefresh'
            , 'exportTodayPTMemberExcel', 'exportTodayPTMemberPDF', 'exportTodayMemberExcel', 'exportTodayMemberPDF'
            , 'exportTodayNonMemberExcel', 'exportTodayNonMemberPDF', 'exportExpireMemberExcel', 'exportExpireMemberPDF'
            , 'exportSubscriptionMemberExcel', 'exportSubscriptionMemberPDF', 'exportPTSubscriptionMemberExcel', 'exportPTSubscriptionMemberPDF'
            , 'reportUserNotificationsList', 'attendanceGeofenceCheck'
            // Reservation Permissions
           // , 'listReservation', 'createReservation', 'editReservation', 'deleteReservation'
            //, 'changeReservationStatus', 'confirmReservation', 'cancelReservation', 'attendReservation', 'markMissedReservation'
            , 'getReservationMemberAjax', 'createReservationMemberAjax', 'deleteReservationMemberAjax'
            //, 'getNonMemberReservation', 'createReservationNonMemberAjax', 'deleteReservationNonMemberAjax'
            , 'reservation.events', 'reservation.slots', 'reservation.checkOverlap', 'reservation.ajaxCreate', 'reservation.ajaxUpdate', 'reservation.ajaxGet'
            , 'reservation.confirm', 'reservation.cancel', 'reservation.attend', 'reservation.missed'
            , 'getMembersBySearch'
        ];
        
        // Merge permissions once
        $userPermissions = is_array($user->permissions) ? $user->permissions : [];
        $permissions = array_merge($default_permissions, $userPermissions);

        // Ensure mainSettings is loaded (cached) - pass user to avoid another query
        $mainSettings = $this->ensureMainSettings($user);
        
        // Share mainSettings with views to avoid duplicate cache queries
        \Illuminate\Support\Facades\View::share('mainSettings', $mainSettings);
        
        // Check if mainSettings has sw_end_date
        if (isset($mainSettings->sw_end_date)) {
            $expiryDate = Carbon::parse($mainSettings->sw_end_date)->addDays(2)->toDateString();
            $today = Carbon::now()->toDateString();
            
            if ($expiryDate <= $today) {
                if (in_array($route, $default_permissions)) {
                    $user->is_super_user = false;
                    return $next($request);
                }
            } else {
                // $permission = $request->route()->defaults['permission'] ?? $route;
                // $permission = str_replace('sw.', '', $permission);

                $route  = $request->route();
                $action = $route->getAction();
                $permission = $action['defaults']['permission'] ?? str_replace('sw.', '', $route->getName());

                if (in_array($permission, $permissions) || $user->is_super_user) {
                    return $next($request);
                }
                // if (in_array($route, $permissions) || $user->is_super_user) {
                //     return $next($request);
                // }
            }
        } else {
            // If sw_end_date is not set, just check permissions
            
            $route  = $request->route();
            $action = $route->getAction();
            $permission = $action['defaults']['permission'] ?? str_replace('sw.', '', $route->getName());

            if (in_array($permission, $permissions) || $user->is_super_user) {
                return $next($request);
            }
        }
        
        return redirect()->route('sw.dashboard');
    }

}

