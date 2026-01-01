{{-- Helper Tools / Calculators Subsystem Sidebar --}}
<div class="card mb-5">
    <div class="card-header">
        <div class="card-title">
            <h3 class="fw-bold d-flex align-items-center">
                <i class="ki-outline ki-calculator fs-2 me-2"></i>
                {{ trans('sw.helper_tools')}}
            </h3>
        </div>
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center @if(\Route::currentRouteName() == 'sw.calculateIBW') active @endif"
                   href="{{route('sw.calculateIBW')}}">
                    <i class="fa fa-calculator me-3"></i>
                    <span class="fw-semibold">{{ trans('sw.calculate_ibw')}}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center @if(\Route::currentRouteName() == 'sw.calculateCalories') active @endif"
                   href="{{route('sw.calculateCalories')}}">
                    <i class="fa fa-fire me-3"></i>
                    <span class="fw-semibold">{{ trans('sw.calculate_calories')}}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center @if(\Route::currentRouteName() == 'sw.calculateBMI') active @endif"
                   href="{{route('sw.calculateBMI')}}">
                    <i class="fa fa-line-chart me-3"></i>
                    <span class="fw-semibold">{{ trans('sw.calculate_bmi')}}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center @if(\Route::currentRouteName() == 'sw.calculateWater') active @endif"
                   href="{{route('sw.calculateWater')}}">
                    <i class="fa fa-tint me-3"></i>
                    <span class="fw-semibold">{{ trans('sw.calculate_water')}}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center @if(\Route::currentRouteName() == 'sw.calculateVatPercentage') active @endif"
                   href="{{route('sw.calculateVatPercentage')}}">
                    <i class="fa fa-percent me-3"></i>
                    <span class="fw-semibold">{{ trans('sw.calculate_vat_percentage')}}</span>
                </a>
            </li>
        </ul>
    </div>
</div>
