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
        .tag-red {
            background-color: #ec2d38 !important;
            color: #fff !important;
        }
        .tag-indigo {
            background-color: #0162e8 !important;
            color: #fff !important;
        }
        .tag-green {
            background-color: #0fa751 !important;
            color: #fff !important;
        }
        .tag {
            color: #14112d;
            background-color: #ecf0fa;
            border-radius: 3px !important;
            padding: 0 .5rem;
            line-height: 2em;
            display: -ms-inline-flexbox;
            display: inline-flex;
            cursor: default;
            font-weight: 400;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        .loader {
            border: 16px solid #f3f3f3;
            border-radius: 50% !important;
            border-top: 16px solid #3498db;
            width: 32px;
            height: 32px;
            -webkit-animation: spin 2s linear infinite;
            animation: spin 2s linear infinite;
        }

        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

        /* Actions column styling */
        .actions-column {
            min-width: 120px;
            text-align: right;
        }

        .actions-column .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .actions-column .d-flex {
            gap: 0.25rem;
        }
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
                    <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.moneybox')}}</span>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <!--begin::Filter-->
                    <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_moneybox_filter_collapse">
                        <i class="ki-outline ki-filter fs-6"></i>
                        {{ trans('sw.filter')}}
                    </button>
                    <!--end::Filter-->

                    <!--begin::Export-->
                    @if((count(array_intersect(@(array)$swUser->permissions, ['exportMoneyBoxPDF', 'exportMoneyBoxExcel'])) > 0) || $swUser->is_super_user)
                        <div class="m-0">
                            <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                <i class="ki-outline ki-exit-down fs-6"></i>
                                {{ trans('sw.download')}}
                            </button>
                            <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                                @if(in_array('exportMoneyBoxExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.exportMoneyBoxExcel', ['from' => request('from'), 'to' => request('to'), 'search' => request('search'), 'subscription' => request('subscription'), 'user' => request('user'), 'moneybox_type' => request('moneybox_type'), 'payment_type' => request('payment_type')])}}" class="menu-link px-3">
                                            <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                            {{ trans('sw.excel_export')}}
                                        </a>
                                    </div>
                                @endif
                                @if(in_array('exportMoneyBoxPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.exportMoneyBoxPDF', ['from' => request('from'), 'to' => request('to'), 'search' => request('search'), 'subscription' => request('subscription'), 'user' => request('user'), 'moneybox_type' => request('moneybox_type'), 'payment_type' => request('payment_type')])}}" class="menu-link px-3">
                                            <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                            {{ trans('sw.pdf_export')}}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    <!--end::Export-->

                    <!--begin::Refresh-->
                    <button class="btn btn-sm btn-flex btn-light-primary" id="members_refresh" onclick="members_refresh()">
                        <i class="ki-outline ki-arrows-circle fs-6"></i>
                        {{ trans('sw.members_refresh')}}
                    </button>
                    <!--end::Refresh-->

                    <!--begin::Trashed-->
                    <!--
                    @if($swUser->is_super_user)
                        @if(request('trashed'))
                            <a href="{{ route('sw.listMoneyBox', array_merge(request()->except('trashed'), [])) }}" class="btn btn-sm btn-flex btn-light-danger">
                                <i class="ki-outline ki-eye fs-6"></i>
                                {{ trans('sw.show_active')}}
                            </a>
                        @else
                            <a href="{{ route('sw.listMoneyBox', array_merge(request()->query(), ['trashed' => 1])) }}" class="btn btn-sm btn-flex btn-light-warning">
                                <i class="ki-outline ki-trash fs-6"></i>
                                {{ trans('sw.show_trashed')}}
                            </a>
                        @endif
                    @endif
                    -->
                    <!--end::Trashed-->
                </div>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body pt-0">
            <!--begin::Filter-->
            <div class="collapse" id="kt_moneybox_filter_collapse">
                <div class="card card-body mb-5">
                    <form id="form_filter" action="{{ route('sw.listMoneyBox') }}" method="get">
                        <div class="row g-6">
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                                <div class="input-group date-picker input-daterange">
                                    <input type="text" class="form-control" name="from" id="from_date" value="@php echo @strip_tags($_GET['from']) ? \Carbon\Carbon::parse($_GET['from'])->format('Y-m-d') : date('Y-m-d') @endphp" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                    <span class="input-group-text">{{ trans('sw.to')}}</span>
                                    <input type="text" class="form-control" name="to" id="to_date" value="@php echo @strip_tags($_GET['to']) ? \Carbon\Carbon::parse($_GET['to'])->format('Y-m-d') : date('Y-m-d') @endphp" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                                </div>
                            </div>
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
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.store_credit')}}</label>
                                <select name="is_store_balance" class="form-select form-select-solid">
                                    <option value="">{{ trans('sw.store_credit')}}...</option>
                                    <option value="0" @if(isset($_GET['is_store_balance']) && ((request('is_store_balance') != "") && (request('is_store_balance') == 0))) selected="" @endif>{{ trans('sw.including_balance')}}</option>
                                    <option value="1" @if(request('is_store_balance') == 1) selected="" @endif>{{ trans('sw.excluding_balance')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-5">
                            <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6">{{ trans('admin.reset')}}</button>
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
                                <th class="min-w-100px text-nowrap">
                                    <i class="ki-outline ki-chart-simple fs-6 me-2"></i>{{ trans('sw.total_amount_before')}}
                                </th>
                                <th class="min-w-100px text-nowrap">
                                    <i class="ki-outline ki-chart-simple fs-6 me-2"></i>{{ trans('sw.total_amount_after')}}
                                </th>
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
                                    <td class="pe-0">
                                        <span class="fw-bold">{{ number_format($order->amount_before, 2)}}</span>
                                    </td>
                                    <td class="pe-0">
                                        <span class="fw-bold">{{ number_format(\Modules\Software\Http\Controllers\Front\GymMoneyBoxFrontController::amountAfter($order->amount, $order->amount_before, $order->operation), 2) }}</span>
                                    </td>
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
                                                <a data-target="#modalEdit" data-toggle="modal" href="#"
                                                   id="{{@$order->id}}" payment_type="{{@$order->payment_type}}" style="cursor: pointer;"
                                                   class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                                   title="{{ trans('sw.edit')}}">
                                                    <i class="ki-outline ki-pencil fs-2"></i>
                                                </a>
                                            @endif
                                            <!--
                                            @if($swUser->is_super_user && !request('trashed'))
                                                <a href="#" data-id="{{@$order->id}}"
                                                   class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm delete-moneybox-btn"
                                                   title="{{ trans('sw.delete')}}">
                                                    <i class="ki-outline ki-trash fs-2"></i>
                                                </a>
                                            @endif
                                            @if($swUser->is_super_user && request('trashed'))
                                                <a href="#" data-id="{{@$order->id}}"
                                                   class="btn btn-icon btn-bg-light btn-active-color-success btn-sm restore-moneybox-btn"
                                                   title="{{ trans('sw.restore')}}">
                                                    <i class="ki-outline ki-arrows-circle fs-2"></i>
                                                </a>
                                            @endif
                                            -->
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
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{ trans('sw.earnings_by_category')}}</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.subscription_earnings')}}</span>
                                    <span class="fs-6 fw-bold text-primary">{{number_format($total_subscriptions, 2)}}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.pt_subscription_earnings')}}</span>
                                    <span class="fs-6 fw-bold text-primary">{{number_format($total_pt_subscriptions, 2)}}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.activity_earnings')}}</span>
                                    <span class="fs-6 fw-bold text-primary">{{number_format($total_activities, 2)}}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.store_earnings')}}</span>
                                    <span class="fs-6 fw-bold text-primary">{{number_format($total_stores, 2)}}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.add_moneybox_revenues')}}</span>
                                    <span class="fs-6 fw-bold text-primary">{{$total_add_to_money_box}}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.withdraw_moneybox_revenues')}}</span>
                                    <span class="fs-6 fw-bold text-primary">{{$total_withdraw_from_money_box}}</span>
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
    </div>
    <!--end::Card-->

    <!-- start model pay -->
    <div class="modal" id="modalEdit">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.payment_type')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
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


        $(document).on('click', 'a[data-target="#modalEdit"]', function (e) {
            var that = $(this);
            var attr_id = that.attr('id');
            var payment_type = that.attr('payment_type');
            
            // Validate that we have the required attributes
            if (!attr_id || !payment_type) {
                console.error('Missing id or payment_type attribute');
                return;
            }
            
            var paymentTypeSelect = document.getElementById("payment_type_"+payment_type);
            if (paymentTypeSelect) {
                paymentTypeSelect.selected = true;
            }
            
            $('#modalEditResult').hide();
            $('#edit_id').remove();
            $('#form_edit').append('<input value="' + attr_id + '"  id="edit_id" name="edit_id"  hidden>');
        });
        $(document).on('click', '#form_edit_btn', function (event) {
            event.preventDefault();
            var id = $('#edit_id').val();
            var payment_type = $('#payment_type').val();
            
            // Validate that both values are present
            if (!id || !payment_type) {
                $('#modalEditResult').show();
                $('#modalEditResult').html('<div class="alert alert-danger">{{ trans('admin.operation_failed')}}: Missing required data</div>');
                console.error('Missing id or payment_type. id:', id, 'payment_type:', payment_type);
                return;
            }
            
            $('#modalEditResult').show();
            $.ajax({
                url: '{{route('sw.editPaymentTypeOrderMoneybox')}}',
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {id: id, payment_type: payment_type},
                success: function (response) {
                    if (response == '1' || response.trim() == '1') {
                        $('#modalEditResult').html('<div class="alert alert-success">{{ trans('admin.successfully_paid')}}</div>');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
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

        // Delete moneybox entry with rebuild
        $(document).on('click', '.delete-moneybox-btn', function (event) {
            event.preventDefault();
            var that = $(this);
            var id = that.data('id');
            var tableRow = that.closest('tr'); // Store row reference

            // Validate that we have the required ID
            if (!id) {
                swal("{{ trans('admin.operation_failed')}}", "{{ trans('admin.missing_data')}}", "error");
                console.error('Missing moneybox ID');
                return;
            }

            // Show confirmation dialog
            swal({
                title: "{{ trans('admin.are_you_sure')}}",
                text: "{{ trans('sw.delete_moneybox_warning')}}",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "{{ trans('admin.yes_delete')}}",
                cancelButtonText: "{{ trans('admin.cancel')}}",
                showLoaderOnConfirm: true,
                preConfirm: function () {
                    return new Promise(function (resolve, reject) {
                        // Perform the delete operation with CSRF token
                        $.ajax({
                            url: '{{route('sw.deleteMoneyBox')}}',
                            cache: false,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                id: id,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                if (response.success) {
                                    resolve(response);
                                } else {
                                    reject(response.message || "{{ trans('admin.something_went_wrong')}}");
                                }
                            },
                            error: function (request, error) {
                                reject("{{ trans('admin.something_went_wrong')}}");
                                console.error("Request: " + JSON.stringify(request));
                                console.error("Error: " + JSON.stringify(error));
                            }
                        });
                    });
                },
                allowOutsideClick: false
            }).then(function (result) {
                if (result.value) {
                    // Close SweetAlert immediately
                    swal.close();

                    // Remove the table row immediately after closing the dialog
                    setTimeout(function() {
                        tableRow.fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 100);

                    // Reload the page to show updated calculations
                    setTimeout(function() {
                        location.reload();
                    }, 800);
                }
            }).catch(function (error) {
                if (error) {
                    swal("{{ trans('admin.operation_failed')}}", error, "error");
                }
            });
        });

        // Restore moneybox entry with rebuild
        $(document).on('click', '.restore-moneybox-btn', function (event) {
            event.preventDefault();
            var that = $(this);
            var id = that.data('id');

            // Validate that we have the required ID
            if (!id) {
                swal("{{ trans('admin.operation_failed')}}", "{{ trans('admin.missing_data')}}", "error");
                console.error('Missing moneybox ID');
                return;
            }

            // Show confirmation dialog
            swal({
                title: "{{ trans('admin.are_you_sure')}}",
                text: "{{ trans('sw.restore_moneybox_warning')}}",
                type: "info",
                showCancelButton: true,
                confirmButtonColor: "#0fa751",
                confirmButtonText: "{{ trans('admin.yes_restore')}}",
                cancelButtonText: "{{ trans('admin.cancel')}}",
                showLoaderOnConfirm: true,
                preConfirm: function () {
                    return new Promise(function (resolve, reject) {
                        // Perform the restore operation with CSRF token
                        $.ajax({
                            url: '{{route('sw.restoreMoneyBox')}}',
                            cache: false,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                id: id,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                if (response.success) {
                                    resolve(response);
                                } else {
                                    reject(response.message || "{{ trans('admin.something_went_wrong')}}");
                                }
                            },
                            error: function (request, error) {
                                reject("{{ trans('admin.something_went_wrong')}}");
                                console.error("Request: " + JSON.stringify(request));
                                console.error("Error: " + JSON.stringify(error));
                            }
                        });
                    });
                },
                allowOutsideClick: false
            }).then(function (result) {
                if (result.value) {
                    // Close SweetAlert immediately
                    swal.close();

                    // Remove the table row immediately after closing the dialog
                    setTimeout(function() {
                        that.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 100);

                    // Reload the page to show updated calculations
                    setTimeout(function() {
                        location.reload();
                    }, 800);
                }
            }).catch(function (error) {
                if (error) {
                    swal("{{ trans('admin.operation_failed')}}", error, "error");
                }
            });
        });

    </script>

@endsection

