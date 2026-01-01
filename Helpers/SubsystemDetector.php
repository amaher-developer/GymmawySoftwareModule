<?php

namespace Modules\Software\Helpers;

class SubsystemDetector
{
    /**
     * Detect the current subsystem based on route name
     *
     * @return string|null
     */
    public static function detectCurrentSubsystem()
    {
        $routeName = request()->route() ? request()->route()->getName() : '';

        // Map route patterns to subsystems
        $subsystemMap = [
            'store' => ['sw.createStoreOrderPOS', 'sw.listStoreProducts', 'sw.listStoreOrders', 'sw.listStoreOrderVendor', 'sw.listStoreCategory', 'sw.storeStatistics'],
            'subscription' => ['sw.listMember', 'sw.memberSubscriptionStatistics', 'sw.listPotentialMember', 'sw.reportRenewMemberList', 'sw.reportExpireMemberList'],
            'activity_reservation' => ['sw.listActivity', 'sw.listReservation', 'sw.listReservationMember', 'sw.listNonMember'],
            'training' => ['sw.listTrainingPlan', 'sw.listTrainingMemberLog', 'sw.listTrainingMedicine'],
            'pt' => ['sw.listPTSubscription', 'sw.listPTClass', 'sw.listPTMember', 'sw.listPTSessions', 'sw.reportPTSubscriptionMemberList'],
            'calculates' => ['sw.calculateIBW', 'sw.calculateCalories', 'sw.calculateBMI', 'sw.calculateWater', 'sw.calculateVatPercentage'],
        ];

        // Check if current route matches any subsystem
        foreach ($subsystemMap as $subsystem => $routes) {
            foreach ($routes as $route) {
                if (str_starts_with($routeName, $route)) {
                    return $subsystem;
                }
            }
        }

        // Check URL segments as fallback
        $segments = request()->segments();
        if (count($segments) >= 2) {
            $secondSegment = $segments[1] ?? '';

            // Map URL segments to subsystems
            $segmentMap = [
                'store' => 'store',
                'member' => 'subscription',
                'potential-member' => 'subscription',
                'activity' => 'activity_reservation',
                'reservation' => 'activity_reservation',
                'non-member' => 'activity_reservation',
                'training' => 'training',
                'pt' => 'pt',
                'calculates' => 'calculates',
            ];

            if (isset($segmentMap[$secondSegment])) {
                return $segmentMap[$secondSegment];
            }
        }

        return null;
    }

    /**
     * Get subsystem configuration
     *
     * @param string $subsystem
     * @return array
     */
    public static function getSubsystemConfig($subsystem)
    {
        $configs = [
            'store' => [
                'name' => 'sw.store_pos_system',
                'icon' => 'ki-outline ki-shop',
                'feature_flag' => 'active_store',
                'blade_path' => 'software::layouts.sidebars.store',
            ],
            'subscription' => [
                'name' => 'sw.membership_subscription',
                'icon' => 'ki-outline ki-user',
                'feature_flag' => 'active_subscription',
                'blade_path' => 'software::layouts.sidebars.subscription',
            ],
            'activity_reservation' => [
                'name' => 'sw.activities_reservations',
                'icon' => 'ki-outline ki-calendar',
                'feature_flag' => 'active_activity_reservation',
                'blade_path' => 'software::layouts.sidebars.activity_reservation',
            ],
            'training' => [
                'name' => 'sw.training_coaching',
                'icon' => 'ki-outline ki-teacher',
                'feature_flag' => 'active_training',
                'blade_path' => 'software::layouts.sidebars.training',
            ],
            'pt' => [
                'name' => 'sw.pt',
                'icon' => 'ki-outline ki-security-user',
                'feature_flag' => 'active_pt',
                'blade_path' => 'software::layouts.sidebars.pt',
            ],
            'calculates' => [
                'name' => 'sw.helper_tools',
                'icon' => 'ki-outline ki-calculator',
                'feature_flag' => null, // Always available
                'blade_path' => 'software::layouts.sidebars.calculates',
            ],
        ];

        return $configs[$subsystem] ?? null;
    }

    /**
     * Check if subsystem is enabled
     *
     * @param string $subsystem
     * @param object $mainSettings
     * @return bool
     */
    public static function isSubsystemEnabled($subsystem, $mainSettings)
    {
        $config = self::getSubsystemConfig($subsystem);

        if (!$config) {
            return false;
        }

        // If no feature flag, subsystem is always enabled
        if (!$config['feature_flag']) {
            return true;
        }

        return (bool) data_get($mainSettings, $config['feature_flag']);
    }
}
