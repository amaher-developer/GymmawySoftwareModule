@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
    </ul>
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css"/>
    <style>
        @if($lang == 'ar')
            .static-info.align-reverse .name, .static-info.align-reverse .value { text-align: right; }
        @else
            .static-info.align-reverse .name, .static-info.align-reverse .value { text-align: left; }
        @endif

        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-responsive table { min-width: 1200px; }

        .badge-sale   { background-color: #0fa751; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 12px; }
        .badge-refund { background-color: #ec2d38; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 12px; }

        .summary-card {
            border-radius: 8px;
            padding: 16px 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #e4e6ef;
            padding: 6px 0;
            font-size: 14px;
        }
        .summary-row:last-child { border-bottom: none; font-weight: 700; font-size: 15px; }
        .summary-row .label { color: #5e6278; }
        .summary-row .value { font-weight: 600; }
        .value-positive { color: #0fa751; }
        .value-negative { color: #ec2d38; }
        .value-neutral  { color: #3f4254; }
    </style>
@endsection

@section('page_body')
    <div class="card card-flush">
        <!--begin::Card header-->
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <div class="d-flex align-items-center my-1">
                    <i class="ki-outline ki-bill fs-2 me-3 text-primary"></i>
                    <span class="fs-4 fw-semibold text-gray-900">{{ $title }}</span>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_tax_real_filter_collapse">
                        <i class="ki-outline ki-filter fs-6"></i>
                        {{ trans('sw.filter') }}
                    </button>
                    @if((count(array_intersect(@(array)$swUser->permissions, ['exportMoneyBoxTaxPDF','exportMoneyBoxTaxExcel'])) > 0) || $swUser->is_super_user)
                        <div class="m-0">
                            <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                <i class="ki-outline ki-exit-down fs-6"></i>
                                {{ trans('sw.download') }}
                            </button>
                            <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                                @if(in_array('exportMoneyBoxTaxExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{ route('sw.exportMoneyBoxTaxExcel', ['from' => request('from'), 'to' => request('to'), 'transaction' => request('transaction')]) }}" class="menu-link px-3">
                                            <i class="ki-outline ki-file-down fs-6 me-2"></i>{{ trans('sw.excel_export') }}
                                        </a>
                                    </div>
                                @endif
                                @if(in_array('exportMoneyBoxTaxPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{ route('sw.exportMoneyBoxTaxPDF', ['from' => request('from'), 'to' => request('to'), 'transaction' => request('transaction')]) }}" class="menu-link px-3">
                                            <i class="ki-outline ki-file-down fs-6 me-2"></i>{{ trans('sw.pdf_export') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!--end::Card header-->

        <div class="card-body pt-0">
            <!--begin::Filter-->
            <div class="collapse" id="kt_tax_real_filter_collapse">
                <div class="card card-body mb-5">
                    <form id="form_filter" action="" method="get">
                        <div class="row g-6">
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range') }}</label>
                                <div class="input-group date-picker input-daterange">
                                    <input type="text" class="form-control" name="from" id="from_date"
                                        value="@php echo @strip_tags($_GET['from']) ? \Carbon\Carbon::parse($_GET['from'])->format('Y-m-d') : date('Y-m-d') @endphp"
                                        placeholder="{{ trans('sw.from') }}" autocomplete="off">
                                    <span class="input-group-text">{{ trans('sw.to') }}</span>
                                    <input type="text" class="form-control" name="to" id="to_date"
                                        value="@php echo @strip_tags($_GET['to']) ? \Carbon\Carbon::parse($_GET['to'])->format('Y-m-d') : date('Y-m-d') @endphp"
                                        placeholder="{{ trans('sw.to') }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.transaction_type') }}</label>
                                <select name="transaction" class="form-select form-select-solid">
                                    <option value="">{{ trans('admin.choose') }}...</option>
                                    <option value="1" @if(request('transaction') == \Modules\Software\Classes\TypeConstants::TAX_TRANSACTION_SALES) selected @endif>{{ trans('sw.sales') }}</option>
                                    <option value="2" @if(request('transaction') == \Modules\Software\Classes\TypeConstants::TAX_TRANSACTION_REFUND) selected @endif>{{ trans('sw.refund') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.type') }}</label>
                                <select name="type" class="form-select form-select-solid">
                                    <option value="">{{ trans('admin.choose') }}...</option>
                                    <option value="1" @if(request('type') == 1) selected @endif>{{ trans('sw.subscribed_clients') }}</option>
                                    <option value="2" @if(request('type') == 2) selected @endif>{{ trans('sw.daily_clients') }}</option>
                                    <option value="3" @if(request('type') == 3) selected @endif>{{ trans('sw.pt') }}</option>
                                    <option value="4" @if(request('type') == 4) selected @endif>{{ trans('sw.store') }}</option>
                                    <option value="5" @if(request('type') == 5) selected @endif>{{ trans('sw.moneybox') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-5">
                            <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6">{{ trans('admin.reset') }}</button>
                            <button type="submit" class="btn btn-primary fw-semibold px-6">
                                <i class="ki-outline ki-check fs-6"></i>{{ trans('sw.filter') }}
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
                    <input type="text" name="search" class="form-control form-control-solid ps-12"
                        value="@php echo @strip_tags($_GET['search']) @endphp"
                        placeholder="{{ trans('sw.search_on') }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="ki-outline ki-magnifier fs-3"></i>
                    </button>
                </form>
            </div>
            <!--end::Search-->

            <!--begin::Period header-->
            @php
                $vatPct = (float)(@$mainSettings->vat_details['vat_percentage'] ?? 0);
                $fromDisplay = @strip_tags($_GET['from']) ? \Carbon\Carbon::parse($_GET['from'])->format('Y-m-d') : date('Y-m-d');
                $toDisplay   = @strip_tags($_GET['to'])   ? \Carbon\Carbon::parse($_GET['to'])->format('Y-m-d')   : date('Y-m-d');
            @endphp
            <div class="d-flex align-items-center justify-content-between mb-5 p-4 rounded bg-light-primary">
                <div class="d-flex align-items-center gap-5">
                    <div>
                        <span class="fs-7 text-muted">{{ trans('sw.from') }}</span>
                        <span class="fs-6 fw-bold text-gray-900 ms-2">{{ $fromDisplay }}</span>
                    </div>
                    <i class="ki-outline ki-arrow-right fs-4 text-muted"></i>
                    <div>
                        <span class="fs-7 text-muted">{{ trans('sw.to') }}</span>
                        <span class="fs-6 fw-bold text-gray-900 ms-2">{{ $toDisplay }}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge badge-light-primary fs-7">{{ trans('admin.total_count') }}: {{ $total }}</span>
                    @if($vatPct > 0)
                        <span class="badge badge-light-warning fs-7">{{ trans('sw.vat') }}: {{ $vatPct }}%</span>
                    @endif
                </div>
            </div>
            <!--end::Period header-->

            @if(count($orders) > 0)

            <!--begin::Tax Summary Cards-->
            @php
                // Aggregate totals across the full (non-paginated) result set via $revenues/$expenses
                // VAT totals from the orders in the current view — loop all sorders via $orders
                // We compute from the paginated set for display; summary uses controller-provided vars
                $totalSalesWithVat   = $revenues;
                $totalRefundsWithVat = $expenses;
                // Reconstruct VAT portion from paginated items
                $totalSalesVat   = 0;
                $totalRefundsVat = 0;
                foreach($orders as $o){
                    $ov = round($o->vat ?? 0, 2);
                    if($o->operation == 0) $totalSalesVat   += $ov;
                    else                   $totalRefundsVat += $ov;
                }
                $totalSalesBeforeVat   = $totalSalesWithVat   - $totalSalesVat;
                $totalRefundsBeforeVat = $totalRefundsWithVat - $totalRefundsVat;
                $netTaxableAmount = $totalSalesBeforeVat - $totalRefundsBeforeVat;
                $netVatPayable    = $totalSalesVat - $totalRefundsVat;
                $netTotal         = $totalSalesWithVat - $totalRefundsWithVat;
            @endphp

            <div class="row g-4 mb-6">
                <!--Sales column-->
                <div class="col-md-4">
                    <div class="card border border-success">
                        <div class="card-header bg-light-success py-3">
                            <h5 class="card-title mb-0 text-success">
                                <i class="ki-outline ki-arrow-up fs-4 me-1 text-success"></i>
                                {{ trans('sw.sales') }}
                            </h5>
                        </div>
                        <div class="card-body summary-card">
                            <div class="summary-row">
                                <span class="label">{{ trans('sw.invoice_total') }} ({{ trans('sw.excl_tax') ?? trans('sw.before_vat') ?? 'Excl. Tax' }})</span>
                                <span class="value value-neutral">{{ number_format($totalSalesBeforeVat, 2) }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="label">{{ trans('sw.vat') }}{{ $vatPct > 0 ? ' ('.$vatPct.'%)' : '' }}</span>
                                <span class="value value-neutral">{{ number_format($totalSalesVat, 2) }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="label">{{ trans('sw.invoice_total_required') }} ({{ trans('sw.incl_tax') ?? trans('sw.with_vat') ?? 'Incl. Tax' }})</span>
                                <span class="value value-positive">{{ number_format($totalSalesWithVat, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Refunds column-->
                <div class="col-md-4">
                    <div class="card border border-danger">
                        <div class="card-header bg-light-danger py-3">
                            <h5 class="card-title mb-0 text-danger">
                                <i class="ki-outline ki-arrow-down fs-4 me-1 text-danger"></i>
                                {{ trans('sw.refund') }}
                            </h5>
                        </div>
                        <div class="card-body summary-card">
                            <div class="summary-row">
                                <span class="label">{{ trans('sw.invoice_total') }}</span>
                                <span class="value value-neutral">{{ number_format($totalRefundsBeforeVat, 2) }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="label">{{ trans('sw.vat') }}</span>
                                <span class="value value-neutral">{{ number_format($totalRefundsVat, 2) }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="label">{{ trans('sw.invoice_total_required') }}</span>
                                <span class="value value-negative">{{ number_format($totalRefundsWithVat, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Net Tax column-->
                <div class="col-md-4">
                    <div class="card border border-primary">
                        <div class="card-header bg-light-primary py-3">
                            <h5 class="card-title mb-0 text-primary">
                                <i class="ki-outline ki-bill fs-4 me-1 text-primary"></i>
                                {{ trans('sw.vat_total') }} ({{ trans('sw.net') ?? 'Net' }})
                            </h5>
                        </div>
                        <div class="card-body summary-card">
                            <div class="summary-row">
                                <span class="label">{{ trans('sw.invoice_total') }}</span>
                                <span class="value value-neutral">{{ number_format($netTaxableAmount, 2) }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="label">{{ trans('sw.vat_total') }}</span>
                                <span class="value value-neutral">{{ number_format($netVatPayable, 2) }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="label">{{ trans('sw.invoice_total_required') }}</span>
                                <span class="value {{ $netTotal >= 0 ? 'value-positive' : 'value-negative' }}">{{ number_format($netTotal, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Tax Summary Cards-->

            <!--begin::Detail Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-4">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0 bg-light">
                            <th class="min-w-60px text-nowrap">#</th>
                            <th class="min-w-90px text-nowrap">
                                <i class="ki-outline ki-document fs-6 me-1"></i>
                                {{ trans('sw.invoice') }}
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-1"></i>
                                {{ trans('sw.client_name') ?? trans('sw.name') }}
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="ki-outline ki-basket fs-6 me-1"></i>
                                {{ trans('sw.service') ?? trans('sw.subscription') }}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-1"></i>
                                {{ trans('sw.date') }}
                            </th>
                            <th class="min-w-110px text-nowrap">
                                <i class="ki-outline ki-wallet fs-6 me-1"></i>
                                {{ trans('sw.payment_type') }}
                            </th>
                            <th class="min-w-100px text-nowrap text-center">
                                {{ trans('sw.transaction_type') }}
                            </th>
                            <th class="min-w-120px text-nowrap text-end">
                                {{ trans('sw.invoice_total') }}
                                <span class="fs-8 text-muted d-block">({{ trans('sw.excl_tax') ?? 'Excl. Tax' }})</span>
                            </th>
                            <th class="min-w-80px text-nowrap text-end">
                                {{ trans('sw.vat') }} %
                            </th>
                            <th class="min-w-100px text-nowrap text-end">
                                {{ trans('sw.vat_total') }}
                            </th>
                            <th class="min-w-130px text-nowrap text-end">
                                {{ trans('sw.invoice_total_required') }}
                                <span class="fs-8 text-muted d-block">({{ trans('sw.incl_tax') ?? 'Incl. Tax' }})</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    @foreach($orders as $key => $order)
                        @php
                            // Resolve client name and service/product description
                            if (@$order->member_pt_subscription) {
                                $clientName  = @$order->member_pt_subscription->member->name ?? trans('sw.guest');
                                $serviceName = @$order->member_pt_subscription->pt_subscription->name ?? trans('sw.pt');
                            } elseif (@$order->non_member_subscription) {
                                $clientName  = @$order->non_member_subscription->name ?? trans('sw.guest');
                                $serviceName = @$order->non_member_subscription->name ?? trans('sw.daily_clients');
                            } elseif (@$order->store_order) {
                                $clientName  = @$order->store_order->name ?? trans('sw.guest');
                                $serviceName = trans('sw.products');
                            } elseif (@$order->member_subscription) {
                                $clientName  = @$order->member_subscription->member->name ?? trans('sw.guest');
                                $serviceName = @$order->member_subscription->subscription->name ?? trans('sw.subscription');
                            } else {
                                $clientName  = trans('sw.guest');
                                $serviceName = trans('sw.moneybox');
                            }

                            $totalWithVat    = round($order->amount, 2);
                            $vatAmount       = round($order->vat ?? 0, 2);
                            $totalBeforeVat  = $totalWithVat - $vatAmount;
                            // Guard: if amount is 0 or subtraction yields negative, zero-out VAT
                            if ($totalWithVat <= 0 || $totalBeforeVat < 0) {
                                $vatAmount      = 0;
                                $totalBeforeVat = $totalWithVat;
                            }
                            $isSale          = ($order->operation == 0);

                            // Resolve payment type name
                            $paymentTypeName = '';
                            foreach($payment_types as $pt){
                                if($pt->payment_id == $order->payment_type){
                                    $paymentTypeName = $pt->name;
                                    break;
                                }
                            }
                        @endphp
                        <tr>
                            <td>
                                <span class="text-gray-600 fw-bold">{{ ($orders->currentPage() - 1) * $orders->perPage() + $key + 1 }}</span>
                            </td>
                            <td>
                                <a href="{{ route('sw.showOrder', $order->id) }}" class="text-primary fw-bold text-hover-primary">
                                    # {{ $order->id }}
                                </a>
                            </td>
                            <td>
                                <span class="text-gray-900 fw-semibold">{{ $clientName }}</span>
                            </td>
                            <td>
                                <span class="text-gray-700">{{ $serviceName }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-900 fw-semibold">{{ $order->created_at->format('Y-m-d') }}</span>
                                    <span class="text-muted fs-7">{{ $order->created_at->format('h:i a') }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-gray-700">{{ $paymentTypeName ?: '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if($isSale)
                                    <span class="badge-sale">{{ trans('sw.sales') }}</span>
                                @else
                                    <span class="badge-refund">{{ trans('sw.refund') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold {{ $isSale ? 'text-gray-900' : 'text-danger' }}">
                                    {{ $isSale ? '' : '-' }}{{ number_format($totalBeforeVat, 2) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="text-muted">{{ $vatPct > 0 ? $vatPct.'%' : '-' }}</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold {{ $isSale ? 'text-gray-900' : 'text-danger' }}">
                                    {{ $isSale ? '' : '-' }}{{ number_format($vatAmount, 2) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold fs-6 {{ $isSale ? 'text-success' : 'text-danger' }}">
                                    {{ $isSale ? '' : '-' }}{{ number_format($totalWithVat, 2) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <!--begin::Table footer totals-->
                    <tfoot>
                        <tr class="bg-light fw-bold text-gray-900 fs-6">
                            <td colspan="7" class="text-end pe-4">{{ trans('admin.total') }}</td>
                            <td class="text-end text-success">{{ number_format($netTaxableAmount, 2) }}</td>
                            <td class="text-end">-</td>
                            <td class="text-end text-success">{{ number_format($netVatPayable, 2) }}</td>
                            <td class="text-end text-success">{{ number_format($netTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                    <!--end::Table footer totals-->
                </table>
            </div>
            <!--end::Detail Table-->

            <!--begin::Earnings by category-->
            <div class="row g-4 mt-4 mb-5">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ trans('sw.earnings_by_category') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold text-gray-900">{{ trans('sw.subscription_earnings') }}</span>
                                <span class="fs-6 fw-bold text-primary">{{ number_format($total_subscriptions, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold text-gray-900">{{ trans('sw.pt_subscription_earnings') }}</span>
                                <span class="fs-6 fw-bold text-primary">{{ number_format($total_pt_subscriptions, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold text-gray-900">{{ trans('sw.activity_earnings') }}</span>
                                <span class="fs-6 fw-bold text-primary">{{ number_format($total_activities, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold text-gray-900">{{ trans('sw.store_earnings') }}</span>
                                <span class="fs-6 fw-bold text-primary">{{ number_format($total_stores, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold text-gray-900">{{ trans('sw.moneybox_earnings') }}</span>
                                <span class="fs-6 fw-bold text-primary">{{ number_format(@$total_moneybox, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ trans('sw.payment_types_summary') }}</h3>
                        </div>
                        <div class="card-body">
                            @foreach($payment_types as $payment_type)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-semibold text-gray-900">{{ $payment_type->name }}</span>
                                    <div class="d-flex flex-column text-end">
                                        <span class="fs-7 text-muted">{{ trans('sw.revenues2') }}: {{ number_format(@$payment_revenues[$payment_type->payment_id] ?? 0, 2) }}</span>
                                        <span class="fs-7 text-muted">{{ trans('sw.expenses2') }}: {{ number_format(@$payment_expenses[$payment_type->payment_id] ?? 0, 2) }}</span>
                                        <span class="fs-6 fw-bold text-primary">{{ trans('sw.earnings2') }}: {{ number_format(((@$payment_revenues[$payment_type->payment_id] ?? 0) - (@$payment_expenses[$payment_type->payment_id] ?? 0)), 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Earnings by category-->

            <!--begin::Pagination-->
            <div class="d-flex flex-stack flex-wrap pt-10">
                <div class="fs-6 fw-semibold text-gray-700">
                    {{ trans('sw.showing_entries', [
                        'from'  => $orders->firstItem() ?? 0,
                        'to'    => $orders->lastItem() ?? 0,
                        'total' => $orders->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $orders->appends($search_query)->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->

            @else
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label bg-light-primary">
                        <i class="ki-outline ki-bill fs-2x text-primary"></i>
                    </div>
                </div>
                <h3 class="fs-2 fw-bold text-gray-900 mb-2">{{ trans('sw.no_record_found') }}</h3>
                <div class="fs-6 fw-semibold text-gray-500 mb-6">{{ trans('sw.no_tax_records_found') }}</div>
            </div>
            @endif
        </div>
        <!--end::Card body-->
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js') }}" type="text/javascript"></script>
    @parent
    <script>
        jQuery(document).ready(function () {
            var today = new Date();
            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true,
                orientation: 'bottom auto',
                defaultViewDate: { year: today.getFullYear(), month: today.getMonth(), day: today.getDate() }
            });
            $('button[type="reset"]').on('click', function () {
                setTimeout(() => { $(this).closest('form').find('select').trigger('change'); }, 100);
            });
        });
    </script>
@endsection
