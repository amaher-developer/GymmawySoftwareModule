@extends('software::layouts.list')
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
        

        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }


        .loader {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

     
        .right{
            float: left;
        }
        .left{
            float: left;
        }
    </style>
@endsection
@section('list_title') {{ @$title }} @endsection
@section('list_add_button')
<div >
    <button class="btn btn-default btn-block rounded-3" id="members_refresh" onclick="members_refresh()">
        <i class="fa fa-refresh mx-1"></i>
        {{ trans('sw.members_refresh')}}
    </button>
</div>
@endsection
@section('page_body')

    <!--begin::Filter Row-->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <input class="form-control form-control-solid" placeholder="{{ trans('sw.select_date_range') }}" id="kt_daterangepicker_4" readonly/>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-primary w-100" onclick="applyFilter()">
                                <i class="ki-outline ki-filter fs-2"></i>
                                {{ trans('sw.apply') }}
                            </button>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-secondary w-100" onclick="resetFilter()">
                                <i class="ki-outline ki-cross fs-2"></i>
                                {{ trans('sw.reset') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Filter Row-->

    <!--begin::Statistics-->
    <div class="row g-5 g-xl-8 mt-2">
        <!--begin::Daily Revenues-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-success me-5">
                        <i class="ki-outline ki-chart-simple fs-2tx text-success"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.daily_revenues')}}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">
                            @if($revenues > 0)
                                {{number_format($revenues, 2)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Daily Revenues-->

        <!--begin::Daily Expenses-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-danger me-5">
                        <i class="ki-outline ki-minus-circle fs-2tx text-danger"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.daily_expenses')}}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">
                            @if($expenses > 0)
                                {{number_format($expenses, 2)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Daily Expenses-->

        <!--begin::Daily Earnings-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-info me-5">
                        <i class="ki-outline ki-chart-line-up fs-2tx text-info"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.daily_earnings')}}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">
                            @if($earnings != 0)
                                {{number_format($earnings, 2)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Daily Earnings-->

        <!--begin::Money Box-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-primary me-5">
                        <i class="ki-outline ki-wallet fs-2tx text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.moneybox')}}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">
                            @if($money_box_now != 0)
                                {{number_format($money_box_now, 2)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Money Box-->

        <!--begin::Subscribed Clients-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-success me-5">
                        <i class="ki-outline ki-badge fs-2tx text-success"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.subscribed_clients')}}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">{{$members_count}}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Subscribed Clients-->

        <!--begin::Daily Clients-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-warning me-5">
                        <i class="ki-outline ki-calendar-tick fs-2tx text-warning"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.daily_clients')}}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">{{$non_members_count}}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Daily Clients-->

        <!--begin::Potential Clients-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-info me-5">
                        <i class="ki-outline ki-user-tick fs-2tx text-info"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.potential_clients')}}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">{{$potential_members_count}}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Potential Clients-->

        <!--begin::Users-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-primary me-5">
                        <i class="ki-outline ki-profile-user fs-2tx text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.users')}}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">{{$admin_count}}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Users-->

        <!--begin::Reservations-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-info me-5">
                        <i class="ki-outline ki-calendar-tick fs-2tx text-info"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.reservations') }}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">{{$total_reservations ?? 0}}</span>
                        <span class="text-muted fs-8">{{ $from_date && $to_date ? trans('sw.selected_period') : trans('sw.today') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Reservations-->

        <!--begin::Confirmed Reservations-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-success me-5">
                        <i class="ki-outline ki-check-circle fs-2tx text-success"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.confirmed') }}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">{{$confirmed_reservations ?? 0}}</span>
                        <span class="text-muted fs-8">{{ $from_date && $to_date ? trans('sw.selected_period') : trans('sw.today') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Confirmed Reservations-->

        <!--begin::Attended Reservations-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-primary me-5">
                        <i class="ki-outline ki-check fs-2tx text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.attended') }}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">{{$attended_reservations ?? 0}}</span>
                        <span class="text-muted fs-8">{{ $from_date && $to_date ? trans('sw.selected_period') : trans('sw.today') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Attended Reservations-->

        <!--begin::Cancelled Reservations-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-danger me-5">
                        <i class="ki-outline ki-cross fs-2tx text-danger"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.cancelled') }}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">{{$cancelled_reservations ?? 0}}</span>
                        <span class="text-muted fs-8">{{ $from_date && $to_date ? trans('sw.selected_period') : trans('sw.today') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Cancelled Reservations-->
    </div>
    <!--end::Statistics-->

    <!--begin::Additional Statistics-->
    <div class="row g-5 g-xl-8 mt-5">
        <!--begin::New Members-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-success me-5">
                        <i class="ki-outline ki-user-plus fs-2tx text-success"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.new_members')}}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">{{$new_members_count}}</span>
                        <span class="text-muted fs-8">{{ $from_date && $to_date ? trans('sw.selected_period') : trans('sw.today') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::New Members-->

        <!--begin::New Daily Clients-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-warning me-5">
                        <i class="ki-outline ki-user-square fs-2tx text-warning"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.new_daily_clients')}}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">{{$new_non_members_count}}</span>
                        <span class="text-muted fs-8">{{ $from_date && $to_date ? trans('sw.selected_period') : trans('sw.today') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::New Daily Clients-->

        <!--begin::Attendance-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-info me-5">
                        <i class="ki-outline ki-entrance-right fs-2tx text-info"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.attendance')}}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">{{$attendance_count}}</span>
                        <span class="text-muted fs-8">{{ $from_date && $to_date ? trans('sw.selected_period') : trans('sw.today') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Attendance-->

        <!--begin::Expiring Soon-->
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-light-danger me-5">
                        <i class="ki-outline ki-timer fs-2tx text-danger"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-gray-700 fw-bold d-block fs-7 mb-1">{{ trans('sw.expiring_soon')}}</span>
                        <span class="text-gray-900 fw-bolder d-block fs-2x">{{$expiring_soon_count}}</span>
                        <span class="text-muted fs-8">{{ trans('sw.next_7_days')}}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Expiring Soon-->
    </div>
    <!--end::Additional Statistics-->

    <!--begin::Memberships Data & Logs-->
    <div class="row g-5 g-xl-8 mt-5">
        <!--begin::Memberships Data-->
        <div class="col-lg-6">
            <div class="card card-flush h-100 shadow-sm border-0">
                <!--begin::Card header-->
                <div class="card-header pt-7 pb-5">
                    <div class="card-title">
                        <h3 class="fw-bolder">{{ trans('sw.memberships_data')}}</h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Stats-->
                    <div class="row mb-7">
                        <div class="col-6">
                            <div class="d-flex align-items-center p-4 bg-light-success rounded">
                                <div class="symbol symbol-50px me-4">
                                    <div class="symbol-label bg-success">
                                        <i class="ki-outline ki-check fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-7 fw-semibold text-gray-600">{{ trans('sw.active')}}</span>
                                    <span class="fs-2hx fw-bolder text-success">{{$members_active_count}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center p-4 bg-light-danger rounded">
                                <div class="symbol symbol-50px me-4">
                                    <div class="symbol-label bg-danger">
                                        <i class="ki-outline ki-cross fs-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-7 fw-semibold text-gray-600">{{ trans('sw.expire')}}</span>
                                    <span class="fs-2hx fw-bolder text-danger">{{$members_deactive_count}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Stats-->
                    
                    <!--begin::Table-->
                    <div class="hover-scroll-overlay-y">
                        <table class="table table-hover table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                            <thead>
                                <tr class="fw-bolder text-muted bg-light">
                                    <th class="ps-4 rounded-start">{{ trans('sw.memberships')}}</th>
                                    <th>{{ trans('sw.price')}}</th>
                                    <th>{{ trans('sw.period')}}</th>
                                    <th class="text-end pe-4 rounded-end">{{ trans('sw.members')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subscriptions as $subscription)
                                <tr>
                                    <td class="ps-4">
                                        <a href="javascript:;" class="text-gray-800 fw-bold text-hover-primary d-block">{{$subscription->name}}</a>
                                        <span class="text-muted fs-8">{{\Carbon\Carbon::parse($subscription->created_at)->format('M d, Y')}}</span>
                                    </td>
                                    <td class="text-gray-700 fw-semibold">
                                        @if($subscription->price > 0)
                                            {{number_format($subscription->price, 0)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light-info badge-lg">{{$subscription->period}} {{ trans('sw.days')}}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="badge badge-primary badge-lg">{{$subscription->member_subscriptions_count}}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Memberships Data-->

        <!--begin::Logs-->
        <div class="col-lg-6">
            <div class="card card-flush h-100 shadow-sm border-0">
                <!--begin::Card header-->
                <div class="card-header pt-7 pb-5">
                    <div class="card-title">
                        <h3 class="fw-bolder">{{ trans('sw.logs')}}</h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Timeline-->
                    <div class="timeline hover-scroll-overlay-y" style="max-height: 450px;">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-5">
                            <div class="timeline-line w-40px"></div>
                            <div class="timeline-icon symbol symbol-circle symbol-30px">
                                @if(in_array($log->type, [37, 61]))
                                    <div class="symbol-label bg-success shadow-sm">
                                        <i class="ki-outline ki-barcode fs-2 text-white"></i>
                                    </div>
                                @elseif(($log->type > 22) && ($log->type < 49))
                                    <div class="symbol-label bg-danger shadow-sm">
                                        <i class="ki-outline ki-file fs-2 text-white"></i>
                                    </div>
                                @elseif($log->type < 23)
                                    <div class="symbol-label bg-info shadow-sm">
                                        <i class="ki-outline ki-user fs-2 text-white"></i>
                                    </div>
                                @else
                                    <div class="symbol-label bg-primary shadow-sm">
                                        <i class="ki-outline ki-check fs-2 text-white"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="timeline-content m-0 ps-3">
                                <div class="fs-6 fw-bold text-gray-800 mb-1">{{$log->notes}}</div>
                                <div class="fs-8 text-muted fw-semibold">
                                    <i class="ki-outline ki-time fs-7 me-1"></i>
                                    {{\Carbon\Carbon::parse($log->created_at)->diffForHumans()}}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <!--end::Timeline-->
                    
                    <!--begin::Footer-->
                    <div class="d-flex justify-content-center mt-7">
                        <a href="{{route('sw.listUserLog')}}" class="btn btn-light-primary">
                            <i class="ki-outline ki-eye fs-5 me-1"></i>
                            {{ trans('sw.all_logs')}}
                        </a>
                    </div>
                    <!--end::Footer-->
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Logs-->
    </div>
    <!--end::Memberships Data & Logs-->
    <!--begin::Chart Section-->
    <div class="row g-5 g-xl-8 mt-5">
        <div class="col-12">
            <div class="card card-flush shadow-sm border-0">
                <!--begin::Card header-->
                <div class="card-header pt-7 pb-5">
                    <div class="card-title">
                        <h3 class="fw-bolder">{{ trans('sw.memberships')}}</h3>
                    </div>
                    <div class="card-toolbar">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-light-success fs-7 fw-bold">
                                <i class="ki-outline ki-arrow-up fs-7 text-success"></i>
                                {{ trans('sw.memberships_new')}}
                            </span>
                            <span class="badge badge-light-danger fs-7 fw-bold">
                                <i class="ki-outline ki-arrow-down fs-7 text-danger"></i>
                                {{ trans('sw.memberships_expired')}}
                            </span>
                        </div>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div id="container" style="min-width: 310px; height: 450px; margin: 0 auto"></div>
                </div>
                <!--end::Card body-->
            </div>
        </div>
    </div>
    <!--end::Chart Section-->
 

@endsection
@section('scripts')
    <script src="https://code.highcharts.com/highcharts.js"></script>

    <script>
        $("#kt_daterangepicker_4").daterangepicker({
            autoUpdateInput: false,
            opens: '{{ $lang == "ar" ? "left" : "right" }}',
            ranges: {
                "{{ trans('sw.today') }}": [moment(), moment()],
                "{{ trans('sw.yesterday') }}": [moment().subtract(1, "days"), moment().subtract(1, "days")],
                "{{ trans('sw.last_7_days') }}": [moment().subtract(6, "days"), moment()],
                "{{ trans('sw.last_30_days') }}": [moment().subtract(29, "days"), moment()],
                "{{ trans('sw.this_month') }}": [moment().startOf("month"), moment().endOf("month")],
                "{{ trans('sw.last_month') }}": [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")]
            },
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' - ',
                applyLabel: '{{ trans('sw.apply') }}',
                cancelLabel: '{{ trans('sw.cancel') }}',
                fromLabel: '{{ trans('sw.from') }}',
                toLabel: '{{ trans('sw.to') }}',
                customRangeLabel: '{{ trans('sw.custom') }}',
                @if($lang == 'ar')
                daysOfWeek: ['{{ trans('sw.sun') }}', '{{ trans('sw.mon') }}', '{{ trans('sw.tue') }}', '{{ trans('sw.wed') }}', '{{ trans('sw.thurs') }}', '{{ trans('sw.fri') }}', '{{ trans('sw.sat') }}'],
                monthNames: ['{{ trans('sw.month_1') }}', '{{ trans('sw.month_2') }}', '{{ trans('sw.month_3') }}', '{{ trans('sw.month_4') }}', '{{ trans('sw.month_5') }}', '{{ trans('sw.month_6') }}', '{{ trans('sw.month_7') }}', '{{ trans('sw.month_8') }}', '{{ trans('sw.month_9') }}', '{{ trans('sw.month_10') }}', '{{ trans('sw.month_11') }}', '{{ trans('sw.month_12') }}'],
                @endif
                firstDay: {{ $lang == 'ar' ? 6 : 0 }}
            }
        });

        $('#kt_daterangepicker_4').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#kt_daterangepicker_4').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        @if(request('from_date') && request('to_date'))
        $('#kt_daterangepicker_4').val('{{ request('from_date') }} - {{ request('to_date') }}');
        $('#kt_daterangepicker_4').data('daterangepicker').setStartDate('{{ request('from_date') }}');
        $('#kt_daterangepicker_4').data('daterangepicker').setEndDate('{{ request('to_date') }}');
        @endif

        function applyFilter() {
            var inputVal = $("#kt_daterangepicker_4").val();
            var url = '{{ route('sw.statistics') }}';
            
            if (inputVal) {
                var dateRange = $("#kt_daterangepicker_4").data('daterangepicker');
                var from_date = dateRange.startDate.format('YYYY-MM-DD');
                var to_date = dateRange.endDate.format('YYYY-MM-DD');
                url += '?from_date=' + from_date + '&to_date=' + to_date;
            }
            
            window.location.href = url;
        }

        function resetFilter() {
            window.location.href = '{{ route('sw.statistics') }}';
        }

        $(function() {


            $('#container').highcharts({
                title: {
                    text: '{{ trans('sw.memberships')}}',
                    x: -20 //center
                },
                xAxis: {
                    reversed: true,
                    categories: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
                },
                yAxis: {
                    title: {
                        text: ' {{ trans('sw.members')}} '
                    },
                    opposite: true,
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle',
                    borderWidth: 0
                },
                series: [{
                    color: '#4caf50',
                    name: '{{ trans('sw.memberships_new')}}',
                    data: [{{$new_members}}]
                }, {
                    color: '#f44336',
                    name: '{{ trans('sw.memberships_expired')}}',
                    data: [{{$expired_members}}]
                }]
            });
        });

        function members_refresh(){
            $('#members_refresh').hide().after('<div class="col-md-12"><div class="loader"></div></div>');
            $.ajax({
                url: '{{route('sw.membersRefresh')}}',
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {},
                success: function (response) {
                    setTimeout(function () {
                        window.location.replace("{{asset(route('sw.statistics'))}}");
                    }, 500);
                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });
        }

    </script>
@endsection
