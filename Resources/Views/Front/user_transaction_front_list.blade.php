@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home')}}</a>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listUser') }}" class="text-muted text-hover-primary">{{ trans('sw.users')}}</a>
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
@section('page_body')

<!--begin::Employee Transactions-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-dollar fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.employee_transactions')}}</span>
            </div>
        </div>
        <!--end::Card title-->
        <div class="card-toolbar flex-wrap">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Add Transaction-->
                @if(in_array('createUserTransaction', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createUserTransaction')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add Transaction-->
            </div>
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Filters-->
        <div class="mb-5">
            <form action="" method="get" class="row g-3">
                <!--begin::Search-->
                <div class="col-md-3">
                    <div class="d-flex align-items-center position-relative">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                        <input type="text" name="search" class="form-control form-control-solid ps-12" 
                               value="{{ request('search') }}" placeholder="{{ trans('sw.search_on')}}">
                    </div>
                </div>
                <!--end::Search-->

                <!--begin::Employee Filter-->
                <div class="col-md-3">
                    <select name="employee_id" class="form-select" data-control="select2" data-placeholder="{{ trans('sw.all_employees')}}">
                        <option value="">{{ trans('sw.all_employees')}}</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <!--end::Employee Filter-->

                <!--begin::Transaction Type Filter-->
                <div class="col-md-3">
                    <select name="transaction_type" class="form-select">
                        <option value="">{{ trans('sw.all_transaction_types')}}</option>
                        <option value="monthly_salary" {{ request('transaction_type') == 'monthly_salary' ? 'selected' : '' }}>
                            {{ trans('sw.monthly_salary')}}
                        </option>
                        <option value="commission_private_training" {{ request('transaction_type') == 'commission_private_training' ? 'selected' : '' }}>
                            {{ trans('sw.commission_private_training')}}
                        </option>
                        <option value="commission_subscription_sales" {{ request('transaction_type') == 'commission_subscription_sales' ? 'selected' : '' }}>
                            {{ trans('sw.commission_subscription_sales')}}
                        </option>
                        <option value="bonus" {{ request('transaction_type') == 'bonus' ? 'selected' : '' }}>
                            {{ trans('sw.bonus')}}
                        </option>
                        <option value="advance" {{ request('transaction_type') == 'advance' ? 'selected' : '' }}>
                            {{ trans('sw.advance')}}
                        </option>
                        <option value="penalty_deduction" {{ request('transaction_type') == 'penalty_deduction' ? 'selected' : '' }}>
                            {{ trans('sw.penalty_deduction')}}
                        </option>
                    </select>
                </div>
                <!--end::Transaction Type Filter-->

                <!--begin::Financial Month Filter-->
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ki-outline ki-calendar fs-3"></i>
                        </span>
                        <input type="text" name="financial_month_display" id="financial_month_filter" class="form-control" 
                               placeholder="{{ trans('sw.all_months')}}" value="{{ request('financial_month') }}" readonly>
                        <input type="hidden" name="financial_month" id="financial_month_hidden_filter" value="{{ request('financial_month') }}">
                    </div>
                </div>
                <!--end::Financial Month Filter-->

                <!--begin::Filter Button-->
                <div class="col-md-1">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="ki-outline ki-filter fs-3"></i>
                    </button>
                </div>
                <!--end::Filter Button-->
            </form>
        </div>
        <!--end::Filters-->

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

        @if(count($transactions) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-100px text-nowrap">{{ trans('sw.id')}}</th>
                        <th class="min-w-150px text-nowrap">{{ trans('sw.employee')}}</th>
                        <th class="min-w-150px text-nowrap">{{ trans('sw.transaction_type')}}</th>
                        <th class="min-w-100px text-nowrap">{{ trans('sw.amount')}}</th>
                        <th class="min-w-100px text-nowrap">{{ trans('sw.financial_month')}}</th>
                        <th class="min-w-150px text-nowrap">{{ trans('sw.notes')}}</th>
                        <th class="text-end min-w-70px text-nowrap">{{ trans('admin.actions')}}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <span class="badge badge-light-primary fs-7">#{{ $transaction->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-35px me-3">
                                        <div class="symbol-label fs-3 bg-light-info text-info">
                                            <i class="ki-outline ki-user fs-2"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-gray-800 fs-6 fw-bold">
                                            {{ $transaction->employee->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-gray-500 fs-7">
                                            {{ $transaction->employee->email ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $typeColors = [
                                        'monthly_salary' => 'primary',
                                        'commission_private_training' => 'success',
                                        'commission_subscription_sales' => 'info',
                                        'bonus' => 'warning',
                                        'advance' => 'danger',
                                        'penalty_deduction' => 'dark'
                                    ];
                                    $color = $typeColors[$transaction->transaction_type] ?? 'secondary';
                                @endphp
                                <span class="badge badge-light-{{ $color }} fs-7">
                                    {{ $transaction->transaction_type_name }}
                                </span>
                            </td>
                            <td>
                                <span class="text-gray-800 fw-bold fs-6">
                                    {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}{{ number_format($transaction->amount, 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-gray-800">{{ $transaction->financial_month }}</span>
                                @if($transaction->deduction_month)
                                    <div class="text-gray-500 fs-7">
                                        <i class="ki-outline ki-calendar fs-7 me-1"></i>{{ trans('sw.deduction')}}: {{ $transaction->deduction_month }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="text-gray-600">{{ \Illuminate\Support\Str::limit($transaction->notes, 30) }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    @if(in_array('editUserTransaction', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <a href="{{route('sw.editUserTransaction',$transaction->id)}}"
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                    @endif
                                    
                                    @if(in_array('deleteUserTransaction', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <a href="{{route('sw.deleteUserTransaction',$transaction->id)}}"
                                           class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="{{ trans('admin.delete')}}">
                                            <i class="ki-outline ki-trash fs-2"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <!--end::Table-->

            @if(method_exists($transactions, 'links'))
                <!--begin::Pagination-->
                <div class="d-flex justify-content-between align-items-center flex-wrap pt-5">
                    <div class="fs-6 fw-semibold text-gray-700">
                        {{ trans('admin.showing')}} {{ $transactions->firstItem() }} {{ trans('admin.to')}} {{ $transactions->lastItem() }} {{ trans('admin.of')}} {{ $transactions->total() }} {{ trans('admin.entries')}}
                    </div>
                    <div>
                        {{ $transactions->appends(request()->query())->links() }}
                    </div>
                </div>
                <!--end::Pagination-->
            @endif
        @else
            <!--begin::No results-->
            <div class="card-px text-center py-20">
                <i class="ki-outline ki-file-deleted fs-5x text-gray-400 mb-5"></i>
                <h2 class="fs-2 fw-bold mb-2">{{ trans('sw.no_results_found')}}</h2>
                @if(in_array('createUserTransaction', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createUserTransaction')}}" class="btn btn-primary">
                        <i class="ki-outline ki-plus fs-3"></i>
                        {{ trans('sw.add_new_transaction')}}
                    </a>
                @endif
            </div>
            <!--end::No results-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Employee Transactions-->
@endsection

@section('scripts')
<script>
    jQuery(document).ready(function() {
        // Initialize Select2 for employee filter
        $('select[name="employee_id"]').select2();

        // Initialize Month/Year Picker for Filter
        const monthYearFormat = 'YYYY-MM';
        const displayFormat = '{{ $lang == "ar" ? "MMMM YYYY" : "MMMM YYYY" }}';
        
        $('#financial_month_filter').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoUpdateInput: false,
            minYear: 2020,
            maxYear: parseInt(moment().format('YYYY'), 10) + 1,
            locale: {
                format: displayFormat,
                cancelLabel: '{{ trans('sw.clear') }}',
                @if($lang == 'ar')
                applyLabel: '{{ trans('sw.apply') }}',
                monthNames: [
                    'يناير', 'فبراير', 'مارس', 'إبريل', 'مايو', 'يونيو',
                    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
                ],
                @else
                applyLabel: '{{ trans('sw.apply') }}',
                @endif
            },
            @if(request('financial_month'))
            startDate: moment('{{ request('financial_month') }}', monthYearFormat),
            @endif
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format(displayFormat));
            $('#financial_month_hidden_filter').val(picker.startDate.format(monthYearFormat));
        }).on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#financial_month_hidden_filter').val('');
        });

        // Set initial display value if filter is set
        @if(request('financial_month'))
        var filterDate = moment('{{ request('financial_month') }}', monthYearFormat);
        $('#financial_month_filter').val(filterDate.format(displayFormat));
        @endif
    });
</script>
@endsection


