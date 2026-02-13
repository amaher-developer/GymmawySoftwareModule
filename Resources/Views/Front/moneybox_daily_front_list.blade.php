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
        @media print {
            /* Hide non-content elements */
            #kt_aside, #kt_header, #kt_toolbar, .card-header,
            .breadcrumb, .pagination, .modal, .collapse:not(.show),
            .btn, form, .d-flex.flex-stack.flex-wrap,
            .d-flex.align-items-center.position-relative { display: none !important; }
            /* Reset layout */
            body, .wrapper, .content, .container-fluid,
            .card, .card-body { margin: 0 !important; padding: 5px !important; }
            .card { border: none !important; box-shadow: none !important; }
            .card-body { width: 100% !important; }
            /* Table styling */
            .table { font-size: 11px; }
            .table th, .table td { padding: 4px 6px !important; }
            /* Summary cards */
            .card-body .row { page-break-inside: avoid; }
            .card-body .card { border: 1px solid #ddd !important; margin-bottom: 5px !important; }
            .card-body .card .card-body { padding: 8px !important; }
            .card-body .card .card-header { display: block !important; padding: 5px 8px !important; }
            .symbol { display: none !important; }
            /* Show summary buttons area but hide action buttons */
            .actions-column .btn { display: none !important; }
            .badge { border: 1px solid #ddd; padding: 2px 6px !important; }
        }
    </style>
@endsection
@section('page_body')
<!--begin::Card-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-chart-line fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
             <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">

                @if(!request('date'))
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_moneybox_daily_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->
                @endif

                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportMoneyBoxPDF', 'exportMoneyBoxExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download') }}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportMoneyBoxExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportMoneyBoxExcel', request()->query())}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export') }}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportMoneyBoxPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportMoneyBoxPDF', request()->query())}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.pdf_export') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <!--end::Export-->

                                <!--begin::Print-->
                                <button type="button" class="btn btn-sm btn-flex btn-light-primary" onclick="printPageContent()">
                    <i class="ki-outline ki-printer fs-6"></i>
                    {{ trans('sw.print')}}
                </button>
                <!--end::Print-->

            </div>
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        @if(request('date'))
            {{-- Details View --}}
            <div class="d-flex justify-content-end mb-5">
                <a href="{{ route('sw.listMoneyBoxDaily') }}" class="btn btn-light-primary">
                    <i class="ki-outline ki-arrow-left fs-6"></i>
                    {{ trans('sw.back_to_summary') }}
                </a>
            </div>

            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">#</th>
                        <th class="min-w-200px">{{ trans('sw.details') }}</th>
                        <th class="min-w-250px">{{ trans('sw.notes') }}</th>
                        <th class="min-w-100px text-end">{{ trans('sw.amount') }}</th>
                        <th class="min-w-150px text-end">{{ trans('sw.date') }}</th>
                        <th class="min-w-100px text-end">{{ trans('admin.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 fw-bold mb-1">{{ @$order->money_box_type->name }}</span>
                                    <span class="text-muted">{{ @$order->member_name }}</span>
                                    <span class="text-muted fs-7">{{ trans('sw.by') }}: {{ @$order->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-gray-800">{{ $order->notes }}</span>
                            </td>
                            <td class="text-end">
                                @if($order->operation == \Modules\Software\Classes\TypeConstants::Add)
                                    <span class="badge badge-light-success fs-6">{{ number_format($order->amount, 2) }}</span>
                                @else
                                    <span class="badge badge-light-danger fs-6">-{{ number_format($order->amount, 2) }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="text-muted fw-semibold">{{ $order->created_at->format('Y-m-d h:i A') }}</span>
                            </td>
                            <td class="text-end">
                                @if($order->member_subscription_id)
                                    <a href="{{ route('sw.member_invoice', ['id' => $order->member_subscription_id]) }}" target="_blank" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('sw.invoice')}}">
                                        <i class="ki-outline ki-document fs-3"></i>
                                    </a>
                                @elseif($order->member_pt_subscription_id)
                                    <a href="{{ route('sw.pt_member_invoice', ['id' => $order->member_pt_subscription_id]) }}" target="_blank" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('sw.invoice')}}">
                                        <i class="ki-outline ki-document fs-3"></i>
                                    </a>
                                @elseif($order->non_member_subscription_id)
                                    <a href="{{ route('sw.non_member_invoice', ['id' => $order->non_member_subscription_id]) }}" target="_blank" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('sw.invoice')}}">
                                        <i class="ki-outline ki-document fs-3"></i>
                                    </a>
                                @elseif($order->store_order_id)
                                    <a href="{{ route('sw.store_order_invoice', ['id' => $order->store_order_id]) }}" target="_blank" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('sw.invoice')}}">
                                        <i class="ki-outline ki-document fs-3"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <!--end::Table-->

            <!--begin::Statistics-->
            <div class="row g-5 mt-5">
                <div class="col-md-4">
                    <div class="card bg-light-success">
                        <div class="card-body d-flex align-items-center p-4">
                             <div class="symbol symbol-50px me-5"><div class="symbol-label bg-success"><i class="ki-outline ki-arrow-up fs-2x text-white"></i></div></div>
                            <div class="d-flex flex-column">
                                <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.total_in') }}</span>
                                <span class="fs-2 fw-bold text-success">{{ number_format(@$revenues, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                 <div class="col-md-4">
                    <div class="card bg-light-danger">
                        <div class="card-body d-flex align-items-center p-4">
                             <div class="symbol symbol-50px me-5"><div class="symbol-label bg-danger"><i class="ki-outline ki-arrow-down fs-2x text-white"></i></div></div>
                            <div class="d-flex flex-column">
                                <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.total_out') }}</span>
                                <span class="fs-2 fw-bold text-danger">{{ number_format(@$expenses, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light-primary">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="symbol symbol-50px me-5"><div class="symbol-label bg-primary"><i class="ki-outline ki-chart-simple fs-2x text-white"></i></div></div>
                            <div class="d-flex flex-column">
                                <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.net_total') }}</span>
                                <span class="fs-2 fw-bold text-primary">{{ number_format(@$earnings, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Statistics-->

            <!--begin::Pagination-->
            <div class="d-flex flex-stack flex-wrap pt-10">
                <div class="fs-6 fw-semibold text-gray-700">
                    {{ trans('sw.showing_entries', [ 'from' => $orders->firstItem() ?? 0, 'to' => $orders->lastItem() ?? 0, 'total' => $orders->total() ]) }}
                </div>
                <ul class="pagination">
                    {!! $orders->appends(request()->query())->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->

        @else
            <!--begin::Filter-->
            <div class="collapse" id="kt_moneybox_daily_filter_collapse">
                 <div class="card card-body mb-5">
                    <form id="form_filter" action="" method="get">
                        <div class="row g-6">
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.payment_type')}}</label>
                                <select name="payment_type" class="form-select form-select-solid">
                                    <option value="">{{ trans('sw.payment_type')}}...</option>
                                    @foreach($payment_types as $payment_type)
                                        <option value="{{$payment_type->payment_id}}" @if(isset($_GET['payment_type']) && ((request('payment_type') != "") && (request('payment_type') == $payment_type->payment_id))) selected="" @endif>{{$payment_type->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.moneybox')}}</label>
                                <select name="moneybox_type" class="form-select form-select-solid">
                                    <option value="">{{ trans('sw.moneybox')}}...</option>
                                    <option value="{{\Modules\Software\Classes\TypeConstants::CreateMoneyBoxAdd}}" @if(isset($_GET['moneybox_type']) && ((request('moneybox_type') != "") && (request('moneybox_type') == \Modules\Software\Classes\TypeConstants::CreateMoneyBoxAdd))) selected="" @endif>{{ trans('sw.add_to_money_box')}}</option>
                                    <option value="{{\Modules\Software\Classes\TypeConstants::CreateMoneyBoxWithdraw}}" @if(request('moneybox_type') == \Modules\Software\Classes\TypeConstants::CreateMoneyBoxWithdraw) selected="" @endif>{{ trans('sw.withdraw_from_money_box')}}</option>
                                    <option value="{{\Modules\Software\Classes\TypeConstants::CreateMoneyBoxWithdrawEarnings}}" @if(request('moneybox_type') == \Modules\Software\Classes\TypeConstants::CreateMoneyBoxWithdrawEarnings) selected="" @endif>{{ trans('sw.withdraw_earning')}}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.store_credit')}}</label>
                                <select name="is_store_balance" class="form-select form-select-solid">
                                    <option value="">{{ trans('sw.store_credit')}}...</option>
                                    <option value="0" @if(isset($_GET['is_store_balance']) && ((request('is_store_balance') != "") && (request('is_store_balance') == 0))) selected="" @endif>{{ trans('sw.including_balance')}}</option>
                                    <option value="1" @if(request('is_store_balance') == 1) selected="" @endif>{{ trans('sw.excluding_balance')}}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.users')}}</label>
                                <select name="user" class="form-select form-select-solid">
                                    <option value="">{{ trans('sw.users')}}...</option>
                                    @foreach($users as $user)
                                        <option value="{{$user->id}}" @if(isset($_GET['user']) && ((request('user') != "") && (request('user') == $user->id))) selected="" @endif>{{$user->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.subscriptions')}}</label>
                                <select name="subscription" class="form-select form-select-solid">
                                    <option value="">{{ trans('sw.subscriptions')}}...</option>
                                    @foreach($subscriptions as $subscription)
                                        <option value="{{$subscription->id}}" @if(request('subscription') == $subscription->id) selected="" @endif>{{$subscription->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-5">
                            <a href="{{ route('sw.listMoneyBoxDaily') }}" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6">{{ trans('admin.reset')}}</a>
                            <button type="submit" class="btn btn-primary fw-semibold px-6">
                                <i class="ki-outline ki-check fs-6"></i>
                                {{ trans('sw.filter')}}
                            </button>
                        </div>
                    </form>
                 </div>
            </div>
            <!--end::Filter-->
            <!--begin::Search-->
            <div class="d-flex align-items-center position-relative my-1 mb-5">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <form class="d-flex" action="" method="get" style="max-width: 400px;">
                    <input type="text" name="search" class="form-control form-control-solid ps-12" value="@php echo @strip_tags($_GET['search']) @endphp" placeholder="{{ trans('sw.search_on')}}">
                    <button class="btn btn-primary" type="submit">
                        <i class="ki-outline ki-magnifier fs-3"></i>
                    </button>
                </form>
            </div>
            <!--end::Search-->
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

            @if(count($orders) > 0)
                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_moneybox_table">
                        <thead>
                            <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-100px text-nowrap">
                                    <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.amount')}}
                                </th>
                                <!-- <th class="min-w-100px text-nowrap">
                                    <i class="ki-outline ki-chart-simple fs-6 me-2"></i>{{ trans('sw.total_amount_before')}}
                                </th>
                                <th class="min-w-100px text-nowrap">
                                    <i class="ki-outline ki-chart-simple fs-6 me-2"></i>{{ trans('sw.total_amount_after')}}
                                </th> -->
                                <th class="min-w-100px text-nowrap">
                                    <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('sw.operation')}}
                                </th>
                                <th class="min-w-150px text-nowrap">
                                    <i class="ki-outline ki-information-5 fs-6 me-2"></i>{{ trans('sw.notes')}}
                                </th>
                                <th class="min-w-100px text-nowrap">
                                    <i class="ki-outline ki-document fs-6 me-2"></i>{{ trans('sw.invoice')}}
                                </th>
                                <th class="min-w-100px text-nowrap">
                                    <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date')}}
                                </th>
                                <th class="min-w-100px text-nowrap">
                                    <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.by')}}
                                </th>
                                <th class="text-end actions-column">
                                    <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                            @foreach($orders as $key=> $order)
                                <tr>
                                    <td class="pe-0">
                                        <span class="fw-bold">{{ number_format($order->amount, 2) }}</span>
                                    </td>
                                    <!-- <td class="pe-0">
                                        <span class="fw-bold">{{ number_format($order->amount_before, 2)}}</span>
                                    </td> -->
                                    <!-- <td class="pe-0">
                                        <span class="fw-bold">{{ number_format(\Modules\Software\Http\Controllers\Front\GymMoneyBoxFrontController::amountAfter($order->amount, $order->amount_before, $order->operation), 2) }}</span>
                                    </td> -->
                                    <td class="pe-0">
                                        <span class="fw-bold">{!! $order->operation_name !!}</span>
                                    </td>
                                    <td class="pe-0">
                                        <span class="fw-bold">{{ $order->notes }}</span>
                                    </td>
                                    <td class="pe-0">
                                        <a href="{{route('sw.showOrder',$order->id)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                            <i class="ki-outline ki-eye fs-2"></i>
                                        </a>
                                    </td>
                                    <td class="pe-0">
                                        <div class="d-flex flex-column">
                                            <div class="text-muted fw-bold d-flex align-items-center">
                                                <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                                <span>{{ $order->created_at->format('Y-m-d') }}</span>
                                            </div>
                                            <div class="text-muted fs-7 d-flex align-items-center">
                                                <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                                <span>{{ $order->created_at->format('h:i a') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        <span class="fw-bold">{{ @$order->user->name }}</span>
                                    </td>
                                    <td class="text-end actions-column">
                                        <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                            @if(in_array('editPaymentTypeOrderMoneybox', (array)$swUser->permissions) || $swUser->is_super_user)
                                                <a data-bs-target="#modalEdit" data-bs-toggle="modal" href="#"
                                                   id="{{@$order->id}}" payment_type="{{@$order->payment_type}}" style="cursor: pointer;"
                                                   class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm btn-edit-payment"
                                                   title="{{ trans('sw.edit')}}">
                                                    <i class="ki-outline ki-pencil fs-2"></i>
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

                <!--begin::Financial Summary-->
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="card bg-light-primary">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-5">
                                        <div class="symbol-label bg-primary">
                                            <i class="ki-outline ki-chart-simple fs-2x text-white"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.revenues')}}</span>
                                        <span class="fs-2 fw-bold text-primary">{{number_format($revenues, 2)}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-danger">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-5">
                                        <div class="symbol-label bg-danger">
                                            <i class="ki-outline ki-chart-line-down fs-2x text-white"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.expenses')}}</span>
                                        <span class="fs-2 fw-bold text-danger">{{number_format($expenses, 2)}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-success">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-5">
                                        <div class="symbol-label bg-success">
                                            <i class="ki-outline ki-chart-line-up fs-2x text-white"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.earnings')}}</span>
                                        <span class="fs-2 fw-bold text-success">{{number_format($earnings, 2)}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Financial Summary-->

                <!--begin::Detailed Summary-->
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{ trans('sw.payment_types_summary')}}</h3>
                            </div>
                            <div class="card-body">
                                @foreach($payment_types as $payment_type)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="fw-semibold text-gray-900">{{$payment_type->name}}</span>
                                        <div class="d-flex flex-column text-end">
                                            <span class="fs-7 text-muted">{{ trans('sw.revenues2')}}: {{number_format(@$payment_revenues[$payment_type->payment_id] ?? 0, 2)}}</span>
                                            <span class="fs-7 text-muted">{{ trans('sw.expenses2')}}: {{number_format(@$payment_expenses[$payment_type->payment_id] ?? 0, 2)}}</span>
                                            <span class="fs-6 fw-bold text-primary">{{ trans('sw.earnings2')}}: {{number_format(((@$payment_revenues[$payment_type->payment_id] ?? 0) - (@$payment_expenses[$payment_type->payment_id] ?? 0)), 2)}}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        {{-- إيرادات نقدية (Cash Revenues) --}}
                        <div class="card mb-4">
                            <div class="card-header bg-light-success">
                                <h3 class="card-title">
                                    <i class="ki-outline ki-dollar fs-4 me-2 text-success"></i>
                                    {{ trans('sw.cash_revenues')}}
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.subscription_earnings')}}</span>
                                    <span class="fs-6 fw-bold text-success">{{number_format($total_subscriptions, 2)}}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.pt_subscription_earnings')}}</span>
                                    <span class="fs-6 fw-bold text-success">{{number_format($total_pt_subscriptions, 2)}}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.activity_earnings')}}</span>
                                    <span class="fs-6 fw-bold text-success">{{number_format($total_activities, 2)}}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.store_earnings')}}</span>
                                    <span class="fs-6 fw-bold text-success">{{number_format($total_stores, 2)}}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.add_moneybox_revenues')}}</span>
                                    <span class="fs-6 fw-bold text-success">{{number_format($total_add_to_money_box, 2)}}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.withdraw_moneybox_revenues')}}</span>
                                    <span class="fs-6 fw-bold text-danger">{{number_format($total_withdraw_from_money_box, 2)}}</span>
                                </div>
                            </div>
                        </div>

                        {{-- عمليات رصيد (Balance Operations - NOT Revenue) --}}
                        <div class="card">
                            <div class="card-header bg-light-warning">
                                <h3 class="card-title">
                                    <i class="ki-outline ki-wallet fs-4 me-2 text-warning"></i>
                                    {{ trans('sw.balance_operations')}}
                                </h3>
                                <span class="badge badge-light-warning fs-8">{{ trans('sw.not_revenue')}}</span>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning py-2 mb-3">
                                    <i class="ki-outline ki-information-5 fs-6 me-2"></i>
                                    <small>{{ trans('sw.balance_operations_note')}}</small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">
                                        <i class="ki-outline ki-plus-circle fs-6 me-2 text-info"></i>
                                        {{ trans('sw.total_wallet_topups')}}
                                    </span>
                                    <span class="fs-6 fw-bold text-info">{{number_format($total_wallet_topups ?? 0, 2)}}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">
                                        <i class="ki-outline ki-check-circle fs-6 me-2 text-primary"></i>
                                        {{ trans('sw.total_debt_payments')}}
                                    </span>
                                    <span class="fs-6 fw-bold text-primary">{{number_format($total_debt_payments ?? 0, 2)}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Detailed Summary-->

                <!--begin::Pagination-->
                <div class="d-flex flex-stack flex-wrap pt-10">
                    <div class="fs-6 fw-semibold text-gray-700">
                        {{ trans('sw.showing_entries', [
                            'from' => $orders->firstItem() ?? 0,
                            'to' => $orders->lastItem() ?? 0,
                            'total' => $orders->total()
                        ]) }}
                    </div>
                    <ul class="pagination">
                        {!! $orders->appends($search_query)->render() !!}
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
                    <div class="fs-1 fw-bold text-gray-900 mb-3">{{ trans('sw.no_record_found')}}</div>
                    <div class="fs-6 text-gray-600">{{ trans('sw.no_data_available')}}</div>
                </div>
                <!--end::Empty State-->
            @endif
        </div>
        <!--end::Card body-->
        @endif
    </div>
    <!--end::Card-->

    <!-- start model pay -->
    <div class="modal" id="modalEdit">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.payment_type')}}</h6>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                </div>
                <div class="modal-body">
                    <div id="modalEditResult"></div>
                    <form id="form_edit" action="" method="GET">
                        <div class="row">
                            <label class="form-group col-lg-3" style="padding-top: 5px;">{{ trans('sw.payment_type')}}</label>
                            <div class="form-group col-lg-6">
                                <select class="form-control" name="payment_type" id="payment_type">
                                    @foreach($payment_types as $payment_type)
                                        <option id="payment_type_{{$payment_type->payment_id}}" value="{{$payment_type->payment_id}}">{{$payment_type->name}}</option>
                                    @endforeach
                                </select>
                            </div><!-- end pay qty  -->
                            <div class="form-group  col-lg-3">
                            <button class="btn ripple btn-primary rounded-3 " id="form_edit_btn"
                                    type="submit">{{ trans('admin.submit')}}</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End model pay -->
@endsection

@section('scripts')
 <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    @parent
    <script>

        function printPageContent() {
            window.print();
        }

        $(document).on('click', '#export', function (event) {
            event.preventDefault();
            $.ajax({
                url: $(this).attr('url'),
                cache: false,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var a = document.createElement("a");
                    a.href = response.file;
                    a.download = response.name;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });

        });

        $("#filter_form").slideUp();
        $(".filter_trigger_button").click(function () {
            $("#filter_form").slideToggle(300);
        });

        $(document).on('click', '.remove_filter', function (event) {
            event.preventDefault();
            var filter = $(this).attr('id');
            $("#" + filter).val('');
            $("#form_filter").submit();
        });
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

            $('button[type="reset"]').on('click', function() {
                setTimeout(() => {
                    $(this).closest('form').find('select').trigger('change');
                }, 100);
            });
        });


        $('.btn-edit-payment').off('click').on('click', function (e) {
            var that = $(this);
            var attr_id = that.attr('id');
            var payment_type = that.attr('payment_type');
            document.getElementById("payment_type_"+payment_type).selected = "true";
            $('#modalEditResult').hide();
            $('#edit_id').remove();
            $('#form_edit').append('<input value="' + attr_id + '"  id="edit_id" name="edit_id"  hidden>');
        });
        $(document).on('click', '#form_edit_btn', function (event) {
            event.preventDefault();
            id = $('#edit_id').val();
            payment_type = $('#payment_type').val();
            $('#modalEditResult').show();
            $.ajax({
                url: '{{route('sw.editPaymentTypeOrderMoneybox')}}',
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {id: id, payment_type: payment_type},
                success: function (response) {
                    if (response == '1') {
                        $('#modalEditResult').html('<div class="alert alert-success">{{ trans('admin.successfully_paid')}}</div>');
                        location.reload();
                    } else {
                        $('#modalEditResult').html('<div class="alert alert-danger">' + response + '</div>');
                    }

                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
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
                        window.location.replace("{{asset(route('sw.listMoneyBox'))}}");
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

