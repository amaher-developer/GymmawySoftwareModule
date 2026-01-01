{{-- Store & POS Subsystem Sidebar --}}
<div class="card mb-5">
    <div class="card-header">
        <div class="card-title">
            <h3 class="fw-bold d-flex align-items-center">
                <i class="ki-outline ki-shop fs-2 me-2"></i>
                {{ trans('sw.store_pos_system') }}
            </h3>
        </div>
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-pills flex-column">
            @if ($swUser && (isset($permissionsMap['createStoreOrderPOS']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.createStoreOrderPOS') active @endif"
                       href="{{ route('sw.createStoreOrderPOS') }}">
                        <i class="ki-outline ki-calculator me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.point_of_sale') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && (isset($permissionsMap['listStoreProducts']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listStoreProducts') active @endif"
                       href="{{ route('sw.listStoreProducts') }}">
                        <i class="ki-outline ki-package me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.products') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && (isset($permissionsMap['listStoreOrders']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listStoreOrders') active @endif"
                       href="{{ route('sw.listStoreOrders') }}">
                        <i class="ki-outline ki-bill me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.sales_invoices') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && (isset($permissionsMap['listStoreOrderVendor']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listStoreOrderVendor') active @endif"
                       href="{{ route('sw.listStoreOrderVendor') }}">
                        <i class="ki-outline ki-delivery me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.purchase_invoices') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && (isset($permissionsMap['listStoreCategory']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.listStoreCategory') active @endif"
                       href="{{ route('sw.listStoreCategory') }}">
                        <i class="ki-outline ki-category me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.store_categories') }}</span>
                    </a>
                </li>
            @endif

            @if ($swUser && @$mainSettings->active_store && (isset($permissionsMap['statistics']) || $isSuperUser))
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center @if(Route::currentRouteName() == 'sw.storeStatistics') active @endif"
                       href="{{ route('sw.storeStatistics') }}">
                        <i class="ki-outline ki-chart-line me-3 fs-3"></i>
                        <span class="fw-semibold">{{ trans('sw.store_statistics') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
