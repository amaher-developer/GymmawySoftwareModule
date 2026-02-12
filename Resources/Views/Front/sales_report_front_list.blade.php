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
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css"/>
    <style>
        .summary-card {
            transition: transform 0.2s ease-in-out;
        }
        .summary-card:hover {
            transform: translateY(-2px);
        }
        .total-sales-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .net-total-card {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        @if($lang == 'ar')
            .static-info.align-reverse .name, .static-info.align-reverse .value {
                text-align: right;
            }
        @else
            .static-info.align-reverse .name, .static-info.align-reverse .value {
                text-align: left;
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
                    <i class="ki-outline ki-chart-simple-2 fs-2 me-3"></i>
                    <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.sales_report')}}</span>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <!--begin::Filter-->
                    <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_sales_filter_collapse">
                        <i class="ki-outline ki-filter fs-6"></i>
                        {{ trans('sw.filter')}}
                    </button>
                    <!--end::Filter-->

                    <!--begin::Export-->
                    @if((count(array_intersect(@(array)$swUser->permissions, ['exportSalesReportPDF', 'exportSalesReportExcel'])) > 0) || $swUser->is_super_user)
                        <div class="m-0">
                            <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                <i class="ki-outline ki-exit-down fs-6"></i>
                                {{ trans('sw.download')}}
                            </button>
                            <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                                @if(in_array('exportSalesReportExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.exportSalesReportExcel', ['from' => request('from') ?? $from, 'to' => request('to') ?? $to, 'user' => request('user')])}}" class="menu-link px-3">
                                            <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                            {{ trans('sw.excel_export')}}
                                        </a>
                                    </div>
                                @endif
                                @if(in_array('exportSalesReportPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.exportSalesReportPDF', ['from' => request('from') ?? $from, 'to' => request('to') ?? $to, 'user' => request('user')])}}" class="menu-link px-3">
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
            <!--begin::Filter-->
            <div class="collapse show" id="kt_sales_filter_collapse">
                <div class="card card-body mb-5">
                    <form id="form_filter" action="{{ route('sw.salesReport') }}" method="get">
                        <div class="row g-6">
                            <div class="col-md-6">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                                <div class="input-group date-picker input-daterange">
                                    <input type="text" class="form-control" name="from" id="from_date" value="{{ $from }}" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                    <span class="input-group-text">{{ trans('sw.to')}}</span>
                                    <input type="text" class="form-control" name="to" id="to_date" value="{{ $to }}" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.users')}}</label>
                                <select name="user" class="form-select form-select-solid">
                                    <option value="">{{ trans('sw.all_users')}}...</option>
                                    @foreach($users as $user)
                                        <option value="{{$user->id}}" @if(isset($_GET['user']) && ((request('user') != "") && (request('user') == $user->id))) selected="" @endif>{{$user->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary fw-semibold px-6 w-100">
                                    <i class="ki-outline ki-check fs-6"></i>
                                    {{ trans('sw.filter')}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!--end::Filter-->

            <!--begin::Info Notice-->
            <div class="alert alert-info d-flex align-items-center mb-5">
                <i class="ki-outline ki-information-5 fs-2x text-info me-3"></i>
                <div class="d-flex flex-column">
                    <span class="fs-6 fw-semibold">{{ trans('sw.sales_report_info_title')}}</span>
                    <span class="fs-7 text-muted">{{ trans('sw.sales_report_info_desc')}}</span>
                </div>
            </div>
            <!--end::Info Notice-->

            <!--begin::Total Sales & Net Total Cards-->
            <div class="row mb-5 g-4">
                <div class="col-md-6">
                    <div class="card total-sales-card summary-card h-100">
                        <div class="card-body p-6">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-70px me-5">
                                        <div class="symbol-label bg-white bg-opacity-20">
                                            <i class="ki-outline ki-dollar fs-2x text-white"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fs-4 fw-semibold text-white opacity-75">{{ trans('sw.total_sales')}}</span>
                                        <span class="fs-1 fw-bold text-white">{{ number_format($totalSales, 2) }}</span>
                                        <span class="fs-7 text-white opacity-75">{{ trans('sw.from')}} {{ $from }} {{ trans('sw.to')}} {{ $to }}</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column align-items-end">
                                    <i class="ki-outline ki-chart-line-up fs-3x text-white opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card net-total-card summary-card h-100">
                        <div class="card-body p-6">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-70px me-5">
                                        <div class="symbol-label bg-white bg-opacity-20">
                                            <i class="ki-outline ki-wallet fs-2x text-white"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fs-4 fw-semibold text-white opacity-75">{{ trans('sw.net_total')}}</span>
                                        <span class="fs-1 fw-bold text-white">{{ number_format($netTotal, 2) }}</span>
                                        <span class="fs-7 text-white opacity-75">{{ trans('sw.total_sales')}} + {{ trans('sw.moneybox')}}</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column align-items-end">
                                    <i class="ki-outline ki-chart-line-up fs-3x text-white opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Total Sales & Net Total Cards-->

            <!--begin::Sales Breakdown by Payment Method-->
            <div class="row g-4 mb-5">
                <div class="col-12">
                    <h4 class="fw-bold text-gray-900 mb-4">
                        <i class="ki-outline ki-wallet fs-4 me-2"></i>
                        {{ trans('sw.sales_by_payment_method')}}
                    </h4>
                </div>
                @foreach($payment_types as $paymentType)
                    <div class="col-md-4">
                        <div class="card bg-light-primary summary-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-4">
                                        <div class="symbol-label bg-primary">
                                            @if($paymentType->payment_id == 0)
                                                <i class="ki-outline ki-wallet fs-2x text-white"></i>
                                            @elseif($paymentType->payment_id == 1)
                                                <i class="ki-outline ki-credit-cart fs-2x text-white"></i>
                                            @else
                                                <i class="ki-outline ki-bank fs-2x text-white"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fs-6 fw-semibold text-gray-900">{{ $paymentType->name }}</span>
                                        <span class="fs-2 fw-bold text-primary">{{ number_format($salesByPaymentType[$paymentType->payment_id]['amount'] ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!--begin::Store Balance Sales-->
                <div class="col-md-4">
                    <div class="card bg-light-warning summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <div class="symbol-label bg-warning">
                                        <i class="ki-outline ki-abstract-26 fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.store_balance_sales')}}</span>
                                    <span class="fs-2 fw-bold text-warning">{{ number_format($storeBalanceSales, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Store Balance Sales-->
            </div>
            <!--end::Sales Breakdown by Payment Method-->

            <!--begin::Sales Breakdown by Category-->
            <div class="row g-4 mb-5">
                <div class="col-12">
                    <h4 class="fw-bold text-gray-900 mb-4">
                        <i class="ki-outline ki-category fs-4 me-2"></i>
                        {{ trans('sw.sales_by_category')}}
                    </h4>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-success summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <div class="symbol-label bg-success">
                                        <i class="ki-outline ki-profile-user fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.subscription_sales')}}</span>
                                    <span class="fs-2 fw-bold text-success">{{ number_format($subscriptionSales, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-info summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <div class="symbol-label bg-info">
                                        <i class="ki-outline ki-people fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.pt_sales')}}</span>
                                    <span class="fs-2 fw-bold text-info">{{ number_format($ptSales, 2) }}</span>
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
                                        <i class="ki-outline ki-calendar fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.activity_sales')}}</span>
                                    <span class="fs-2 fw-bold text-danger">{{ number_format($activitySales, 2) }}</span>
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
                                        <i class="ki-outline ki-shop fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.store_sales')}}</span>
                                    <span class="fs-2 fw-bold text-dark">{{ number_format($storeSales, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Sales Breakdown by Category-->

            <!--begin::Moneybox Operations-->
            <div class="row g-4 mb-5">
                <div class="col-12">
                    <h4 class="fw-bold text-gray-900 mb-4">
                        <i class="ki-outline ki-wallet fs-4 me-2"></i>
                        {{ trans('sw.moneybox')}}
                    </h4>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light-success summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <div class="symbol-label bg-success">
                                        <i class="ki-outline ki-plus-circle fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.add_to_money_box')}}</span>
                                    <span class="fs-2 fw-bold text-success">{{ number_format($moneyboxAdd, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light-danger summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <div class="symbol-label bg-danger">
                                        <i class="ki-outline ki-minus-circle fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.withdraw_from_money_box')}}</span>
                                    <span class="fs-2 fw-bold text-danger">{{ number_format($moneyboxWithdraw, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light-warning summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <div class="symbol-label bg-warning">
                                        <i class="ki-outline ki-arrow-up-right fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.withdraw_earning')}}</span>
                                    <span class="fs-2 fw-bold text-warning">{{ number_format($moneyboxWithdrawEarnings, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Moneybox Operations-->

            <!--begin::Important Note-->
            <div class="alert alert-warning d-flex align-items-center">
                <i class="ki-outline ki-information-5 fs-2x text-warning me-3"></i>
                <div class="d-flex flex-column">
                    <span class="fs-6 fw-semibold">{{ trans('sw.sales_report_note_title')}}</span>
                    <span class="fs-7 text-muted">{{ trans('sw.sales_report_note_desc')}}</span>
                </div>
            </div>
            <!--end::Important Note-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
@endsection

@section('scripts')
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}" type="text/javascript"></script>
    @parent
    <script>
        jQuery(document).ready(function() {
            var today = new Date();
            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true,
                orientation: 'bottom auto',
                defaultDate: { year: today.getFullYear(), month: today.getMonth(), day: today.getDate() },
                defaultViewDate: { year: today.getFullYear(), month: today.getMonth(), day: today.getDate() }
            });
        });
    </script>
@endsection
