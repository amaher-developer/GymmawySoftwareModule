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
            min-width: 140px;
            text-align: right;
        }

        .actions-column .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.475rem;
            transition: all 0.15s ease;
        }

        .actions-column .btn-icon:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
        }

        .actions-column .d-flex {
            gap: 0.25rem;
            flex-wrap: nowrap;
        }

        /* Ensure consistent button sizing */
        .actions-column .btn {
            min-width: 32px;
            min-height: 32px;
        }
    </style>
@endsection
@section('page_body')

<!--begin::Store Order Vendor-->
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
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_store_order_vendor_filter_collapse">
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
                            <a href="{{route('sw.exportStoreOrderVendorExcel', $search_query)}}" class="menu-link px-3">
                                <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                {{ trans('sw.excel_export')}}
                            </a>
        </div>
                        <div class="menu-item px-3">
                            <a href="{{route('sw.exportStoreOrderVendorPDF', $search_query)}}" class="menu-link px-3">
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
        <div class="collapse" id="kt_store_order_vendor_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-12">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" id="from_date" value="@php echo @strip_tags($_GET['from']) @endphp" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" name="to" id="to_date" value="@php echo @strip_tags($_GET['to']) @endphp" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                            </div>
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
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_store_order_vendor_table">
                    <thead>
                <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-80px text-nowrap">
                        <i class="ki-outline ki-hash fs-6 me-2"></i>
                        #
                    </th>
                    <th class="min-w-150px text-nowrap">
                        <i class="ki-outline ki-package fs-6 me-2"></i>
                        {{ trans('sw.product')}}
                    </th>
                    <th class="min-w-100px text-nowrap">
                        <i class="ki-outline ki-abstract-25 fs-6 me-2"></i>
                        {{ trans('sw.quantity')}}
                    </th>
                    <th class="min-w-100px text-nowrap">
                        <i class="ki-outline ki-dollar fs-6 me-2"></i>
                        {{ trans('sw.total_price')}}
                    </th>
                    <th class="min-w-100px text-nowrap">
                        <i class="ki-outline ki-information fs-6 me-2"></i>
                        {{ trans('sw.details')}}
                    </th>
                    <th class="min-w-150px text-nowrap">
                        <i class="ki-outline ki-calendar fs-6 me-2"></i>
                        {{ trans('sw.date')}}
                    </th>
                    <th class="text-end min-w-100px text-nowrap actions-column">
                        <i class="ki-outline ki-setting-2 fs-6 me-2"></i>
                        {{ trans('admin.actions')}}
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
                                                                </div>
                                                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-3">
                                    <div class="symbol-label fs-3 bg-light-info text-info">
                                        <i class="ki-outline ki-package fs-2"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 text-hover-primary mb-1">{{ @$order->product->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="badge badge-light-warning fs-7 fw-bold">
                                {{ $order->quantity }}
                            </div>
                        </td>
                        <td>
                            <div class="badge badge-light-success fs-7 fw-bold">
                                {{ $order->amount }}
                            </div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" 
                                    data-bs-toggle="modal" data-bs-target="#modal_store_order_{{$order->id}}" 
                                    title="{{ trans('sw.details')}}">
                                <i class="ki-outline ki-information fs-2"></i>
                            </button>
                        </td>
                        <td class="text-end pe-0">
                            <span class="text-muted fw-semibold text-muted d-block fs-7">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>
                                {{ $order->created_at->format('Y-m-d H:i') }}
                            </span>
                        </td>
                        <td class="text-end actions-column">
                            <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                <a href="{{route('sw.showStoreOrderVendor',$order->id)}}"
                                   class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" 
                                   title="{{ trans('admin.view')}}">
                                    <i class="ki-outline ki-eye fs-2"></i>
                                </a>
                                {{-- <a title="{{ trans('sw.disable_without_refund')}}"
                                   data-swal-text="{{ trans('sw.disable_without_refund')}}"
                                   href="{{route('sw.deleteStoreOrderVendor',$order->id).'?refund=0'}}"
                                   class="confirm_delete btn btn-icon btn-bg-light btn-active-color-secondary btn-sm"
                                   title="{{ trans('sw.disable_without_refund')}}">
                                    <i class="ki-outline ki-trash fs-2"></i>
                                </a> --}}
                                <a title="{{ trans('sw.disable_with_refund', ['amount' => (float)$order->amount])}}"
                                   data-swal-text="{{ trans('sw.disable_with_refund', ['amount' => (float)$order->amount])}}"
                                    data-swal-amount="{{@(float)$order->amount}}"
                                           href="{{route('sw.deleteStoreOrderVendor',$order->id).'?refund=1&total_amount='.@(float)$order->amount}}"
                                           class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="{{ trans('sw.disable_with_refund', ['amount' => (float)$order->amount])}}">
                                            <i class="ki-outline ki-trash fs-2"></i>

                                </a>
                            </div>
                        </td>
                        </tr>

                    <!-- Modal for vendor details -->
                    <div id="modal_store_order_{{$order->id}}" class="modal fade" tabindex="-1" aria-labelledby="modal_store_order_{{$order->id}}_label" aria-hidden="true">
                        <div class="modal-dialog">
                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modal_store_order_{{$order->id}}_label">{{ trans('sw.vendor')}}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="d-flex flex-column gap-5">
                                        @if($order->vendor_name)
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold text-gray-600 me-3" style="width: 120px;">{{ trans('sw.vendor_name')}}:</div>
                                                <div class="text-gray-800">{{@$order->vendor_name}}</div>
                                            </div>
                                        @endif
                                        @if($order->vendor_phone)
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold text-gray-600 me-3" style="width: 120px;">{{ trans('sw.vendor_phone')}}:</div>
                                                <div class="text-gray-800">{{@$order->vendor_phone}}</div>
                                            </div>
                                        @endif
                                        @if($order->vendor_address)
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold text-gray-600 me-3" style="width: 120px;">{{ trans('sw.vendor_address')}}:</div>
                                                <div class="text-gray-800">{{@$order->vendor_address}}</div>
                                            </div>
                                        @endif
                                        @if($order->notes)
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold text-gray-600 me-3" style="width: 120px;">{{ trans('sw.notes')}}:</div>
                                                <div class="text-gray-800">{{@$order->notes}}</div>
                                            </div>
                                        @endif
                                        @if(!$order->vendor_name && !$order->vendor_phone && !$order->vendor_address && !$order->notes )
                                            <div class="alert alert-info text-center">{{ trans('admin.no_records')}}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('admin.close')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
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
                        <i class="ki-outline ki-package fs-2x text-success"></i>
                    </div>
                </div>
                <div class="fs-1 fw-bold text-gray-900 mb-3">{{ trans('sw.no_data_found')}}</div>
                <div class="fs-6 text-gray-600">{{ trans('sw.no_data_found_desc')}}</div>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Store Order Vendor-->
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
            $("#form_filter").submit();
        });

        jQuery(document).ready(function () {
            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true,
                orientation: 'bottom auto'
            });
            
            $('button[type="reset"]').on('click', function() {
                setTimeout(() => {
                    $(this).closest('form').find('select').trigger('change');
                }, 100);
            });

            // Initialize tooltips for action buttons
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

@endsection