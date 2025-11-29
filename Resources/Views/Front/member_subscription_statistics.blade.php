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
                                @if($subscription_revenue > 0)
                                    <span class="fs-4 fw-semibold text-gray-500 me-1 align-self-start">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}</span>
                                @endif
                                <!--end::Currency-->
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">
                                    @if($subscription_revenue > 0)
                                        {{ number_format($subscription_revenue, 0) }}
                                    @else
                                        -
                                    @endif
                                </span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.subscription_revenue')}}</span>
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
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $active_subscriptions }}</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Stats-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
                
                <!--begin::Card widget - Active Members-->
                <div class="card card-flush h-md-50 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $active_subscriptions }}</span>
                            <!--end::Amount-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.active_subscriptions')}}</span>
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
                                <span class="fw-bolder fs-6 text-gray-900">{{ trans('sw.total_subscriptions')}}</span>
                                <span class="fw-bold fs-6 text-gray-500">{{ $total_subscriptions > 0 ? round(($active_subscriptions / $total_subscriptions) * 100) : 0 }}%</span>
                            </div>
                            <div class="h-8px mx-3 w-100 bg-light-success rounded">
                                <div class="bg-success rounded h-8px" role="progressbar" style="width: {{ $total_subscriptions > 0 ? round(($active_subscriptions / $total_subscriptions) * 100) : 0 }}%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
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
                                @if($monthly_revenue > 0)
                                    <span class="fs-4 fw-semibold text-gray-500 me-1 align-self-start">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}</span>
                                @endif
                                <!--end::Currency-->
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">
                                    @if($monthly_revenue > 0)
                                        {{ number_format($monthly_revenue, 0) }}
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
                            <div class="d-flex fs-6 fw-semibold align-items-center mb-3">
                                <!--begin::Bullet-->
                                <div class="bullet w-8px h-6px rounded-2 bg-success me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.subscription_revenue')}}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">
                                    @if($subscription_revenue > 0)
                                        {{number_format($subscription_revenue, 0)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
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
                
                <!--begin::Card widget - New This Month-->
                <div class="card card-flush h-md-50 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $new_this_month }}</span>
                            <!--end::Amount-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.new_this_month')}}</span>
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
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $active_subscriptions }}</div>
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
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $frozen_subscriptions }}</div>
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
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $expired_subscriptions }}</div>
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
                            <span class="card-label fw-bold text-gray-900">{{ trans('sw.subscription_trends')}}</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ trans('sw.monthly_subscription_activity')}}</span>
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
                                <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2">{{ $total_subscriptions }}</span>
                            </div>
                            <!--end::Statistics-->
                            <!--begin::Description-->
                            <span class="fs-6 fw-semibold text-gray-500">{{ trans('sw.total_subscriptions')}}</span>
                            <!--end::Description-->
                        </div>
                        <!--end::Statistics-->
                        <!--begin::Chart-->
                        <div id="kt_subscription_trends_chart" class="min-h-auto ps-4 pe-6" style="height: 300px"></div>
                        <!--end::Chart-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Chart widget-->
            </div>
            <!--end::Col-->

            <!--begin::Col - Member Reservations Statistics-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-10">
                <!--begin::Card widget - Total Reservations-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $total_member_reservations ?? 0 }}</span>
                            <!--end::Amount-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.member_reservations') }}</span>
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
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $member_confirmed_reservations ?? 0 }}</div>
                            </div>
                            <!--end::Label-->
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center mb-2">
                                <div class="bullet w-8px h-6px rounded-2 bg-primary me-3"></div>
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.attended') }}</div>
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $member_attended_reservations ?? 0 }}</div>
                            </div>
                            <!--end::Label-->
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center mb-2">
                                <div class="bullet w-8px h-6px rounded-2 bg-warning me-3"></div>
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.missed') }}</div>
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $member_missed_reservations ?? 0 }}</div>
                            </div>
                            <!--end::Label-->
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <div class="bullet w-8px h-6px rounded-2 bg-danger me-3"></div>
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.cancelled') }}</div>
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $member_cancelled_reservations ?? 0 }}</div>
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Stats-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
                
                <!--begin::Card widget - Reservations Chart-->
                <div class="card card-flush h-md-50 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.reservations_trend') }}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex align-items-end px-0 pb-0">
                        <!--begin::Chart-->
                        <div id="kt_member_reservations_chart" class="w-100" style="height: 80px"></div>
                        <!--end::Chart-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
            </div>
            <!--end::Col - Member Reservations Statistics-->
        </div>
        <!--end::Row-->
        
        <!--begin::Row-->
        <div class="row gy-5 g-xl-10">
            <!--begin::Col-->
            <div class="col-xl-12 mb-5 mb-xl-10">
                <!--begin::Table widget-->
                <div class="card card-flush h-xl-100">
                    <!--begin::Header-->
                    <div class="card-header pt-7">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">{{ trans('sw.popular_subscriptions')}}</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ trans('sw.top_performing_memberships')}}</span>
                        </h3>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-2">
                        <!--begin::Responsive Table-->
                        <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                            <table class="table align-middle table-row-dashed fs-6 gy-3">
                                <!--begin::Table head-->
                                <thead>
                                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-150px">{{ trans('sw.membership')}}</th>
                                        <th class="text-end min-w-100px">{{ trans('sw.price')}}</th>
                                        <th class="text-end min-w-100px">{{ trans('sw.period')}}</th>
                                        <th class="text-end min-w-100px">{{ trans('sw.members')}}</th>
                                        <th class="text-end min-w-100px">{{ trans('sw.revenue')}}</th>
                                    </tr>
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody class="fw-bold text-gray-600">
                                    @foreach($popular_subscriptions as $subscription)
                                    <tr>
                                        <td>
                                            <a href="javascript:;" class="text-gray-900 text-hover-primary">{{ $subscription->name }}</a>
                                        </td>
                                        <td class="text-end">
                                            @if($subscription->price > 0)
                                                {{number_format($subscription->price, 2)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end">{{ $subscription->period }} {{ trans('sw.days')}}</td>
                                        <td class="text-end">
                                            <span class="badge py-3 px-4 fs-7 badge-light-primary">{{ $subscription->member_subscriptions_count }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-900 fw-bolder">
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
                        </div>
                        <!--end::Responsive Table-->
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
            var url = '{{ route('sw.memberSubscriptionStatistics') }}';
            
            if (inputVal) {
                var dateRange = $("#kt_daterangepicker_4").data('daterangepicker');
                var from_date = dateRange.startDate.format('YYYY-MM-DD');
                var to_date = dateRange.endDate.format('YYYY-MM-DD');
                url += '?from_date=' + from_date + '&to_date=' + to_date;
            }
            
            window.location.href = url;
        }

        function resetFilter() {
            window.location.href = '{{ route('sw.memberSubscriptionStatistics') }}';
        }

        function members_refresh(){
            $('#members_refresh').hide().after('<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
            $.ajax({
                url: '{{route('sw.subscriptionStatisticsRefresh')}}',
                cache: false,
                type: 'GET',
                dataType: 'json',
                data: {},
                success: function (response) {
                    setTimeout(function () {
                        window.location.replace("{{route('sw.memberSubscriptionStatistics')}}");
                    }, 500);
                },
                error: function (request, error) {
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });
        }

        $(function() {
            // Subscription Trends Chart
            $('#kt_subscription_trends_chart').highcharts({
                chart: { type: 'area', height: 300, spacingTop: 10 },
                title: null,
                xAxis: {
                    categories: ['{{ trans('sw.jan')}}', '{{ trans('sw.feb')}}', '{{ trans('sw.mar')}}', '{{ trans('sw.apr')}}', '{{ trans('sw.may')}}', '{{ trans('sw.jun')}}', '{{ trans('sw.jul')}}', '{{ trans('sw.aug')}}', '{{ trans('sw.sep')}}', '{{ trans('sw.oct')}}', '{{ trans('sw.nov')}}', '{{ trans('sw.dec')}}']
                },
                yAxis: { title: null },
                legend: { align: 'left', verticalAlign: 'top', y: 0 },
                series: [{
                    name: '{{ trans('sw.new_subscriptions')}}',
                    data: [{{ $new_subscriptions_chart }}],
                    color: '#50CD89'
                }, {
                    name: '{{ trans('sw.expired_subscriptions')}}',
                    data: [{{ $expired_subscriptions_chart }}],
                    color: '#F1416C'
                }, {
                    name: '{{ trans('sw.frozen_subscriptions')}}',
                    data: [{{ $frozen_subscriptions_chart }}],
                    color: '#FFC700'
                }, {
                    name: '{{ trans('sw.reservations')}}',
                    data: [{{ $member_reservations_chart ?? '0,0,0,0,0,0,0,0,0,0,0,0' }}],
                    color: '#009ef7'
                }],
                credits: { enabled: false }
            });

            // Member Reservations Mini Chart
            var reservationsChart = {
                self: null,
                rendered: false
            };
            var initReservationsChart = function() {
                var element = document.getElementById("kt_member_reservations_chart");
                if (!element) {
                    return;
                }
                var height = parseInt(KTUtil.css(element, 'height'));
                var labelColor = KTUtil.getCssVariableValue('--bs-gray-500');
                var borderColor = KTUtil.getCssVariableValue('--bs-gray-200');
                var primaryColor = KTUtil.getCssVariableValue('--bs-primary');
                var options = {
                    series: [{
                        name: '{{ trans('sw.reservations') }}',
                        data: [{{ $member_reservations_chart ?? '0,0,0,0,0,0,0,0,0,0,0,0' }}]
                    }],
                    chart: {
                        fontFamily: 'inherit',
                        type: 'area',
                        height: height,
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        sparkline: { enabled: true }
                    },
                    plotOptions: {},
                    legend: { show: false },
                    dataLabels: { enabled: false },
                    fill: { type: 'solid', opacity: 1 },
                    stroke: {
                        curve: 'smooth',
                        show: true,
                        width: 3,
                        colors: [primaryColor]
                    },
                    xaxis: {
                        categories: ['{{ trans('sw.jan') }}', '{{ trans('sw.feb') }}', '{{ trans('sw.mar') }}', '{{ trans('sw.apr') }}', '{{ trans('sw.may') }}', '{{ trans('sw.jun') }}', '{{ trans('sw.jul') }}', '{{ trans('sw.aug') }}', '{{ trans('sw.sep') }}', '{{ trans('sw.oct') }}', '{{ trans('sw.nov') }}', '{{ trans('sw.dec') }}'],
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: { show: false, style: { colors: labelColor, fontSize: '12px' } },
                        crosshairs: { show: false }
                    },
                    yaxis: {
                        min: 0,
                        max: Math.max({{ $member_reservations_chart ?? '0,0,0,0,0,0,0,0,0,0,0,0' }}) + 5 || 10,
                        labels: { show: false, style: { colors: labelColor, fontSize: '12px' } }
                    },
                    states: {
                        normal: { filter: { type: 'none', value: 0 } },
                        hover: { filter: { type: 'none', value: 0 } },
                        active: { allowMultipleDataPointsSelection: false, filter: { type: 'none', value: 0 } }
                    },
                    tooltip: {
                        style: { fontSize: '12px' },
                        y: {
                            formatter: function(e) {
                                return e + " {{ trans('sw.reservations') }}"
                            }
                        }
                    },
                    colors: ['#009ef7'],
                    markers: {
                        colors: [primaryColor],
                        strokeColor: [primaryColor],
                        strokeWidth: 3
                    }
                };
                reservationsChart.self = new ApexCharts(element, options);
                setTimeout(function() {
                    reservationsChart.self.render();
                    reservationsChart.rendered = true;
                }, 200);
            };
            initReservationsChart();
        });
    </script>
@endsection

