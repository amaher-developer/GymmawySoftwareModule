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
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css"/>
    <style>
        .font-weight-bolder {
            font-weight: bolder !important;
        }
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
        .m-2 {
            margin: 1.5rem !important;
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

        /* Responsive table styles */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive table {
            min-width: 1000px;
        }

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
                    <i class="ki-outline ki-chart-line fs-2 me-3"></i>
                    <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <!--begin::Filter-->
                    <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_moneybox_tax_filter_collapse">
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
                                @if(in_array('exportMoneyBoxTaxExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.exportMoneyBoxTaxExcel', ['from' => request('from'), 'to' => request('to'), 'transaction' => request('transaction')])}}" class="menu-link px-3">
                                            <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                            {{ trans('sw.excel_export')}}
                                        </a>
                                    </div>
                                @endif
                                @if(in_array('exportMoneyBoxTaxPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.exportMoneyBoxTaxPDF', ['from' => request('from'), 'to' => request('to'), 'transaction' => request('transaction')])}}" class="menu-link px-3">
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
            <div class="collapse" id="kt_moneybox_tax_filter_collapse">
                <div class="card card-body mb-5">
                    <form id="form_filter" action="" method="get">
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
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.transaction_type')}}</label>
                                <select name="transaction" class="form-select form-select-solid">
                                    <option value="">{{ trans('admin.choose')}}...</option>
                                    <option value="1" @if(request('transaction') == \Modules\Software\Classes\TypeConstants::TAX_TRANSACTION_SALES) selected="" @endif>{{ trans('sw.sales')}}</option>
                                    <option value="2" @if(request('transaction') == \Modules\Software\Classes\TypeConstants::TAX_TRANSACTION_REFUND) selected="" @endif>{{ trans('sw.refund')}}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.type')}}</label>
                                <select name="type" class="form-select form-select-solid">
                                    <option value="">{{ trans('admin.choose')}}...</option>
                                    <option value="1" @if(request('type') == 1) selected="" @endif>{{ trans('sw.subscribed_clients')}}</option>
                                    <option value="2" @if(request('type') == 2) selected="" @endif>{{ trans('sw.daily_clients')}}</option>
                                    <option value="3" @if(request('type') == 3) selected="" @endif>{{ trans('sw.pt')}}</option>
                                    <option value="4" @if(request('type') == 4) selected="" @endif>{{ trans('sw.store')}}</option>
                                    <option value="5" @if(request('type') == 5) selected="" @endif>{{ trans('sw.moneybox')}}</option>
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
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-80px text-nowrap">
                                <i class="ki-outline ki-hash fs-6 me-2"></i>
                                #
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-dollar fs-6 me-2"></i>
                                {{ trans('sw.invoice_total')}}
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-dollar fs-6 me-2"></i>
                                {{ trans('sw.vat_total')}}
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-dollar fs-6 me-2"></i>
                                {{ trans('sw.invoice_total_required')}}
                            </th>
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-information fs-6 me-2"></i>
                                {{ trans('sw.notes')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-document fs-6 me-2"></i>
                                {{ trans('sw.invoice')}}
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>
                                {{ trans('sw.date')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-2"></i>
                                {{ trans('sw.by')}}
                            </th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        @foreach($orders as $key=> $order)
                            @php
                                $total_before_vat = 0;
                                $total_after_vat = 0;
                                if(@$order->member_pt_subscription){
                                    $total_before_vat =  round((@$order->member_pt_subscription->amount_paid + @$order->member_pt_subscription->amount_remaining) / ((100+@$mainSettings->vat_details['vat_percentage'])/100), 2);
                                }else if(@$order->non_member_subscription || ((int)$order->type === \Modules\Software\Classes\TypeConstants::CreateNonMember)){
                                    $total_before_vat =  round((@$order->non_member_subscription->price + @$order->non_member_subscription->price_remaining) / ((100+@$mainSettings->vat_details['vat_percentage'])/100), 2);
                                    if(!@$order->non_member_subscription->price){
                                        $total_before_vat= round(((@$order->amount) / ((100+@$mainSettings->vat_details['vat_percentage'])/100)),2);
                                    }
                                }else if(@$order->store_order || ((int)$order->type === \Modules\Software\Classes\TypeConstants::CreateStoreOrder) || ((int)$order->type === \Modules\Software\Classes\TypeConstants::CreateStorePurchaseOrder)){
                                    $total_before_vat =  round((@$order->store_order->amount_paid + @$order->store_order->amount_remaining) / ((100+@$mainSettings->vat_details['vat_percentage'])/100), 2);
                                    if(!@$order->store_order->amount_paid){
                                        $total_before_vat= round(((@$order->amount) / ((100+@$mainSettings->vat_details['vat_percentage'])/100)), 2);
                                    }

                                }else if(@$order->member_subscription){
                                    $total_before_vat =  round((@$order->member_subscription->amount_paid + @$order->member_subscription->amount_remaining) / ((100+@$mainSettings->vat_details['vat_percentage'])/100), 2);
                                }else{
                                    $total_before_vat =  round($order->amount / ((100+@$mainSettings->vat_details['vat_percentage'])/100),2);
                                }

                                if(@$order->member_pt_subscription){
                                    $total_after_vat = round((@$order->member_pt_subscription->amount_paid + @$order->member_pt_subscription->amount_remaining), 2);
                                    $client_name = @$order->member_pt_subscription->member->name;
                                    $product_name = @$order->member_pt_subscription->pt_subscription->name;
                                }elseif(@$order->non_member_subscription){
                                    $total_after_vat = round((@$order->non_member_subscription->price + @$order->non_member_subscription->price_remaining), 2);
                                    $client_name = @$order->non_member_subscription->name;
                                    $product_name = @$order->non_member_subscription->name;
                                }elseif(@$order->store_order){
                                    $total_after_vat = round((@$order->store_order->amount_paid + @$order->store_order->amount_remaining), 2);
                                    $client_name = @$order->store_order->name ?? trans('sw.guest');
                                    $product_name = @$order->store_order->name ?? trans('sw.products');
                                }elseif(@$order->member_subscription){
                                    $total_after_vat = round((@$order->member_subscription->amount_paid + @$order->member_subscription->amount_remaining), 2);
                                    $client_name = @$order->member_subscription->member->name;
                                    $product_name = @$order->member_subscription->subscription->name;
                                }elseif(@$order->store_order_id){
                                     $total_after_vat =round($order->amount,2);
                                     $client_name = trans('sw.guest');
                                     $product_name = trans('sw.product');

                                }else{
                                     $total_after_vat =round($order->amount, 2);
                                     $client_name = trans('sw.guest');
                                     $product_name = trans('sw.moneybox');

                                }
                                
                                // Use the order's actual amount and VAT values
                                $total_after_vat = round($order->amount, 2);
                                $vat = round($order->vat ?? 0, 2);
                                $total_before_vat = $total_after_vat - $vat;

                            @endphp
                            <tr>
                                <td class="pe-0">
                                    <span class="text-gray-900 fw-bold"># {{$order->id}}</span>
                                </td>
                                <td class="pe-0">
                                    <span class="text-gray-900 fw-bold">{{$total_before_vat}}</span>
                                </td>
                                <td class="pe-0">
                                    <span class="text-gray-900 fw-bold">{{ number_format($vat, 2)}}</span>
                                </td>
                                <td class="pe-0">
                                    <span class="text-gray-900 fw-bold">{{$total_after_vat}}</span>
                                </td>
                                <td class="pe-0">
                                    <span class="text-gray-800 fs-6">{{ trans(($order->operation == \Modules\Software\Classes\TypeConstants::Add ? 'sw.tax_msg_sales' : 'sw.tax_msg_funds'), ['name' => $client_name,'amount' => $total_after_vat, 'product' => $product_name ]) }}</span>
                                </td>
                                <td class="pe-0">
                                    <a href="{{route('sw.showOrder',$order->id)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('sw.invoice')}}">
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
                                    <span class="fw-semibold text-gray-900">{{ trans('sw.moneybox_earnings')}}</span>
                                    <span class="fs-6 fw-bold text-primary">{{number_format(@$total_moneybox, 2)}}</span>
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
                <!--begin::Empty state-->
                <div class="text-center py-10">
                    <div class="symbol symbol-100px mb-5">
                        <div class="symbol-label bg-light-primary">
                            <i class="ki-outline ki-chart-simple fs-2x text-primary"></i>
                        </div>
                    </div>
                    <h3 class="fs-2 fw-bold text-gray-900 mb-2">{{ trans('sw.no_record_found')}}</h3>
                    <div class="fs-6 fw-semibold text-gray-500 mb-6">{{ trans('sw.no_tax_records_found')}}</div>
                </div>
                <!--end::Empty state-->
            @endif
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
@endsection

@section('scripts')
 <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
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

    </script>

@endsection