{{-- Membership & Subscription Subsystem Sidebar --}}
<div class="card mb-5">
    <div class="card-header">
        <div class="card-title">
            <h3 class="fw-bold d-flex align-items-center">
                <i class="ki-outline ki-user fs-2 me-2"></i>
                {{ trans('sw.membership_subscription') }}
            </h3>
        </div>
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-pills flex-column">
            @if (@$mainSettings->active_subscription && ($swUser && (isset($permissionsMap['listMember']) || $isSuperUser)))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listMember') active @endif"
                       href="{{ route('sw.listMember') }}">
                        <i class="ki-outline ki-profile-user me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.subscribed_clients') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && (isset($permissionsMap['listPotentialMember']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listPotentialMember') active @endif"
                       href="{{ route('sw.listPotentialMember') }}">
                        <i class="ki-outline ki-user-tick me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.potential_clients') }}</span>
                    </a>
                </li>
            @endif

            @if (@$mainSettings->active_subscription && ($swUser && (isset($permissionsMap['statistics']) || $isSuperUser)))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.memberSubscriptionStatistics') active @endif"
                       href="{{ route('sw.memberSubscriptionStatistics') }}">
                        <i class="ki-outline ki-chart-simple me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.member_subscription_statistics') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && @$mainSettings->active_subscription && (isset($permissionsMap['reportRenewMemberList']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.reportRenewMemberList') active @endif"
                       href="{{ route('sw.reportRenewMemberList') }}">
                        <i class="ki-outline ki-arrows-circle me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.memberships_renewal_report') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && @$mainSettings->active_subscription && (isset($permissionsMap['reportExpireMemberList']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.reportExpireMemberList') active @endif"
                       href="{{ route('sw.reportExpireMemberList') }}">
                        <i class="ki-outline ki-time me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.memberships_expire_report') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && @$mainSettings->active_subscription && (isset($permissionsMap['listSubscription']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Request::is(($lang ?? 'ar') . '/subscription*')) active @endif"
                       href="{{ route('sw.listSubscription') }}">
                        <i class="ki-outline ki-setting-2 me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.memberships') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
