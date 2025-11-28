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
            float: left;
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
                                @if($total_revenue > 0)
                                    <span class="fs-4 fw-semibold text-gray-500 me-1 align-self-start">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}</span>
                                @endif
                                <!--end::Currency-->
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">
                                    @if($total_revenue > 0)
                                        {{ number_format($total_revenue, 0) }}
                                    @else
                                        -
                                    @endif
                                </span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.non_member_revenue')}}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex flex-column justify-content-end ">
                        <!--begin::Stats-->
                        <div class="d-flex flex-column content-justify-center w-100">
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center mb-3">
                                <!--begin::Bullet-->
                                <div class="bullet w-8px h-6px rounded-2 bg-success me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.monthly_non_member_revenue')}}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">
                                    @if($monthly_revenue > 0)
                                        {{number_format($monthly_revenue, 0)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
                                    @else
                                        -
                                    @endif
                                </div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Stats-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
                
                <!--begin::Card widget - Total Non-Members-->
                <div class="card card-flush h-md-50 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $total_non_members }}</span>
                            <!--end::Amount-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.total_non_members')}}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex flex-column justify-content-end ">
                        <!--begin::Progress-->
                        <div class="d-flex align-items-center flex-column mt-3 w-100">
                            <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                <span class="fw-semibold fs-6 text-gray-500">{{ trans('sw.total_sessions')}}</span>
                                <span class="fw-bold fs-6">{{ $total_sessions }}</span>
                            </div>
                            <div class="h-5px mx-3 w-100 bg-light mb-3">
                                <div class="bg-success rounded h-5px" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
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
                <!--begin::Card widget - Active Sessions-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $active_sessions }}</span>
                            <!--end::Amount-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.active_sessions')}}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex align-items-end px-0 pb-0">
                        <!--begin::Chart-->
                        <div id="kt_card_widget_6_chart" class="w-100" style="height: 80px"></div>
                        <!--end::Chart-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
                
                <!--begin::Card widget - Expired Sessions-->
                <div class="card card-flush h-md-50 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $expired_sessions }}</span>
                            <!--end::Amount-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.expired_sessions')}}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex align-items-end px-0 pb-0">
                        <!--begin::Chart-->
                        <div id="kt_card_widget_7_chart" class="w-100" style="height: 80px"></div>
                        <!--end::Chart-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
            </div>
            <!--end::Col-->

            <!--begin::Col - Reservations Statistics-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-10">
                <!--begin::Card widget - Total Reservations-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $total_non_member_reservations ?? 0 }}</span>
                            <!--end::Amount-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.total_reservations') }}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex flex-column justify-content-end">
                        <!--begin::Stats-->
                        <div class="d-flex flex-column content-justify-center w-100">
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center mb-2">
                                <div class="bullet w-8px h-6px rounded-2 bg-success me-3"></div>
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.confirmed') }}</div>
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $non_member_confirmed_reservations ?? 0 }}</div>
                            </div>
                            <!--end::Label-->
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center mb-2">
                                <div class="bullet w-8px h-6px rounded-2 bg-primary me-3"></div>
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.attended') }}</div>
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $non_member_attended_reservations ?? 0 }}</div>
                            </div>
                            <!--end::Label-->
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <div class="bullet w-8px h-6px rounded-2 bg-danger me-3"></div>
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.cancelled') }}</div>
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $non_member_cancelled_reservations ?? 0 }}</div>
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Stats-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
            </div>
            <!--end::Col - Reservations Statistics-->

            <!--begin::Col-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-10">
                <!--begin::Card widget - Attendance Rate-->
                <div class="card card-flush h-md-100 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $attendance_rate }}%</span>
                            <!--end::Amount-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.attendance_rate')}}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex flex-column justify-content-end ">
                        <!--begin::Progress-->
                        <div class="d-flex align-items-center flex-column mt-3 w-100">
                            <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                <span class="fw-semibold fs-6 text-gray-500">{{ trans('sw.sessions_attended')}}</span>
                                <span class="fw-bold fs-6">{{ $total_sessions > 0 ? round(($attendance_rate / 100) * $total_sessions) : 0 }}</span>
                            </div>
                            <div class="h-8px mx-3 w-100 bg-light-success mb-3">
                                <div class="bg-success rounded h-8px" role="progressbar" style="width: {{ $attendance_rate }}%;" aria-valuenow="{{ $attendance_rate }}" aria-valuemin="0" aria-valuemax="100"></div>
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
                <!--begin::Tables Widget - Popular Activities-->
                <div class="card card-flush h-md-100 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-7">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">{{ trans('sw.popular_activities')}}</span>
                        </h3>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-5">
                        <!--begin::Scroll-->
                        <div class="hover-scroll-overlay-y pe-6 me-n6" style="height: 250px">
                            @foreach($popular_activities as $activity)
                            <!--begin::Item-->
                            <div class="border border-dashed border-gray-300 rounded px-4 py-3 mb-3">
                                <!--begin::Info-->
                                <div class="d-flex flex-stack">
                                    <!--begin::Name-->
                                    <div class="d-flex flex-column">
                                        <div class="fs-6 fw-bold text-gray-800">{{ $activity->name }}</div>
                                        <div class="fs-7 fw-semibold text-gray-500">{{ trans('sw.sessions')}}: {{ $activity->sessions_count }}</div>
                                    </div>
                                    <!--end::Name-->
                                    <!--begin::Stats-->
                                    <div class="text-end">
                                        <div class="fs-6 fw-bold text-success">{{ $activity->attendance_rate }}%</div>
                                        <div class="fs-7 text-gray-500">{{ trans('sw.attendance_rate')}}</div>
                                    </div>
                                    <!--end::Stats-->
                                </div>
                                <!--end::Info-->
                            </div>
                            <!--end::Item-->
                            @endforeach
                        </div>
                        <!--end::Scroll-->
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Tables Widget-->
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->

        <!--begin::Row-->
        <div class="row gy-5 g-xl-10">
            <!--begin::Col-->
            <div class="col-xl-6 mb-xl-10">
                <!--begin::Chart Widget - Monthly Activity-->
                <div class="card card-flush h-xl-100">
                    <!--begin::Header-->
                    <div class="card-header pt-7">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">{{ trans('sw.monthly_non_member_activity')}}</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ trans('sw.non_member_trends')}}</span>
                        </h3>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-5">
                        <!--begin::Chart container-->
                        <div id="kt_non_member_activity_chart" class="w-100" style="height: 350px"></div>
                        <!--end::Chart container-->
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Chart Widget-->
            </div>
            <!--end::Col-->

            <!--begin::Col-->
            <div class="col-xl-6 mb-xl-10">
                <!--begin::Table Widget - Recent Non-Members-->
                <div class="card card-flush h-xl-100">
                    <!--begin::Header-->
                    <div class="card-header pt-7">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">{{ trans('sw.recent_non_members')}}</span>
                        </h3>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-2">
                        <!--begin::Scroll-->
                        <div class="hover-scroll-overlay-y pe-6 me-n6" style="height: 350px">
                            <!--begin::Table-->
                            <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                <!--begin::Table head-->
                                <thead>
                                    <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                        <th class="p-0 pb-3 min-w-150px text-start">{{ trans('sw.name')}}</th>
                                        <th class="p-0 pb-3 min-w-100px text-end">{{ trans('sw.phone')}}</th>
                                        <th class="p-0 pb-3 min-w-100px text-end">{{ trans('sw.sessions')}}</th>
                                        <th class="p-0 pb-3 min-w-100px text-end">{{ trans('sw.attended')}}</th>
                                    </tr>
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody>
                                    @foreach($recent_non_members as $member)
                                    <tr>
                                        <td class="text-start">
                                            <span class="text-gray-800 fw-bold text-hover-primary fs-6">{{ $member->name }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-600 fw-semibold fs-6">{{ $member->phone }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge badge-light-primary fs-7 fw-bold">{{ $member->sessions_count }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge badge-light-success fs-7 fw-bold">{{ $member->attended_count }}</span>
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
                <!--end::Table Widget-->
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
        // Wait for moment.js and daterangepicker to be loaded
        function waitForDaterangepicker(maxAttempts, attempts) {
            maxAttempts = maxAttempts || 50;
            attempts = attempts || 0;

            if (typeof moment === 'undefined') {
                if (attempts < maxAttempts) {
                    setTimeout(function() { waitForDaterangepicker(maxAttempts, attempts + 1); }, 100);
                } else {
                    console.error('moment.js failed to load after ' + maxAttempts + ' attempts');
                }
                return;
            }

            if (typeof $ === 'undefined' || typeof $.fn === 'undefined') {
                if (attempts < maxAttempts) {
                    setTimeout(function() { waitForDaterangepicker(maxAttempts, attempts + 1); }, 100);
                } else {
                    console.error('jQuery failed to load after ' + maxAttempts + ' attempts');
                }
                return;
            }

            if (typeof $.fn.daterangepicker === 'undefined') {
                if (attempts < maxAttempts) {
                    setTimeout(function() { waitForDaterangepicker(maxAttempts, attempts + 1); }, 100);
                } else {
                    console.error('daterangepicker failed to load after ' + maxAttempts + ' attempts');
                }
                return;
            }

            initDateRangePicker();
        }

        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function() {
                waitForDaterangepicker();
            });
        } else {
            window.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    if (typeof jQuery !== 'undefined') {
                        jQuery(document).ready(function() {
                            waitForDaterangepicker();
                        });
                    } else {
                        waitForDaterangepicker();
                    }
                }, 100);
            });
        }

        function initDateRangePicker() {
            if ($("#kt_daterangepicker_4").length === 0) {
                console.error('Element #kt_daterangepicker_4 not found!');
                return;
            }

            if ($("#kt_daterangepicker_4").data('daterangepicker')) {
                console.log('daterangepicker already initialized');
                return;
            }

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
            var dateRangePicker = $('#kt_daterangepicker_4').data('daterangepicker');
            if (dateRangePicker) {
                dateRangePicker.setStartDate('{{ request('from_date') }}');
                dateRangePicker.setEndDate('{{ request('to_date') }}');
            }
            @endif
        }

        function applyFilter() {
            var inputVal = $("#kt_daterangepicker_4").val();
            var url = '{{ route('sw.nonMemberStatistics') }}';
            
            if (inputVal) {
                var dateRange = $("#kt_daterangepicker_4").data('daterangepicker');
                var from_date = dateRange.startDate.format('YYYY-MM-DD');
                var to_date = dateRange.endDate.format('YYYY-MM-DD');
                url += '?from_date=' + from_date + '&to_date=' + to_date;
            }
            
            window.location.href = url;
        }

    function resetFilter() {
        window.location.href = '{{ route('sw.nonMemberStatistics') }}';
    }

    function members_refresh(){
        $('#members_refresh').hide().after('<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
        $.ajax({
            url: '{{route('sw.nonMemberStatisticsRefresh')}}',
            cache: false,
            type: 'GET',
            dataType: 'json',
            data: {},
            success: function (response) {
                setTimeout(function () {
                    window.location.replace("{{route('sw.nonMemberStatistics')}}");
                }, 500);
            },
            error: function (request, error) {
                console.error("Request: " + JSON.stringify(request));
                console.error("Error: " + JSON.stringify(error));
            }
        });
    }

    var KTChartsWidget6 = function() {
        var e = {
                self: null,
                rendered: !1
            },
            t = function(e) {
                var t = document.getElementById("kt_card_widget_6_chart");
                if (t) {
                    var a = parseInt(KTUtil.css(t, "height")),
                        l = KTUtil.getCssVariableValue("--bs-gray-800"),
                        r = KTUtil.getCssVariableValue("--bs-gray-300"),
                        o = {
                            series: [{
                                name: "{{ trans('sw.active_sessions') }}",
                                data: [{{ $sessions_chart }}]
                            }],
                            chart: {
                                fontFamily: "inherit",
                                type: "area",
                                height: a,
                                toolbar: {
                                    show: !1
                                },
                                zoom: {
                                    enabled: !1
                                },
                                sparkline: {
                                    enabled: !0
                                }
                            },
                            plotOptions: {},
                            legend: {
                                show: !1
                            },
                            dataLabels: {
                                enabled: !1
                            },
                            fill: {
                                type: "solid",
                                opacity: 1
                            },
                            stroke: {
                                curve: "smooth",
                                show: !0,
                                width: 3,
                                colors: [l]
                            },
                            xaxis: {
                                categories: ["{{ trans('sw.jan') }}", "{{ trans('sw.feb') }}", "{{ trans('sw.mar') }}", "{{ trans('sw.apr') }}", "{{ trans('sw.may') }}", "{{ trans('sw.jun') }}", "{{ trans('sw.jul') }}", "{{ trans('sw.aug') }}", "{{ trans('sw.sep') }}", "{{ trans('sw.oct') }}", "{{ trans('sw.nov') }}", "{{ trans('sw.dec') }}"],
                                axisBorder: {
                                    show: !1
                                },
                                axisTicks: {
                                    show: !1
                                },
                                labels: {
                                    show: !1,
                                    style: {
                                        colors: r,
                                        fontSize: "12px"
                                    }
                                },
                                crosshairs: {
                                    show: !1,
                                    position: "front",
                                    stroke: {
                                        color: r,
                                        width: 1,
                                        dashArray: 3
                                    }
                                },
                                tooltip: {
                                    enabled: !0,
                                    formatter: void 0,
                                    offsetY: 0,
                                    style: {
                                        fontSize: "12px"
                                    }
                                }
                            },
                            yaxis: {
                                min: 0,
                                max: Math.max({{ $sessions_chart }}) + 10,
                                labels: {
                                    show: !1,
                                    style: {
                                        colors: r,
                                        fontSize: "12px"
                                    }
                                }
                            },
                            states: {
                                normal: {
                                    filter: {
                                        type: "none",
                                        value: 0
                                    }
                                },
                                hover: {
                                    filter: {
                                        type: "none",
                                        value: 0
                                    }
                                },
                                active: {
                                    allowMultipleDataPointsSelection: !1,
                                    filter: {
                                        type: "none",
                                        value: 0
                                    }
                                }
                            },
                            tooltip: {
                                style: {
                                    fontSize: "12px"
                                },
                                y: {
                                    formatter: function(e) {
                                        return e + " {{ trans('sw.sessions') }}"
                                    }
                                }
                            },
                            colors: ["#20c997"],
                            markers: {
                                colors: [l],
                                strokeColor: [l],
                                strokeWidth: 3
                            }
                        };
                    e.self = new ApexCharts(t, o), setTimeout((function() {
                        e.self.render(), e.rendered = !0
                    }), 200)
                }
            },
            a = function(e) {
                var t = document.getElementById("kt_card_widget_7_chart");
                if (t) {
                    var a = parseInt(KTUtil.css(t, "height")),
                        l = KTUtil.getCssVariableValue("--bs-danger"),
                        r = KTUtil.getCssVariableValue("--bs-gray-300"),
                        o = {
                            series: [{
                                name: "{{ trans('sw.expired_sessions') }}",
                                data: [{{ $sessions_chart }}]
                            }],
                            chart: {
                                fontFamily: "inherit",
                                type: "area",
                                height: a,
                                toolbar: {
                                    show: !1
                                },
                                zoom: {
                                    enabled: !1
                                },
                                sparkline: {
                                    enabled: !0
                                }
                            },
                            plotOptions: {},
                            legend: {
                                show: !1
                            },
                            dataLabels: {
                                enabled: !1
                            },
                            fill: {
                                type: "solid",
                                opacity: 1
                            },
                            stroke: {
                                curve: "smooth",
                                show: !0,
                                width: 3,
                                colors: [l]
                            },
                            xaxis: {
                                categories: ["{{ trans('sw.jan') }}", "{{ trans('sw.feb') }}", "{{ trans('sw.mar') }}", "{{ trans('sw.apr') }}", "{{ trans('sw.may') }}", "{{ trans('sw.jun') }}", "{{ trans('sw.jul') }}", "{{ trans('sw.aug') }}", "{{ trans('sw.sep') }}", "{{ trans('sw.oct') }}", "{{ trans('sw.nov') }}", "{{ trans('sw.dec') }}"],
                                axisBorder: {
                                    show: !1
                                },
                                axisTicks: {
                                    show: !1
                                },
                                labels: {
                                    show: !1,
                                    style: {
                                        colors: r,
                                        fontSize: "12px"
                                    }
                                },
                                crosshairs: {
                                    show: !1,
                                    position: "front",
                                    stroke: {
                                        color: r,
                                        width: 1,
                                        dashArray: 3
                                    }
                                },
                                tooltip: {
                                    enabled: !0,
                                    formatter: void 0,
                                    offsetY: 0,
                                    style: {
                                        fontSize: "12px"
                                    }
                                }
                            },
                            yaxis: {
                                min: 0,
                                max: Math.max({{ $sessions_chart }}) + 10,
                                labels: {
                                    show: !1,
                                    style: {
                                        colors: r,
                                        fontSize: "12px"
                                    }
                                }
                            },
                            states: {
                                normal: {
                                    filter: {
                                        type: "none",
                                        value: 0
                                    }
                                },
                                hover: {
                                    filter: {
                                        type: "none",
                                        value: 0
                                    }
                                },
                                active: {
                                    allowMultipleDataPointsSelection: !1,
                                    filter: {
                                        type: "none",
                                        value: 0
                                    }
                                }
                            },
                            tooltip: {
                                style: {
                                    fontSize: "12px"
                                },
                                y: {
                                    formatter: function(e) {
                                        return e + " {{ trans('sw.sessions') }}"
                                    }
                                }
                            },
                            colors: ["#f1416c"],
                            markers: {
                                colors: [l],
                                strokeColor: [l],
                                strokeWidth: 3
                            }
                        };
                    e.self = new ApexCharts(t, o), setTimeout((function() {
                        e.self.render(), e.rendered = !0
                    }), 200)
                }
            };
        return {
            init: function() {
                t(e), a({
                    self: null,
                    rendered: !1
                })
            }
        }
    }();

    // Monthly Non-Member Activity Chart
    var KTNonMemberActivityChart = function() {
        var chart = {
            self: null,
            rendered: false
        };

        var initChart = function() {
            var element = document.getElementById("kt_non_member_activity_chart");
            if (!element) {
                return;
            }

            var height = parseInt(KTUtil.css(element, 'height'));
            var labelColor = KTUtil.getCssVariableValue('--bs-gray-500');
            var borderColor = KTUtil.getCssVariableValue('--bs-gray-200');
            
            var options = {
                series: [{
                    name: '{{ trans('sw.new_non_members') }}',
                    data: [{{ $new_non_members_chart }}]
                }, {
                    name: '{{ trans('sw.sessions_booked') }}',
                    data: [{{ $sessions_chart }}]
                }, {
                    name: '{{ trans('sw.sessions_attended') }}',
                    data: [{{ $attendance_chart }}]
                }, {
                    name: '{{ trans('sw.reservations') }}',
                    data: [{{ $reservations_chart ?? '0,0,0,0,0,0,0,0,0,0,0,0' }}]
                }],
                chart: {
                    fontFamily: 'inherit',
                    type: 'area',
                    height: height,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {},
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'left',
                    labels: {
                        colors: labelColor
                    }
                },
                dataLabels: {
                    enabled: false
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0,
                        stops: [0, 80, 100]
                    }
                },
                stroke: {
                    curve: 'smooth',
                    show: true,
                    width: 3
                },
                xaxis: {
                    categories: ['{{ trans('sw.jan') }}', '{{ trans('sw.feb') }}', '{{ trans('sw.mar') }}', '{{ trans('sw.apr') }}', '{{ trans('sw.may') }}', '{{ trans('sw.jun') }}', '{{ trans('sw.jul') }}', '{{ trans('sw.aug') }}', '{{ trans('sw.sep') }}', '{{ trans('sw.oct') }}', '{{ trans('sw.nov') }}', '{{ trans('sw.dec') }}'],
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                            colors: labelColor,
                            fontSize: '12px'
                        }
                    },
                    crosshairs: {
                        position: 'front',
                        stroke: {
                            color: borderColor,
                            width: 1,
                            dashArray: 3
                        }
                    },
                    tooltip: {
                        enabled: true,
                        formatter: undefined,
                        offsetY: 0,
                        style: {
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: labelColor,
                            fontSize: '12px'
                        }
                    }
                },
                states: {
                    normal: {
                        filter: {
                            type: 'none',
                            value: 0
                        }
                    },
                    hover: {
                        filter: {
                            type: 'none',
                            value: 0
                        }
                    },
                    active: {
                        allowMultipleDataPointsSelection: false,
                        filter: {
                            type: 'none',
                            value: 0
                        }
                    }
                },
                tooltip: {
                    style: {
                        fontSize: '12px'
                    }
                },
                colors: ['#3F4254', '#009ef7', '#20c997'],
                grid: {
                    borderColor: borderColor,
                    strokeDashArray: 4,
                    yaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                markers: {
                    strokeWidth: 3
                }
            };

            chart.self = new ApexCharts(element, options);
            
            setTimeout(function() {
                chart.self.render();
                chart.rendered = true;
            }, 200);
        }

        return {
            init: function() {
                initChart();
            }
        }
    }();

    KTUtil.onDOMContentLoaded(function() {
        KTChartsWidget6.init();
        KTNonMemberActivityChart.init();
    });
</script>
@endsection



