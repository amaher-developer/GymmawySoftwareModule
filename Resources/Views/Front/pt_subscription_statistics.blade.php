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

@section('list_title') {{ @$title }} @endsection

@section('list_add_button')
<div>
    <button class="btn btn-default btn-block rounded-3" id="members_refresh" onclick="members_refresh()">
        <i class="fa fa-refresh mx-1"></i>
        {{ trans('sw.members_refresh')}}
    </button>
</div>
@endsection

@section('styles')
    <style>
        .text-xxl-end {
            padding: 0 10px;
        }
        .left{
            float: left;
        }
        .right{
            float: right;
        }
    </style>
@endsection

@section('page_body')
    <!--begin::Container-->
    <div id="kt_content_container" class="container-xxl">
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
        <!--begin::Row-->
        <div class="row gx-5 gx-xl-10 mb-xl-10">
            <!--begin::Col-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-10">
                <!--begin::Card widget - Revenue-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center">
                                <!--begin::Currency-->
                                @if($pt_revenue > 0)
                                    <span class="fs-4 fw-semibold text-gray-500 me-1 align-self-start">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}</span>
                                @endif
                                <!--end::Currency-->
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">
                                    @if($pt_revenue > 0)
                                        {{ number_format($pt_revenue, 0) }}
                                    @else
                                        -
                                    @endif
                                </span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.pt_subscription_revenue')}}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex flex-column justify-content-end pe-0">
                        <!--begin::Stats-->
                        <div class="d-flex flex-column content-justify-center w-100">
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center mb-3">
                                <!--begin::Bullet-->
                                <div class="bullet w-8px h-6px rounded-2 bg-success me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.active_subscriptions')}}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $active_pt_subscriptions }}</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Stats-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
                
                <!--begin::Card widget - Active PT Members-->
                <div class="card card-flush h-md-50 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $active_pt_subscriptions }}</span>
                            <!--end::Amount-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.active_pt_subscriptions')}}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex align-items-end pt-0">
                        <!--begin::Progress-->
                        <div class="d-flex align-items-center flex-column mt-3 w-100">
                            <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                <span class="fw-bolder fs-6 text-gray-900">{{ trans('sw.total_pt_subscriptions')}}</span>
                                <span class="fw-bold fs-6 text-gray-500">{{ $total_pt_subscriptions > 0 ? round(($active_pt_subscriptions / $total_pt_subscriptions) * 100) : 0 }}%</span>
                            </div>
                            <div class="h-8px mx-3 w-100 bg-light-success rounded">
                                <div class="bg-success rounded h-8px" role="progressbar" style="width: {{ $total_pt_subscriptions > 0 ? round(($active_pt_subscriptions / $total_pt_subscriptions) * 100) : 0 }}%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <!--end::Progress-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
            </div>
            <!--end::Col-->
            
            <!--begin::Col-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-10">
                <!--begin::Card widget - Monthly Revenue-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center">
                                <!--begin::Currency-->
                                @if($monthly_pt_revenue > 0)
                                    <span class="fs-4 fw-semibold text-gray-500 me-1 align-self-start">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}</span>
                                @endif
                                <!--end::Currency-->
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">
                                    @if($monthly_pt_revenue > 0)
                                        {{ number_format($monthly_pt_revenue, 0) }}
                                    @else
                                        -
                                    @endif
                                </span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.monthly_revenue')}}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex flex-column justify-content-end pe-0">
                        <!--begin::Stats-->
                        <div class="d-flex flex-column content-justify-center w-100">
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <!--begin::Bullet-->
                                <div class="bullet w-8px h-6px rounded-2 bg-success me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.active')}}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $active_pt_subscriptions }}</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center my-3">
                                <!--begin::Bullet-->
                                <div class="bullet w-8px h-6px rounded-2 bg-warning me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.frozen')}}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $frozen_pt_subscriptions }}</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <!--begin::Bullet-->
                                <div class="bullet w-8px h-6px rounded-2 bg-danger me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.expire')}}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $expired_pt_subscriptions }}</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Stats-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
                
                <!--begin::Card widget - Total Trainers-->
                <div class="card card-flush h-md-50 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $total_trainers }}</span>
                            <!--end::Amount-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.total_trainers')}}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex flex-column justify-content-end pe-0">
                        <!--begin::Stats-->
                        <div class="d-flex flex-column content-justify-center w-100">
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center mb-3">
                                <!--begin::Bullet-->
                                <div class="bullet w-8px h-6px rounded-2 bg-primary me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.total_classes')}}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $total_pt_classes }}</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Stats-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
            </div>
            <!--end::Col-->
            
            <!--begin::Col-->
            <div class="col-lg-12 col-xl-12 col-xxl-6 mb-5 mb-xl-0">
                <!--begin::Chart widget-->
                <div class="card card-flush overflow-hidden h-md-100">
                    <!--begin::Header-->
                    <div class="card-header py-5">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">{{ trans('sw.pt_subscription_trends')}}</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ trans('sw.monthly_pt_activity')}}</span>
                        </h3>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex justify-content-between flex-column pb-1 px-0">
                        <!--begin::Statistics-->
                        <div class="px-9 mb-5">
                            <!--begin::Statistics-->
                            <div class="d-flex mb-2">
                                <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2">{{ $total_pt_subscriptions }}</span>
                            </div>
                            <!--end::Statistics-->
                            <!--begin::Description-->
                            <span class="fs-6 fw-semibold text-gray-500">{{ trans('sw.total_pt_subscriptions')}}</span>
                            <!--end::Description-->
                        </div>
                        <!--end::Statistics-->
                        <!--begin::Chart-->
                        <div id="kt_pt_subscription_trends_chart" class="min-h-auto ps-4 pe-6" style="height: 300px"></div>
                        <!--end::Chart-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Chart widget-->
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        
        <!--begin::Row-->
        <div class="row gy-5 g-xl-10">
            <!--begin::Col-->
            <div class="col-xl-6 mb-xl-10">
                <!--begin::Table widget - Popular PT Subscriptions-->
                <div class="card h-md-100">
                    <!--begin::Header-->
                    <div class="card-header align-items-center border-0">
                        <!--begin::Title-->
                        <h3 class="fw-bold text-gray-900 m-0">{{ trans('sw.popular_pt_subscriptions')}}</h3>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-2">
                        <!--begin::Scroll-->
                        <div class="hover-scroll-overlay-y pe-6 me-n6" style="height: 415px">
                            <!--begin::Table-->
                            <table class="table table-row-dashed align-middle gs-0 gy-4 my-0">
                                <!--begin::Table head-->
                                <thead>
                                    <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                        <th class="min-w-125px">{{ trans('sw.pt_subscription')}}</th>
                                        <th class="text-end min-w-100px">{{ trans('sw.classes')}}</th>
                                        <th class="pe-0 text-end min-w-100px">{{ trans('sw.price')}}</th>
                                        <th class="pe-0 text-end min-w-100px">{{ trans('sw.members')}}</th>
                                        <th class="pe-0 text-end min-w-100px">{{ trans('sw.revenue')}}</th>
                                    </tr>
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody>
                                    @foreach($popular_pt_subscriptions as $subscription)
                                    <tr>
                                        <td class="ps-0">
                                            <a href="javascript:;" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6 text-start pe-0">{{ $subscription->name }}</a>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-bold d-block fs-6 ps-0 text-end">{{ $subscription->classes }}</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-800 fw-bold d-block fs-6">
                                                @if($subscription->price > 0)
                                                    {{number_format($subscription->price, 2)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="badge py-3 px-4 fs-7 badge-light-primary">{{ $subscription->pt_members_count }}</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-800 fw-bold d-block fs-6">
                                                @if($subscription->revenue > 0)
                                                    {{number_format($subscription->revenue, 2)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <!--end::Table body-->
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Scroll-->
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Table widget-->
            </div>
            <!--end::Col-->
            
            <!--begin::Col-->
            <div class="col-xl-6 mb-5 mb-xl-10">
                <!--begin::Table widget - Top Trainers-->
                <div class="card h-md-100">
                    <!--begin::Header-->
                    <div class="card-header align-items-center border-0">
                        <!--begin::Title-->
                        <h3 class="fw-bold text-gray-900 m-0">{{ trans('sw.top_trainers')}}</h3>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-2">
                        <!--begin::Scroll-->
                        <div class="hover-scroll-overlay-y pe-6 me-n6" style="height: 415px">
                            <!--begin::Table-->
                            <table class="table table-row-dashed align-middle gs-0 gy-4 my-0">
                                <!--begin::Table head-->
                                <thead>
                                    <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                        <th class="ps-0 w-50px"></th>
                                        <th class="min-w-125px">{{ trans('sw.trainer')}}</th>
                                        <th class="text-end min-w-100px">{{ trans('sw.members')}}</th>
                                        <th class="pe-0 text-end min-w-100px">{{ trans('sw.classes')}}</th>
                                        <th class="pe-0 text-end min-w-100px">{{ trans('sw.revenue')}}</th>
                                    </tr>
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody>
                                    @foreach($top_trainers as $trainer)
                                    <tr>
                                        <td>
                                            <img src="{{ $trainer->image }}" class="w-50px rounded-circle ms-n1" alt="" />
                                        </td>
                                        <td class="ps-0">
                                            <a href="javascript:;" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6 text-start pe-0">{{ $trainer->name }}</a>
                                            <span class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0">{{ trans('sw.trainer')}}</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-bold d-block fs-6 ps-0 text-end">{{ $trainer->members_count }}</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-800 fw-bold d-block fs-6">{{ $trainer->classes_count }}</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-800 fw-bold d-block fs-6">
                                                @if($trainer->revenue > 0)
                                                    {{number_format($trainer->revenue, 2)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <!--end::Table body-->
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Scroll-->
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Table widget-->
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
    </div>
    <!--end::Container-->
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
            var url = '{{ route('sw.ptSubscriptionStatistics') }}';
            
            if (inputVal) {
                var dateRange = $("#kt_daterangepicker_4").data('daterangepicker');
                var from_date = dateRange.startDate.format('YYYY-MM-DD');
                var to_date = dateRange.endDate.format('YYYY-MM-DD');
                url += '?from_date=' + from_date + '&to_date=' + to_date;
            }
            
            window.location.href = url;
        }

        function resetFilter() {
            window.location.href = '{{ route('sw.ptSubscriptionStatistics') }}';
        }

        function members_refresh(){
            $('#members_refresh').hide().after('<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
            $.ajax({
                url: '{{route('sw.ptSubscriptionStatisticsRefresh')}}',
                cache: false,
                type: 'GET',
                dataType: 'json',
                data: {},
                success: function (response) {
                    setTimeout(function () {
                        window.location.replace("{{route('sw.ptSubscriptionStatistics')}}");
                    }, 500);
                },
                error: function (request, error) {
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });
        }

        $(function() {
            // PT Subscription Trends Chart
            $('#kt_pt_subscription_trends_chart').highcharts({
                chart: { type: 'area', height: 300, spacingTop: 10 },
                title: null,
                xAxis: {
                    categories: ['{{ trans('sw.jan')}}', '{{ trans('sw.feb')}}', '{{ trans('sw.mar')}}', '{{ trans('sw.apr')}}', '{{ trans('sw.may')}}', '{{ trans('sw.jun')}}', '{{ trans('sw.jul')}}', '{{ trans('sw.aug')}}', '{{ trans('sw.sep')}}', '{{ trans('sw.oct')}}', '{{ trans('sw.nov')}}', '{{ trans('sw.dec')}}']
                },
                yAxis: { title: null },
                legend: { align: 'left', verticalAlign: 'top', y: 0 },
                series: [{
                    name: '{{ trans('sw.new_pt_subscriptions')}}',
                    data: [{{ $new_pt_subscriptions_chart }}],
                    color: '#50CD89'
                }, {
                    name: '{{ trans('sw.expired_pt_subscriptions')}}',
                    data: [{{ $expired_pt_subscriptions_chart }}],
                    color: '#F1416C'
                }, {
                    name: '{{ trans('sw.frozen_pt_subscriptions')}}',
                    data: [{{ $frozen_pt_subscriptions_chart }}],
                    color: '#FFC700'
                }],
                credits: { enabled: false }
            });
        });
    </script>
@endsection
