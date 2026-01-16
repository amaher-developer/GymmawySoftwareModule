@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
        <!--end::Item-->
    </ul>
    <!--end::Breadcrumb-->
@endsection
@section('styles')
    <style>
        .summary-card {
            transition: transform 0.2s ease-in-out;
        }
        .summary-card:hover {
            transform: translateY(-2px);
        }
        .balance-positive {
            color: #0fa751 !important;
        }
        .balance-negative {
            color: #ec2d38 !important;
        }
        .badge-credit {
            background-color: #e8f5e9;
            color: #0fa751;
        }
        .badge-debt {
            background-color: #ffebee;
            color: #ec2d38;
        }
        .badge-remaining {
            background-color: #fff3e0;
            color: #f57c00;
        }
        @if($lang == 'ar')
            .static-info.align-reverse .name, .static-info.align-reverse .value {
                text-align: right;
            }
        @endif
    </style>
@endsection
@section('page_body')
    <!--begin::Card-->
    <div class="card card-flush">
        <!--begin::Card header-->
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <div class="d-flex align-items-center my-1">
                    <i class="ki-outline ki-wallet fs-2 me-3"></i>
                    <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.customer_balances_report')}}</span>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <!--begin::Export-->
                    @if((count(array_intersect(@(array)$swUser->permissions, ['exportCustomerBalancesPDF', 'exportCustomerBalancesExcel'])) > 0) || $swUser->is_super_user)
                        <div class="m-0">
                            <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                <i class="ki-outline ki-exit-down fs-6"></i>
                                {{ trans('sw.download')}}
                            </button>
                            <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                                @if(in_array('exportCustomerBalancesExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.exportCustomerBalancesExcel', ['search' => request('search'), 'balance_type' => request('balance_type')])}}" class="menu-link px-3">
                                            <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                            {{ trans('sw.excel_export')}}
                                        </a>
                                    </div>
                                @endif
                                @if(in_array('exportCustomerBalancesPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.exportCustomerBalancesPDF', ['search' => request('search'), 'balance_type' => request('balance_type')])}}" class="menu-link px-3">
                                            <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                            {{ trans('sw.pdf_export')}}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    <!--end::Export-->
                </div>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body pt-0">
            <!--begin::Info Notice-->
            <div class="alert alert-info d-flex align-items-center mb-5">
                <i class="ki-outline ki-information-5 fs-2x text-info me-3"></i>
                <div class="d-flex flex-column">
                    <span class="fs-6 fw-semibold">{{ trans('sw.customer_balances_info_title')}}</span>
                    <span class="fs-7 text-muted">{{ trans('sw.customer_balances_info_desc')}}</span>
                </div>
            </div>
            <!--end::Info Notice-->

            <!--begin::Summary Cards-->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card bg-light-success summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <div class="symbol-label bg-success">
                                        <i class="ki-outline ki-arrow-up fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-7 fw-semibold text-gray-600">{{ trans('sw.total_store_credit')}}</span>
                                    <span class="fs-2 fw-bold text-success">{{ number_format($totalStoreCredit, 2) }}</span>
                                    <span class="fs-8 text-muted">{{ $customersWithStoreCredit }} {{ trans('sw.customers')}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-danger summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <div class="symbol-label bg-danger">
                                        <i class="ki-outline ki-arrow-down fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-7 fw-semibold text-gray-600">{{ trans('sw.total_store_debt')}}</span>
                                    <span class="fs-2 fw-bold text-danger">{{ number_format($totalStoreDebt, 2) }}</span>
                                    <span class="fs-8 text-muted">{{ $customersWithStoreDebt }} {{ trans('sw.customers')}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-warning summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <div class="symbol-label bg-warning">
                                        <i class="ki-outline ki-time fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-7 fw-semibold text-gray-600">{{ trans('sw.total_remaining_amount')}}</span>
                                    <span class="fs-2 fw-bold text-warning">{{ number_format($totalRemainingAmount, 2) }}</span>
                                    <span class="fs-8 text-muted">{{ $customersWithRemaining }} {{ trans('sw.customers')}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-dark summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <div class="symbol-label bg-dark">
                                        <i class="ki-outline ki-people fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-7 fw-semibold text-gray-600">{{ trans('sw.total_customers_with_balance')}}</span>
                                    <span class="fs-2 fw-bold text-dark">{{ $totalCustomers }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Summary Cards-->

            <!--begin::Remaining Breakdown Cards-->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card bg-light-primary summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-primary">
                                        <i class="ki-outline ki-calendar fs-2 text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-8 fw-semibold text-gray-600">{{ trans('sw.subscription_remaining')}}</span>
                                    <span class="fs-4 fw-bold text-primary">{{ number_format($totalSubscriptionRemaining, 2) }}</span>
                                    <span class="fs-9 text-muted">{{ $customersWithSubscriptionRemaining }} {{ trans('sw.customers')}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light-info summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-info">
                                        <i class="ki-outline ki-user-tick fs-2 text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-8 fw-semibold text-gray-600">{{ trans('sw.pt_remaining')}}</span>
                                    <span class="fs-4 fw-bold text-info">{{ number_format($totalPTRemaining, 2) }}</span>
                                    <span class="fs-9 text-muted">{{ $customersWithPTRemaining }} {{ trans('sw.customers')}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light-secondary summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-secondary">
                                        <i class="ki-outline ki-brifecase-timer fs-2 text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-8 fw-semibold text-gray-600">{{ trans('sw.training_remaining')}}</span>
                                    <span class="fs-4 fw-bold text-gray-800">{{ number_format($totalTrainingRemaining, 2) }}</span>
                                    <span class="fs-9 text-muted">{{ $customersWithTrainingRemaining }} {{ trans('sw.customers')}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Remaining Breakdown Cards-->

            <!--begin::Filter & Search-->
            <div class="d-flex flex-wrap gap-3 mb-5">
                <form class="d-flex flex-wrap gap-3 w-100" action="{{ route('sw.customerBalancesReport') }}" method="get">
                    <div class="d-flex align-items-center position-relative" style="max-width: 300px;">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                        <input type="text" name="search" class="form-control form-control-solid ps-12" value="{{ request('search') }}" placeholder="{{ trans('sw.search_customer')}}">
                    </div>
                    <select name="balance_type" class="form-select form-select-solid w-auto">
                        <option value="">{{ trans('sw.all_balances')}}</option>
                        <option value="store_credit" {{ request('balance_type') == 'store_credit' ? 'selected' : '' }}>{{ trans('sw.customers_with_store_credit')}}</option>
                        <option value="store_debt" {{ request('balance_type') == 'store_debt' ? 'selected' : '' }}>{{ trans('sw.customers_with_store_debt')}}</option>
                        <option value="remaining" {{ request('balance_type') == 'remaining' ? 'selected' : '' }}>{{ trans('sw.customers_with_remaining')}}</option>
                        <option value="remaining_subscription" {{ request('balance_type') == 'remaining_subscription' ? 'selected' : '' }}>{{ trans('sw.customers_with_subscription_remaining')}}</option>
                        <option value="remaining_pt" {{ request('balance_type') == 'remaining_pt' ? 'selected' : '' }}>{{ trans('sw.customers_with_pt_remaining')}}</option>
                        <option value="remaining_training" {{ request('balance_type') == 'remaining_training' ? 'selected' : '' }}>{{ trans('sw.customers_with_training_remaining')}}</option>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-outline ki-magnifier fs-3"></i>
                        {{ trans('sw.filter')}}
                    </button>
                    @if(request('search') || request('balance_type'))
                        <a href="{{ route('sw.customerBalancesReport') }}" class="btn btn-light">
                            <i class="ki-outline ki-cross fs-3"></i>
                            {{ trans('admin.reset')}}
                        </a>
                    @endif
                </form>
            </div>
            <!--end::Filter & Search-->

            <!--begin::Total count-->
            <div class="d-flex align-items-center mb-5">
                <div class="symbol symbol-50px me-5">
                    <div class="symbol-label bg-light-primary">
                        <i class="ki-outline ki-chart-simple fs-2x text-primary"></i>
                    </div>
                </div>
                <div class="d-flex flex-column">
                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('admin.total_count')}}</span>
                    <span class="fs-2 fw-bold text-primary">{{ $total }}</span>
                </div>
            </div>
            <!--end::Total count-->

            @if(count($members) > 0)
                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-80px">
                                    <i class="ki-outline ki-barcode fs-6 me-2"></i>{{ trans('sw.code')}}
                                </th>
                                <th class="min-w-120px">
                                    <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.customer_name')}}
                                </th>
                                <th class="min-w-80px">
                                    <i class="ki-outline ki-phone fs-6 me-2"></i>{{ trans('sw.phone')}}
                                </th>
                                <th class="min-w-80px">
                                    <i class="ki-outline ki-wallet fs-6 me-2"></i>{{ trans('sw.store_balance')}}
                                </th>
                                <th class="min-w-80px">
                                    <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.subscription_remaining_short')}}
                                </th>
                                <th class="min-w-80px">
                                    <i class="ki-outline ki-user-tick fs-6 me-2"></i>{{ trans('sw.pt_remaining_short')}}
                                </th>
                                <th class="min-w-80px">
                                    <i class="ki-outline ki-brifecase-timer fs-6 me-2"></i>{{ trans('sw.training_remaining_short')}}
                                </th>
                                <th class="min-w-80px">
                                    <i class="ki-outline ki-time fs-6 me-2"></i>{{ trans('sw.total_remaining_short')}}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                            @foreach($members as $member)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-gray-800">{{ $member->code }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-40px me-3">
                                                <img src="{{ $member->image }}" alt="{{ $member->name }}" class="rounded">
                                            </div>
                                            <div class="d-flex flex-column">
                                                <a href="{{ route('sw.showMemberProfile', $member->id) }}" class="text-gray-900 fw-bold text-hover-primary">{{ $member->name }}</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $member->phone }}</span>
                                    </td>
                                    <td>
                                        @if($member->store_balance != 0)
                                            <span class="fs-6 fw-bold {{ $member->store_balance > 0 ? 'balance-positive' : 'balance-negative' }}">
                                                {{ number_format($member->store_balance, 2) }}
                                            </span>
                                            @if($member->store_balance > 0)
                                                <span class="badge badge-credit fs-9 ms-1">{{ trans('sw.credit')}}</span>
                                            @else
                                                <span class="badge badge-debt fs-9 ms-1">{{ trans('sw.debt')}}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($member->subscription_remaining > 0)
                                            <span class="fs-6 fw-bold text-primary">
                                                {{ number_format($member->subscription_remaining, 2) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($member->pt_remaining > 0)
                                            <span class="fs-6 fw-bold text-info">
                                                {{ number_format($member->pt_remaining, 2) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($member->training_remaining > 0)
                                            <span class="fs-6 fw-bold text-gray-800">
                                                {{ number_format($member->training_remaining, 2) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($member->remaining_amount > 0)
                                            <span class="fs-6 fw-bold text-warning">
                                                {{ number_format($member->remaining_amount, 2) }}
                                            </span>
                                            <span class="badge badge-remaining fs-9 ms-1">{{ trans('sw.unpaid')}}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!--end::Table-->

                <!--begin::Pagination-->
                <div class="d-flex flex-stack flex-wrap pt-10">
                    <div class="fs-6 fw-semibold text-gray-700">
                        {{ trans('sw.showing_entries', [
                            'from' => $members->firstItem() ?? 0,
                            'to' => $members->lastItem() ?? 0,
                            'total' => $members->total()
                        ]) }}
                    </div>
                    <ul class="pagination">
                        {!! $members->appends($search_query)->render() !!}
                    </ul>
                </div>
                <!--end::Pagination-->
            @else
                <!--begin::Empty State-->
                <div class="text-center py-10">
                    <div class="symbol symbol-100px mb-5">
                        <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                            <i class="ki-outline ki-check-circle fs-2x text-success"></i>
                        </div>
                    </div>
                    <div class="fs-1 fw-bold text-gray-900 mb-3">{{ trans('sw.no_balances_found')}}</div>
                    <div class="fs-6 text-gray-600">{{ trans('sw.all_customers_settled')}}</div>
                </div>
                <!--end::Empty State-->
            @endif
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
@endsection
