<?php

namespace Modules\Software\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Generic\Models\Setting;

class SwPermission
{
    /**
     * Ensure mainSettings is loaded
     */
    private function ensureMainSettings()
    {
        $mainSettings = Cache::store('file')->get('mainSettings');
        
        if (!$mainSettings) {
            $mainSettings = Setting::branch()->first();
            
            if (!$mainSettings) {
                $mainSettings = new \stdClass();
                $mainSettings->name = 'Gym System';
                $mainSettings->description = 'Gym Management System';
                $mainSettings->logo_white = asset('resources/assets/front/images/logo.png');
                $mainSettings->sw_end_date = '2099-12-31';
                $mainSettings->active_store = false;
                $mainSettings->active_pt = false;
                $mainSettings->active_training = false;
            }
            
            Cache::store('file')->put('mainSettings', $mainSettings, 600);
        }
        
        return $mainSettings;
    }

    public function handle($request, Closure $next)
    {
        $route = \Request::route()->getName();
        $route = str_replace('sw.', '',$route);
        $default_permissions = ['dashboard', 'dashboardMini', 'dashboardPTMini', 'showStoreOrder', 'showOrderSubscriptionNonMember', 'showOrderSubscriptionPOSNonMember', 'showStoreOrderVendor', 'showOrder', 'memberAttendees', 'membersRefresh', 'showMemberProfile', 'creditMemberBalance'
            , 'memberPTAttendees', 'getPTMemberAjax', 'getStoreMemberAjax', 'getPTTrainerAjax'
            , 'memberActivityMembershipAttendees'
            , 'memberInvitationAttendees', 'editUserProfile', 'listUserJson'
            , 'listReservation', 'getReservationMemberAjax', 'createReservationMemberAjax', 'deleteReservationMemberAjax'
            , 'listUserLog', 'downloadCard', 'memberSubscriptionRenew', 'downloadQRCode', 'downloadCode', 'downloadStoreProductBarcode', 'downloadMemberBarcode'
            , 'showOrderSubscription', 'showOrderSubscriptionPOS'
            , 'showOrderPTSubscription', 'showOrderPTSubscriptionPOS'
            , 'showStoreOrderPOS', 'showOrderPOS', 'showStoreOrderVendorPOS'
            , 'listSwPayment', 'updatePotentialMember'
            , 'listHelperTools',  'calculateCalories', 'calculateCaloriesResult', 'calculateBMI', 'calculateBMIResult', 'calculateIBW', 'calculateIBWResult', 'calculateWater', 'calculateWaterResult', 'calculateVatPercentage', 'calculateVatPercentageResult'
            , 'listPTMemberCalendar', 'listPTMemberInClassCalendar'
            , 'userAttendees', 'userAttendeesStore'
            , 'fingerprintRefresh'
            , 'getNonMemberReservation', 'createReservationNonMemberAjax', 'deleteReservationNonMemberAjax'
            , 'reservation.events', 'reservation.slots', 'reservation.checkOverlap', 'reservation.ajaxCreate', 'reservation.ajaxUpdate', 'reservation.ajaxGet'
            , 'reservation.confirm', 'reservation.cancel', 'reservation.attend', 'reservation.missed'
            , 'exportTodayPTMemberExcel', 'exportTodayPTMemberPDF', 'exportTodayMemberExcel', 'exportTodayMemberPDF'
            , 'exportTodayNonMemberExcel', 'exportTodayNonMemberPDF', 'exportExpireMemberExcel', 'exportExpireMemberPDF'
            , 'exportSubscriptionMemberExcel', 'exportSubscriptionMemberPDF', 'exportPTSubscriptionMemberExcel', 'exportPTSubscriptionMemberPDF'

            , 'reportUserNotificationsList'
        ];
        $permissions = @array_merge($default_permissions , (array)@Auth::guard('sw')->user()->permissions);

        // Ensure mainSettings is loaded
        $mainSettings = $this->ensureMainSettings();
        
        // Check if mainSettings has sw_end_date
        if(isset($mainSettings->sw_end_date)) {
            if(Carbon::parse($mainSettings->sw_end_date)->addDays(2)->toDateString() <= Carbon::now()->toDateString()){
                if(in_array($route, $default_permissions)){
                    Auth::guard('sw')->user()->is_super_user = false;
                    return $next($request);
                }
            }else if(@in_array($route, $permissions) || (@Auth::guard('sw')->user()->is_super_user)) {
                return $next($request);
            }
        } else {
            // If sw_end_date is not set, just check permissions
            if(@in_array($route, $permissions) || (@Auth::guard('sw')->user()->is_super_user)) {
                return $next($request);
            }
        }
        
        return redirect()->route('sw.dashboard');
    }

}
