{{-- Personal Training Subsystem Sidebar --}}
<div class="card mb-5">
    <div class="card-header">
        <div class="card-title">
            <h3 class="fw-bold d-flex align-items-center">
                <i class="ki-outline ki-security-user fs-2 me-2"></i>
                {{ trans('sw.pt') }}
            </h3>
        </div>
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-pills flex-column">
            @if ($mainSettings->active_pt && $swUser && (isset($permissionsMap['listPTSubscription']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listPTSubscription') active @endif"
                       href="{{ route('sw.listPTSubscription') }}">
                        <i class="ki-outline ki-award me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.pt_subscriptions') }}</span>
                    </a>
                </li>
            @endif

            @if ($mainSettings->active_pt && $swUser && (isset($permissionsMap['listPTClass']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listPTClass') active @endif"
                       href="{{ route('sw.listPTClass') }}">
                        <i class="ki-outline ki-calendar-8 me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.pt_classes') }}</span>
                    </a>
                </li>
            @endif

            @if ($mainSettings->active_pt && $swUser && (isset($permissionsMap['listPTMember']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listPTMember') active @endif"
                       href="{{ route('sw.listPTMember') }}">
                        <i class="ki-outline ki-profile-circle me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.pt_members') }}</span>
                    </a>
                </li>
            @endif

            @if ($mainSettings->active_pt && $swUser && (isset($permissionsMap['listPTMember']) || isset($permissionsMap['listPTClass']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listPTSessions') active @endif"
                       href="{{ route('sw.listPTSessions') }}">
                        <i class="ki-outline ki-logistic me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.pt_sessions2') }}</span>
                    </a>
                </li>
            @endif

            @if ($mainSettings->active_pt && $swUser && (isset($permissionsMap['listPTTrainer']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listPTTrainer') active @endif"
                       href="{{ route('sw.listPTTrainer') }}">
                        <i class="ki-outline ki-user-edit me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.pt_trainers') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && @$mainSettings->active_pt && (isset($permissionsMap['reportPTSubscriptionMemberList']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.reportPTSubscriptionMemberList') active @endif"
                       href="{{ route('sw.reportPTSubscriptionMemberList') }}">
                        <i class="ki-outline ki-chart-simple-2 me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.report_pt_subscriptions') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && @$mainSettings->active_pt && (isset($permissionsMap['reportTodayPTMemberList']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.reportTodayPTMemberList') active @endif"
                       href="{{ route('sw.reportTodayPTMemberList') }}">
                        <i class="ki-outline ki-status me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.client_pt_attendees_today') }}</span>
                    </a>
                </li>
            @endif

            @if (@$mainSettings->active_pt && ($swUser && (isset($permissionsMap['statistics']) || $isSuperUser)))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.ptSubscriptionStatistics') active @endif"
                       href="{{ route('sw.ptSubscriptionStatistics') }}">
                        <i class="ki-outline ki-chart-line-up-2 me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.pt_subscription_statistics') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
