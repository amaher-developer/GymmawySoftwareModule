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
        .avatar-md {
            width: 48px !important;
            height: 48px !important;
            font-size: 24px !important;
        }

        .rounded-circle {
            border-radius: 50% !important;
        }

        .userlist-table .table th, .userlist-table .table td {
            padding: 0.75rem;
            vertical-align: middle;
            display: table-cell;
        }

        .userlist-table {
            overflow-x: scroll;
        }

        .table-vcenter {
            table-layout: fixed;
            overflow-x: auto !important;
            width: 100% !important;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive table {
            min-width: 800px;
        }

        @media (max-width: 767px) {
            .table-vcenter {
                display: block !important;
            }
            
            .table-responsive {
                border: none;
            }
            
            .table-responsive table {
                min-width: 1000px;
            }
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

<!--begin::Report-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-shop fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.store')}}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_store_orders_report_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->
            </div>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        @php $storeOrderModals = []; @endphp
        <!--begin::Filter-->
        <div class="collapse" id="kt_store_orders_report_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-4">
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
        <!--end::Total count-->

        @if(count($orders) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_store_orders_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-barcode fs-6 me-2"></i>{{ trans('sw.order_number')}}
                            </th>
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.customer')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-phone fs-6 me-2"></i>{{ trans('sw.phone')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.total_amount')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-status fs-6 me-2"></i>{{ trans('sw.status')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date')}}
                            </th>
                            <th class="text-end actions-column">
                                <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($orders as $key=> $order)
                            @php
                                $member = $order->member ?? null;
                                $customerImage = $member && !empty($member->image) ? $member->image : asset('resources/assets/front/img/blank-image.svg');
                                $customerName = $member->name ?? ($order->customer_name ?? 'N/A');
                                $customerPhone = $member->phone ?? ($order->customer_phone ?? trans('sw.not_specified'));
                                $orderProducts = collect($order->order_product ?? []);
                                $amountPaid = (float) ($order->amount_paid ?? 0);
                                $amountRemaining = (float) ($order->amount_remaining ?? 0);
                                $amountBeforeDiscount = (float) ($order->amount_before_discount ?? 0);
                                $discountValue = (float) ($order->discount_value ?? 0);
                                $vatValue = (float) ($order->vat ?? 0);
                                $isPaid = $amountRemaining <= 0.0001;
                                $statusClass = $isPaid ? 'badge-light-success' : 'badge-light-warning';
                                $statusLabel = trans($isPaid ? 'sw.completed' : 'sw.pending');
                                $paymentTypeName = optional($order->pay_type)->name ?? trans('sw.not_specified');
                                $orderDate = $order->created_at ? Carbon\Carbon::parse($order->created_at) : ($order->updated_at ? Carbon\Carbon::parse($order->updated_at) : null);
                                $orderId = $order->id ?? null;
                                $invoiceLink = $orderId ? route('sw.showStoreOrder', $orderId) : null;
                                $loyaltyRedemption = $order->loyaltyRedemption ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-bold">#{{ $orderId ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-50px me-3">
                                            <img alt="avatar" class="rounded-circle" src="{{ $customerImage }}">
                                        </div>
                                        <div>
                                            <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                                {{ $customerName }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $customerPhone }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ number_format($amountPaid, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="text-muted fw-bold d-flex align-items-center">
                                            <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                            <span>{{ $orderDate ? $orderDate->format('Y-m-d') : '—' }}</span>
                                        </div>
                                        <div class="text-muted fs-7 d-flex align-items-center">
                                            <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                            <span>{{ $orderDate ? $orderDate->format('h:i a') : '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end actions-column">
                                    <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                        <!--begin::View-->
                                        <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                                type="button"
                                                data-bs-toggle="modal"
                                                data-bs-target="#storeOrderModal_{{ $orderId }}"
                                                title="{{ trans('admin.view')}}">
                                            <i class="ki-outline ki-eye fs-2"></i>
                                        </button>
                                        <!--end::View-->
                                        @if($invoiceLink)
                                            <a href="{{ $invoiceLink }}"
                                               target="_blank"
                                               class="btn btn-icon btn-bg-light btn-active-color-success btn-sm"
                                               title="{{ trans('sw.invoice') }}">
                                                <i class="ki-outline ki-printer fs-2"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @php
                                ob_start();
                            @endphp
                            <div class="modal fade" id="storeOrderModal_{{ $orderId }}" tabindex="-1" aria-labelledby="storeOrderModalLabel_{{ $orderId }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title d-flex align-items-center gap-2" id="storeOrderModalLabel_{{ $orderId }}">
                                                <i class="ki-outline ki-shop fs-2 text-primary"></i>
                                                <span>{{ trans('sw.order_number') }} #{{ $orderId }}</span>
                                            </h5>
                                            <button type="button" class="btn btn-sm btn-icon btn-light" data-bs-dismiss="modal" aria-label="{{ trans('sw.close') }}">
                                                <i class="ki-outline ki-cross fs-2"></i>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-4 mb-6">
                                                <div class="col-md-4">
                                                    <div class="card border-light shadow-none h-100">
                                                        <div class="card-body">
                                                            <h6 class="fw-semibold text-gray-700 mb-3">{{ trans('sw.customer') }}</h6>
                                                            <div class="d-flex align-items-center">
                                                                <div class="symbol symbol-45px me-3">
                                                                    <img src="{{ $customerImage }}" alt="avatar" class="rounded-circle">
                                                                </div>
                                                                <div class="d-flex flex-column">
                                                                    <span class="fw-bold text-gray-900">{{ $customerName }}</span>
                                                                    <span class="text-muted fs-7">{{ $customerPhone }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="card border-light shadow-none h-100">
                                                        <div class="card-body">
                                                            <h6 class="fw-semibold text-gray-700 mb-3">{{ trans('sw.invoice_details') }}</h6>
                                                            <div class="row g-3">
                                                                <div class="col-sm-6 col-lg-4">
                                                                    <div class="d-flex flex-column">
                                                                        <span class="text-muted fs-7">{{ trans('sw.date') }}</span>
                                                                        <span class="fw-semibold text-gray-900">{{ $orderDate ? $orderDate->format('Y-m-d') : '—' }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6 col-lg-4">
                                                                    <div class="d-flex flex-column">
                                                                        <span class="text-muted fs-7">{{ trans('sw.time') }}</span>
                                                                        <span class="fw-semibold text-gray-900">{{ $orderDate ? $orderDate->format('h:i a') : '—' }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6 col-lg-4">
                                                                    <div class="d-flex flex-column">
                                                                        <span class="text-muted fs-7">{{ trans('sw.payment_type') }}</span>
                                                                        <span class="fw-semibold text-gray-900">{{ $paymentTypeName }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6 col-lg-4">
                                                                    <div class="d-flex flex-column">
                                                                        <span class="text-muted fs-7">{{ trans('sw.amount_before_discount') }}</span>
                                                                        <span class="fw-semibold text-gray-900">{{ number_format($amountBeforeDiscount, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6 col-lg-4">
                                                                    <div class="d-flex flex-column">
                                                                        <span class="text-muted fs-7">{{ trans('sw.discount') }}</span>
                                                                        <span class="fw-semibold text-gray-900">{{ number_format($discountValue, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6 col-lg-4">
                                                                    <div class="d-flex flex-column">
                                                                        <span class="text-muted fs-7">{{ trans('sw.vat') }}</span>
                                                                        <span class="fw-semibold text-gray-900">{{ number_format($vatValue, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6 col-lg-4">
                                                                    <div class="d-flex flex-column">
                                                                        <span class="text-muted fs-7">{{ trans('sw.amount_paid') }}</span>
                                                                        <span class="fw-semibold text-gray-900">{{ number_format($amountPaid, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6 col-lg-4">
                                                                    <div class="d-flex flex-column">
                                                                        <span class="text-muted fs-7">{{ trans('sw.amount_remaining') }}</span>
                                                                        <span class="fw-semibold text-gray-900">{{ number_format(max($amountRemaining, 0), 2) }}</span>
                                                                    </div>
                                                                </div>
                                                                @if($loyaltyRedemption)
                                                                    <div class="col-sm-6 col-lg-4">
                                                                        <div class="d-flex flex-column">
                                                                            <span class="text-muted fs-7">{{ trans('sw.loyalty_points') }}</span>
                                                                            <span class="fw-semibold text-gray-900">{{ number_format($loyaltyRedemption->points ?? 0) }}</span>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border shadow-none">
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped align-middle mb-0">
                                                            <thead class="bg-light">
                                                                <tr class="fw-semibold text-gray-600 text-uppercase fs-7">
                                                                    <th>{{ trans('sw.product') }}</th>
                                                                    <th class="text-center">{{ trans('sw.quantity') }}</th>
                                                                    <th class="text-center">{{ trans('sw.price') }}</th>
                                                                    <th class="text-end">{{ trans('sw.total') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if($orderProducts->count() > 0)
                                                                    @foreach($orderProducts as $item)
                                                                        @php
                                                                            $productName = optional($item->product)->name ?? trans('sw.not_specified');
                                                                            $quantity = (float) ($item->quantity ?? 0);
                                                                            $unitPrice = (float) ($item->price ?? 0);
                                                                            $lineTotal = $unitPrice * $quantity;
                                                                        @endphp
                                                                        <tr>
                                                                            <td>{{ $productName }}</td>
                                                                            <td class="text-center">{{ number_format($quantity, 2) }}</td>
                                                                            <td class="text-center">{{ number_format($unitPrice, 2) }}</td>
                                                                            <td class="text-end">{{ number_format($lineTotal, 2) }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                @else
                                                                    <tr>
                                                                        <td colspan="4" class="text-center text-muted py-6">{{ trans('admin.no_records') }}</td>
                                                                    </tr>
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                <i class="ki-outline ki-cross fs-4 me-1"></i>{{ trans('sw.close') }}
                                            </button>
                                            @if($invoiceLink)
                                                <a href="{{ $invoiceLink }}" target="_blank" class="btn btn-primary">
                                                    <i class="ki-outline ki-printer fs-4 me-1"></i>{{ trans('sw.view_invoice') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                $storeOrderModals[] = ob_get_clean();
                            @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!--end::Table-->
            {!! implode('', $storeOrderModals) !!}
            
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
        @else
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                        <i class="ki-outline ki-shop fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
            </div>
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Report-->

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