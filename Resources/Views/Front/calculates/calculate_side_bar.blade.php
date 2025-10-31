<!--begin::Calculation Sidebar-->
<div class="card">
    <!--begin::Card header-->
    <div class="card-header">
        <div class="card-title">
            <h3 class="fw-bold">{{ trans('sw.helper_tools')}}</h3>
        </div>
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body">
        <!--begin::Navigation-->
        <ul class="nav nav-pills flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center @if(\Route::currentRouteName() == 'sw.calculateIBW') active @endif" 
                   href="{{route('sw.calculateIBW')}}">
                    <i class="fa fa-calculator me-3"></i>
                    <span class="fw-bold">{{ trans('sw.calculate_ibw')}}</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center @if(\Route::currentRouteName() == 'sw.calculateCalories') active @endif" 
                   href="{{route('sw.calculateCalories')}}">
                    <i class="fa fa-fire me-3"></i>
                    <span class="fw-bold">{{ trans('sw.calculate_calories')}}</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center @if(\Route::currentRouteName() == 'sw.calculateBMI') active @endif" 
                   href="{{route('sw.calculateBMI')}}">
                    <i class="fa fa-line-chart me-3"></i>
                    <span class="fw-bold">{{ trans('sw.calculate_bmi')}}</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center @if(\Route::currentRouteName() == 'sw.calculateWater') active @endif" 
                   href="{{route('sw.calculateWater')}}">
                    <i class="fa fa-tint me-3"></i>
                    <span class="fw-bold">{{ trans('sw.calculate_water')}}</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center @if(\Route::currentRouteName() == 'sw.calculateVatPercentage') active @endif" 
                   href="{{route('sw.calculateVatPercentage')}}">
                    <i class="fa fa-percent me-3"></i>
                    <span class="fw-bold">{{ trans('sw.calculate_vat_percentage')}}</span>
                </a>
            </li>
        </ul>
        <!--end::Navigation-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Calculation Sidebar-->
