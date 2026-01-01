{{-- Activities & Reservations Subsystem Sidebar --}}
<div class="card mb-5">
    <div class="card-header">
        <div class="card-title">
            <h3 class="fw-bold d-flex align-items-center">
                <i class="ki-outline ki-calendar fs-2 me-2"></i>
                {{ trans('sw.activities_reservations') }}
            </h3>
        </div>
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-pills flex-column">
            @if ((@$mainSettings->active_activity || @$mainSettings->active_activity_reservation) && ($swUser && (isset($permissionsMap['listActivity']) || $isSuperUser)))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listActivity') active @endif"
                       href="{{ route('sw.listActivity') }}">
                        <i class="ki-outline ki-verify me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.activities') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && @$mainSettings->active_activity_reservation && (isset($permissionsMap['listReservation']) || isset($permissionsMap['createReservation']) || isset($permissionsMap['editReservation']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listReservation') active @endif"
                       href="{{ route('sw.listReservation') }}">
                        <i class="ki-outline ki-calendar-add me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.reservations') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && (($mainSettings->active_mobile || $mainSettings->active_website) && (isset($permissionsMap['listReservationMember']) || $isSuperUser)))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listReservationMember') active @endif"
                       href="{{ route('sw.listReservationMember') }}">
                        <i class="ki-outline ki-people me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.reservation_clients') }}</span>
                    </a>
                </li>
            @endif

            @if ((@$mainSettings->active_activity || @$mainSettings->active_activity_reservation) && ($swUser && (isset($permissionsMap['listNonMember']) || $isSuperUser)))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listNonMember') active @endif"
                       href="{{ route('sw.listNonMember') }}">
                        <i class="ki-outline ki-user-square me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.daily_clients') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && (@$mainSettings->active_activity || @$mainSettings->active_activity_reservation) && (isset($permissionsMap['reportTodayNonMemberList']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.reportTodayNonMemberList') active @endif"
                       href="{{ route('sw.reportTodayNonMemberList') }}">
                        <i class="ki-outline ki-badge me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.non_client_attendees_today') }}</span>
                    </a>
                </li>
            @endif

            @if (@$mainSettings->active_activity || @$mainSettings->active_activity_reservation && ($swUser && (isset($permissionsMap['statistics']) || $isSuperUser)))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.nonMemberStatistics') active @endif"
                       href="{{ route('sw.nonMemberStatistics') }}">
                        <i class="ki-outline ki-chart-line-up me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.non_member_statistics') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
