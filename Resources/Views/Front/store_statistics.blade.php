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
                <!--begin::Card widget - Store Revenue-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center">
                                <!--begin::Currency-->
                                @if($store_revenue > 0)
                                    <span class="fs-4 fw-semibold text-gray-500 me-1 align-self-start">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}</span>
                                @endif
                                <!--end::Currency-->
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">
                                    @if($store_revenue > 0)
                                        {{ number_format($store_revenue, 0) }}
                                    @else
                                        -
                                    @endif
                                </span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.store_revenue')}}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <!--begin::Chart-->
                        <div class="d-flex flex-center me-5 pt-2">
                            <div id="kt_store_revenue_chart" style="min-width: 70px; min-height: 70px"></div>
                        </div>
                        <!--end::Chart-->
                        <!--begin::Labels-->
                        <div class="d-flex flex-column content-justify-center w-100">
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <!--begin::Bullet-->
                                <div class="bullet w-8px h-6px rounded-2 bg-success me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.completed')}}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $completed_orders }}</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center my-3">
                                <!--begin::Bullet-->
                                <div class="bullet w-8px h-6px rounded-2 bg-warning me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.pending')}}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $pending_orders }}</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <!--begin::Bullet-->
                                <div class="bullet w-8px h-6px rounded-2 me-3" style="background-color: #E4E6EF"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.total')}}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $total_orders }}</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Labels-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
                
                <!--begin::Card widget - Orders This Month-->
                <div class="card card-flush h-md-50 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center">
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $total_orders }}</span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.total_orders')}}</span>
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
                                <span class="fw-bolder fs-6 text-gray-900">{{ trans('sw.completed')}}</span>
                                <span class="fw-bold fs-6 text-gray-500">{{ $total_orders > 0 ? round(($completed_orders / $total_orders) * 100) : 0 }}%</span>
                            </div>
                            <div class="h-8px mx-3 w-100 bg-light-success rounded">
                                <div class="bg-success rounded h-8px" role="progressbar" style="width: {{ $total_orders > 0 ? round(($completed_orders / $total_orders) * 100) : 0 }}%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
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
                                <div class="bullet w-8px h-6px rounded-2 bg-info me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.store_revenue')}}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">
                                    @if($store_revenue > 0)
                                        {{number_format($store_revenue, 0)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
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
                
                <!--begin::Card widget - Total Products-->
                <div class="card card-flush h-md-50 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $total_products }}</span>
                            <!--end::Amount-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ trans('sw.total_products')}}</span>
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
                                <div class="bullet w-8px h-6px rounded-2 bg-warning me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ trans('sw.low_stock_products')}}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $low_stock_products }}</div>
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
                            <span class="card-label fw-bold text-gray-900">{{ trans('sw.sales_performance')}}</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ trans('sw.monthly_orders_tracking')}}</span>
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
                                @if($store_revenue > 0)
                                    <span class="fs-4 fw-semibold text-gray-500 me-1">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}</span>
                                    <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2">{{ number_format($store_revenue, 0) }}</span>
                                @else
                                    <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2">-</span>
                                @endif
                            </div>
                            <!--end::Statistics-->
                            <!--begin::Description-->
                            <span class="fs-6 fw-semibold text-gray-500">{{ trans('sw.total_revenue')}}</span>
                            <!--end::Description-->
                        </div>
                        <!--end::Statistics-->
                        <!--begin::Chart-->
                        <div id="kt_sales_performance_chart" class="min-h-auto ps-4 pe-6" style="height: 300px"></div>
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
                <!--begin::Table widget - Top Products-->
                <div class="card h-md-100">
                    <!--begin::Header-->
                    <div class="card-header align-items-center border-0">
                        <!--begin::Title-->
                        <h3 class="fw-bold text-gray-900 m-0">{{ trans('sw.top_products')}}</h3>
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
                                        <th class="ps-0 w-50px">{{ trans('sw.item')}}</th>
                                        <th class="min-w-125px"></th>
                                        <th class="text-end min-w-100px">{{ trans('sw.sales')}}</th>
                                        <th class="pe-0 text-end min-w-100px">{{ trans('sw.price')}}</th>
                                        <th class="pe-0 text-end min-w-100px">{{ trans('sw.revenue')}}</th>
                                    </tr>
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody>
                                    @foreach($top_products as $product)
                                    <tr>
                                        <td>
                                            <img src="{{ $product->image }}" class="w-50px ms-n1" alt="" />
                                        </td>
                                        <td class="ps-0">
                                            <a href="javascript:;" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6 text-start pe-0">{{ $product->name }}</a>
                                            <span class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0">{{ trans('sw.qty')}}: {{ $product->quantity }}</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-bold d-block fs-6 ps-0 text-end">x{{ $product->sales_count }}</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-800 fw-bold d-block fs-6">
                                                @if($product->price > 0)
                                                    {{number_format($product->price, 2)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-800 fw-bold d-block fs-6">
                                                @if($product->revenue > 0)
                                                    {{number_format($product->revenue, 2)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
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
                <!--begin::Chart widget - Order Analytics-->
                <div class="card card-flush overflow-hidden h-md-100">
                    <!--begin::Header-->
                    <div class="card-header py-5">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">{{ trans('sw.order_analytics')}}</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ trans('sw.orders_breakdown')}}</span>
                        </h3>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2">
                        <!--begin::Info-->
                        <div class="px-9 mb-5">
                            <!--begin::Statistics-->
                            <div class="d-flex align-items-center mb-2">
                                <!--begin::Value-->
                                <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2">{{ $total_orders }}</span>
                                <!--end::Value-->
                            </div>
                            <!--end::Statistics-->
                            <!--begin::Description-->
                            <span class="fs-6 fw-semibold text-gray-500">{{ trans('sw.total_orders')}}</span>
                            <!--end::Description-->
                        </div>
                        <!--end::Info-->
                        <!--begin::Chart-->
                        <div id="kt_order_analytics_chart" class="w-100" style="height: 300px"></div>
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
            <div class="col-xl-12 mb-5 mb-xl-10">
                <!--begin::Table Widget - Recent Orders-->
                <div class="card card-flush h-xl-100">
                    <!--begin::Card header-->
                    <div class="card-header pt-7">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">{{ trans('sw.recent_orders')}}</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ trans('sw.latest_transactions')}}</span>
                        </h3>
                        <!--end::Title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2">
                        <!--begin::Scroll-->
                        <div class="hover-scroll-overlay-y pe-6 me-n6" style="max-height: 500px">
                            <!--begin::Table-->
                            <table class="table align-middle table-row-dashed fs-6 gy-3">
                                <!--begin::Table head-->
                                <thead>
                                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-100px">{{ trans('sw.order_id')}}</th>
                                        <th class="min-w-150px">{{ trans('sw.member')}}</th>
                                        <th class="text-end min-w-100px">{{ trans('sw.total')}}</th>
                                        <th class="text-end min-w-100px">{{ trans('sw.status')}}</th>
                                        <th class="text-end min-w-100px">{{ trans('sw.payment_type')}}</th>
                                        <th class="text-end min-w-100px">{{ trans('sw.date')}}</th>
                                    </tr>
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody class="fw-bold text-gray-600">
                                    @foreach($recent_orders as $order)
                                    <tr>
                                        <td>
                                            <a href="javascript:;" class="text-gray-800 text-hover-primary">#{{ $order->id }}</a>
                                        </td>
                                        <td>
                                            <a href="javascript:;" class="text-gray-600 text-hover-primary">{{ $order->member->name ?? 'N/A' }}</a>
                                        </td>
                                        <td class="text-end">
                                            @if($order->total > 0)
                                                {{number_format($order->total, 2)}} {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="badge py-3 px-4 fs-7 @if($order->status == 'completed') badge-light-success @else badge-light-warning @endif">{{ trans('sw.' . $order->status) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-800 fw-bolder">{{ $order->pay_type->name ?? '-' }}</span>
                                        </td>
                                        <td class="text-end">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <!--end::Table body-->
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Scroll-->
                    </div>
                    <!--end::Card body-->
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
            window.location.href = '{{ route('sw.storeStatistics') }}';
        }

        function members_refresh(){
            $('#members_refresh').hide().after('<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
            $.ajax({
                url: '{{route('sw.storeStatisticsRefresh')}}',
                cache: false,
                type: 'GET',
                dataType: 'json',
                data: {},
                success: function (response) {
                    setTimeout(function () {
                        window.location.replace("{{route('sw.storeStatistics')}}");
                    }, 500);
                },
                error: function (request, error) {
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });
        }

        $(function() {
            // Store Revenue Pie Chart
            $('#kt_store_revenue_chart').highcharts({
                chart: { type: 'pie', height: 70, width: 70, margin: 0, spacing: [0, 0, 0, 0] },
                title: null,
                series: [{
                    data: [
                        { name: '{{ trans('sw.completed')}}', y: {{ $completed_orders }}, color: '#50CD89' },
                        { name: '{{ trans('sw.pending')}}', y: {{ $pending_orders }}, color: '#FFC700' },
                    ],
                    size: '100%',
                    innerSize: '60%',
                    dataLabels: { enabled: false }
                }],
                credits: { enabled: false },
                legend: { enabled: false },
                tooltip: { enabled: false }
            });

            // Sales Performance Chart
            $('#kt_sales_performance_chart').highcharts({
                chart: { type: 'area', height: 300, spacingTop: 10 },
                title: null,
                xAxis: {
                    categories: ['{{ trans('sw.jan')}}', '{{ trans('sw.feb')}}', '{{ trans('sw.mar')}}', '{{ trans('sw.apr')}}', '{{ trans('sw.may')}}', '{{ trans('sw.jun')}}', '{{ trans('sw.jul')}}', '{{ trans('sw.aug')}}', '{{ trans('sw.sep')}}', '{{ trans('sw.oct')}}', '{{ trans('sw.nov')}}', '{{ trans('sw.dec')}}']
                },
                yAxis: { title: null },
                legend: { align: 'left', verticalAlign: 'top', y: 0 },
                series: [{
                    name: '{{ trans('sw.completed_orders')}}',
                    data: [{{ $completed_orders_chart }}],
                    color: '#50CD89'
                }, {
                    name: '{{ trans('sw.pending_orders')}}',
                    data: [{{ $pending_orders_chart }}],
                    color: '#FFC700'
                }],
                credits: { enabled: false }
            });

            // Order Analytics Chart
            $('#kt_order_analytics_chart').highcharts({
                chart: { type: 'column', height: 300, spacingTop: 10, spacingBottom: 10 },
                title: null,
                xAxis: {
                    categories: ['{{ trans('sw.jan')}}', '{{ trans('sw.feb')}}', '{{ trans('sw.mar')}}', '{{ trans('sw.apr')}}', '{{ trans('sw.may')}}', '{{ trans('sw.jun')}}', '{{ trans('sw.jul')}}', '{{ trans('sw.aug')}}', '{{ trans('sw.sep')}}', '{{ trans('sw.oct')}}', '{{ trans('sw.nov')}}', '{{ trans('sw.dec')}}']
                },
                yAxis: { title: null },
                legend: { enabled: false },
                plotOptions: {
                    column: {
                        borderRadius: 5,
                        pointPadding: 0.1,
                        groupPadding: 0.1
                    }
                },
                series: [{
                    name: '{{ trans('sw.orders')}}',
                    data: [{{ $completed_orders_chart }}],
                    color: '#009EF7'
                }],
                credits: { enabled: false }
            });
        });
    </script>
@endsection