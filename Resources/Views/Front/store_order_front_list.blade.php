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
        /* Actions column styling */
        .actions-column {
            min-width: 200px !important;
            white-space: nowrap;
        }

        .actions-column .d-flex {
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .actions-column .btn {
            margin: 0;
            padding: 0.375rem;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 1200px) {
            .actions-column {
                min-width: 150px !important;
            }
        }

        @media (max-width: 992px) {
            .actions-column {
                min-width: 120px !important;
            }
        }
    </style>
@endsection
@section('page_body')

<!--begin::Store Orders-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-shop fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_store_orders_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->
                <!--begin::Export-->
                <div class="m-0">
                    <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <i class="ki-outline ki-exit-down fs-6"></i>
                        {{ trans('sw.download')}}
                    </button>
                    <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                        <div class="menu-item px-3">
                            <a href="{{route('sw.exportStoreOrderExcel', $search_query)}}" class="menu-link px-3">
                                <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                {{ trans('sw.excel_export')}}
                            </a>
                        </div>
                        <div class="menu-item px-3">
                            <a href="{{route('sw.exportStoreOrderPDF', $search_query)}}" class="menu-link px-3">
                                <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                {{ trans('sw.pdf_export')}}
                            </a>
                        </div>
                    </div>
                </div>
                <!--end::Export-->
            </div>
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Filter-->
        <div class="collapse" id="kt_store_orders_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-4">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" id="from_date" value="@php echo @strip_tags($_GET['from']) ? \Carbon\Carbon::parse($_GET['from'])->format('Y-m-d') : '' @endphp" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" name="to" id="to_date" value="@php echo @strip_tags($_GET['to']) ? \Carbon\Carbon::parse($_GET['to'])->format('Y-m-d') : '' @endphp" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="re" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" id="reset_filter">{{ trans('admin.reset')}}</button>
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
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="{{ request('search') }}" placeholder="{{ trans('sw.search_on')}}">
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
                <span class="fs-2 fw-bold text-primary">{{ count($orders) }}</span>
            </div>
        </div>
        <!--end::Total count-->

        @if(count($orders) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_store_orders_table">
                    <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-hash fs-6 me-2"></i>#
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.price')}}
                        </th>
                        <th class="min-w-200px text-nowrap">
                            <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.member')}}
                        </th>
                        <th class="min-w-150px text-nowrap">
                            <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date')}}
                        </th>
                        <th class="text-end actions-column">
                            <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                        </th>
                    </tr>
                    </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($orders as $key=> $order)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-50px me-3">
                                        <div class="symbol-label fs-3 bg-light-primary text-primary">
                                            <i class="ki-outline ki-hash fs-2"></i>
                                        </div>
                                    </div>
                                    <!--end::Avatar-->
                                    <div>
                                        <!--begin::Title-->
                                        <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            #{{ $order->id }}
                                        </div>
                                        <!--end::Title-->
                                        <!--begin::Details Button-->
                                        <button type="button" class="btn btn-sm btn-light-info mt-1" data-toggle="modal"
                                                data-target="#modal_store_order_{{$order->id}}">
                                            <i class="ki-outline ki-information fs-2"></i> {{ trans('sw.details')}}
                                        </button>
                                        <!--end::Details Button-->
                                        @if(@$order->notes)
                                            <!--begin::Notes Button-->
                                            <button type="button" class="btn btn-sm btn-light-warning mt-1" data-target="#store_notes_{{$order->id}}" data-toggle="modal">
                                                <i class="ki-outline ki-note fs-2"></i> {{ trans('sw.notes')}}
                                            </button>
                                            <!--end::Notes Button-->
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold text-success">{{ number_format($order->amount_paid, 2) }}</span>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ $order->member?->name ?? trans('sw.guest') }}</span>
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
                            <td class="text-end actions-column">
                                <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                    <!--begin::View-->
                                    <a href="{{route('sw.showStoreOrder',$order->id)}}"
                                       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('admin.view')}}">
                                        <i class="ki-outline ki-eye fs-2"></i>
                                    </a>
                                    <!--end::View-->
                                    
                                    @if(in_array('deleteStoreOrder', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Delete Without Refund-->
                                        {{-- <a title="{{ trans('sw.disable_without_refund')}}"
                                           data-swal-text="{{ trans('sw.disable_without_refund')}}"
                                           href="{{route('sw.deleteStoreOrder',$order->id).'?refund=0'}}"
                                           class="confirm_delete btn btn-icon btn-bg-light btn-active-color-secondary btn-sm" title="{{ trans('sw.disable_without_refund')}}">
                                            <i class="ki-outline ki-trash fs-2"></i>
                                        </a> --}}
                                        <!--end::Delete Without Refund-->
                                        
                                        <!--begin::Delete With Refund-->
                                        <a title="{{ trans('sw.disable_with_refund', ['amount' => $order->amount_paid])}}"
                                           data-swal-text="{{ trans('sw.disable_with_refund', ['amount' => $order->amount_paid])}}"
                                           data-swal-amount="{{@$order->amount_paid}}"
                                           href="{{route('sw.deleteStoreOrder',$order->id).'?refund=1&total_amount='.@$order->amount_paid}}"
                                           class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="{{ trans('sw.disable_with_refund', ['amount' => $order->amount_paid])}}">
                                            <i class="ki-outline ki-trash fs-2"></i>
                                        </a>
                                        <!--end::Delete With Refund-->
                                    @endif
                                </div>
                            </td>
                        </tr>

                                @if(@$order->notes)
                            <!--begin::Notes Modal-->
                                    <div class="modal" id="store_notes_{{$order->id}}">
                                <div class="modal-dialog" role="document">
                                            <div class="modal-content modal-content-demo">
                                                <div class="modal-header">
                                                    <h6 class="modal-title">{{ trans('sw.notes')}}</h6>
                                                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                                            aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    {{@$order->notes}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            <!--end::Notes Modal-->
                                @endif

                    @endforeach
                    </tbody>
                </table>
            </div>
            <!--end::Table-->
            
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
                        <i class="ki-outline ki-shop fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Store Orders-->

<!--begin::Order Details Modals-->
@foreach($orders as $order)
<!--begin::Order Details Modal-->
<div class="modal" id="modal_store_order_{{$order->id}}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="ki-outline ki-information fs-2 me-2"></i>{{ trans('sw.store_products')}}
                </h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-3">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-shop fs-2x text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fs-6 fw-bold text-gray-900">Order #{{ $order->id }}</div>
                                    <div class="fs-7 text-muted">{{ $order->created_at->format('Y-m-d h:i A') }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fs-4 fw-bold text-success">{{ number_format($order->amount_paid, 2) }} {{ trans('sw.app_currency') }}</div>
                                <div class="fs-7 text-muted">{{ trans('sw.total_amount') }}</div>
                            </div>
                        </div>
                        
                        <div class="separator border-gray-200 mb-4"></div>
                        
                        <!-- Order Items Table -->
                        <div class="table-responsive">
                            <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th class="min-w-50px">#</th>
                                        <th class="min-w-150px">{{ trans('sw.store_product') }}</th>
                                        <th class="min-w-80px text-end">{{ trans('sw.quantity') }}</th>
                                        <th class="min-w-100px text-end">{{ trans('sw.price_per_unit') }}</th>
                                        <th class="min-w-100px text-end">{{ trans('sw.total_price') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($order->products) && count($order->products) > 0)
                                        @foreach($order->products as $i => $product)
                                        <tr>
                                            <td class="fw-bold text-gray-600">{{ $i + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-40px me-3">
                                                        <div class="symbol-label bg-light-info">
                                                            <i class="ki-outline ki-box fs-2 text-info"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-gray-900">
                                                            {{ $product['details']['name'] ?? $product['name'] ?? 'Product #' . ($i + 1) }}
                                                        </div>
                                                        @if(isset($product['details']['description']) && $product['details']['description'])
                                                            <div class="fs-7 text-muted">{{ $product['details']['description'] }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge badge-light-primary">{{ $product['quantity'] }}</span>
                                            </td>
                                            <td class="text-end fw-bold text-gray-900">
                                                {{ number_format($product['price'], 2) }} {{ trans('sw.app_currency') }}
                                            </td>
                                            <td class="text-end fw-bold text-success">
                                                {{ number_format($product['price'] * $product['quantity'], 2) }} {{ trans('sw.app_currency') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center py-8">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="ki-outline ki-information fs-2x text-muted mb-3"></i>
                                                    <div class="fw-bold text-muted">{{ trans('sw.store_order_p_not_found_msg', ['price'=> number_format($order->amount_paid, 2)]) }}</div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        
                        @if(isset($order->products) && count($order->products) > 0)
                        <!-- Order Summary -->
                        <div class="separator border-gray-200 my-4"></div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="fs-7 text-muted">{{ trans('sw.customer') }}:</div>
                                <div class="fw-bold text-gray-900">{{ $order->member?->name ?? trans('sw.guest') }}</div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="fs-7 text-muted">{{ trans('sw.payment_type') }}:</div>
                                <div class="fw-bold text-gray-900">@php
                                    $paymentType = \Modules\Software\Models\GymPaymentType::where('payment_id', $order->payment_type)->first();
                                    echo $paymentType ? ($lang == 'ar' ? $paymentType->name_ar : $paymentType->name_en) : '-';
                                @endphp</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" type="button">
                    {{ trans('sw.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!--end::Order Details Modal-->
@endforeach
<!--end::Order Details Modals-->

@endsection

@section('scripts')
    @parent
    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
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
            $("#filter_form").submit();
        });

        jQuery(document).ready(function () {
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
