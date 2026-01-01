{{-- Training & Coaching Subsystem Sidebar --}}
<div class="card mb-5">
    <div class="card-header">
        <div class="card-title">
            <h3 class="fw-bold d-flex align-items-center">
                <i class="ki-outline ki-teacher fs-2 me-2"></i>
                {{ trans('sw.training_coaching') }}
            </h3>
        </div>
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-pills flex-column">
            @if ($mainSettings->active_training && $swUser && (isset($permissionsMap['listTrainingPlan']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listTrainingPlan') active @endif"
                       href="{{ route('sw.listTrainingPlan') }}">
                        <i class="ki-outline ki-book-open me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.training_plans') }}</span>
                    </a>
                </li>
            @endif

            @if ($mainSettings->active_training && $swUser && (isset($permissionsMap['listTrainingMemberLog']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listTrainingMemberLog') active @endif"
                       href="{{ route('sw.listTrainingMemberLog') }}">
                        <i class="ki-outline ki-notepad me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.member_logs') }}</span>
                    </a>
                </li>
            @endif

            @if ($mainSettings->active_training && $swUser && (isset($permissionsMap['listTrainingMedicine']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listTrainingMedicine') active @endif"
                       href="{{ route('sw.listTrainingMedicine') }}">
                        <i class="ki-outline ki-capsule me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.supplements_medicine') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
