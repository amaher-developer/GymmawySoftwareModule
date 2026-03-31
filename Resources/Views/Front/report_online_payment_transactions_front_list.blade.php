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

        /* Stats cards */
        .stat-card {
            border-radius: 8px;
            padding: 12px 16px;
            text-align: center;
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
    </style>
@endsection

@section('page_body')

<!--begin::Report-->
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
            <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
                <!--begin::Show All Toggle-->
                @if($show_all)
                    <a href="{{ route('sw.reportOnlinePaymentTransactionList', array_diff_key(request()->query(), ['show_all' => ''])) }}" class="btn btn-sm btn-flex btn-light-warning">
                        <i class="ki-outline ki-eye fs-6"></i>
                        {{ trans('sw.show_all_transactions')}}
                    </a>
                @else
                    <a href="{{ route('sw.reportOnlinePaymentTransactionList', array_merge(request()->query(), ['show_all' => 1])) }}" class="btn btn-sm btn-flex btn-light-success">
                        <i class="ki-outline ki-check-circle fs-6"></i>
                        {{ trans('sw.show_successful_only')}}
                    </a>
                @endif
                <!--end::Show All Toggle-->

                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_online_payment_transactions_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->

                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportOnlinePaymentPDF', 'exportOnlinePaymentExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download') }}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportOnlinePaymentExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportOnlinePaymentExcel', request()->query())}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export') }}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportOnlinePaymentPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportOnlinePaymentPDF', request()->query())}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.pdf_export') }}
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
        <div class="collapse" id="kt_online_payment_transactions_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-8">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" id="from_date" value="@php echo @strip_tags($_GET['from']) @endphp" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" name="to" id="to_date" value="@php echo @strip_tags($_GET['to']) @endphp" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.payment_gateway')}}</label>
                            <select name="transaction" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.all_gateways') }}</option>
                                <option value="4"  @selected(@strip_tags($_GET['transaction'] ?? '') == '4')>Tabby</option>
                                <option value="5"  @selected(@strip_tags($_GET['transaction'] ?? '') == '5')>Paymob</option>
                                <option value="6"  @selected(@strip_tags($_GET['transaction'] ?? '') == '6')>Tamara</option>
                                <option value="8"  @selected(@strip_tags($_GET['transaction'] ?? '') == '8')>PayTabs</option>
                                <option value="2"  @selected(@strip_tags($_GET['transaction'] ?? '') == '2')>PayPal</option>
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

        <!--begin::Stats-->
        <div class="mb-6">
            <!--begin::Summary row-->
            <div class="row g-4 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card bg-light-primary">
                        <div class="fs-7 fw-semibold text-primary mb-1">{{ trans('admin.total_count') }}</div>
                        <div class="fs-2 fw-bold text-primary">{{ $stats['total_count'] }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card bg-light-success">
                        <div class="fs-7 fw-semibold text-success mb-1">{{ trans('sw.total_amount') }}</div>
                        <div class="fs-2 fw-bold text-success">{{ number_format($stats['total_amount'], 2) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card bg-light-success">
                        <div class="fs-7 fw-semibold text-success mb-1">{{ trans('sw.successful') }}</div>
                        <div class="fs-3 fw-bold text-success">{{ $stats['by_status'][1]['count'] }} <small class="fs-7">/ {{ number_format($stats['by_status'][1]['amount'], 2) }}</small></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card bg-light-warning">
                        <div class="fs-7 fw-semibold text-warning mb-1">{{ trans('sw.pending') }}</div>
                        <div class="fs-3 fw-bold text-warning">{{ $stats['by_status'][0]['count'] }} <small class="fs-7">/ {{ number_format($stats['by_status'][0]['amount'], 2) }}</small></div>
                    </div>
                </div>
            </div>
            <!--begin::Status + Gateway row-->
            <div class="row g-4">
                <div class="col-6 col-md-2">
                    <div class="stat-card bg-light-danger">
                        <div class="fs-7 fw-semibold text-danger mb-1">{{ trans('sw.declined') }}</div>
                        <div class="fs-3 fw-bold text-danger">{{ $stats['by_status'][2]['count'] }} <small class="fs-7">/ {{ number_format($stats['by_status'][2]['amount'], 2) }}</small></div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="stat-card bg-light-secondary">
                        <div class="fs-7 fw-semibold text-gray-600 mb-1">{{ trans('sw.cancelled') }}</div>
                        <div class="fs-3 fw-bold text-gray-700">{{ $stats['by_status'][3]['count'] }} <small class="fs-7">/ {{ number_format($stats['by_status'][3]['amount'], 2) }}</small></div>
                    </div>
                </div>
                @foreach($stats['by_gateway'] as $gateway)
                    @if($gateway['count'] > 0)
                        <div class="col-6 col-md-2">
                            <div class="stat-card bg-light-info">
                                <div class="fs-7 fw-semibold text-info mb-1">{{ $gateway['label'] }}</div>
                                <div class="fs-3 fw-bold text-info">{{ $gateway['count'] }} <small class="fs-7">/ {{ number_format($gateway['amount'], 2) }}</small></div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        <!--end::Stats-->

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

        @if(count($orders) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_online_payment_transactions_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-50px text-nowrap"></th>
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.name')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-phone fs-6 me-2"></i>{{ trans('sw.phone')}}
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="ki-outline ki-list fs-6 me-2"></i>{{ trans('sw.membership')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.amount_paid')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-status fs-6 me-2"></i>{{ trans('sw.status')}}
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-wallet fs-6 me-2"></i>{{ trans('sw.payment_gateway')}}
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-abstract-26 fs-6 me-2"></i>{{ trans('sw.payment_channel')}}
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
                            <tr>
                                <td>
                                    <div class="symbol symbol-50px">
                                        <img alt="avatar" class="rounded-circle" src="{{@$order->member->image ?? @$order->image}}">
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @$order->name }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @$order->phone }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @$order->subscription->name }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold">{{ @$order->amount }}</span>
                                </td>
                                <td>
                                    @if($order->status == 1)
                                        <span class="badge badge-light-success">{{ trans('sw.successful')}}</span>
                                    @elseif($order->status == 0)
                                        <span class="badge badge-light-warning">{{ trans('sw.pending')}}</span>
                                    @elseif($order->status == 3)
                                        <span class="badge badge-light-secondary">{{ trans('sw.cancelled')}}</span>
                                    @else
                                        <span class="badge badge-light-danger">{{ trans('sw.declined')}}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-light-primary">{{ $order->payment_gateway_name }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light-info">{{ $order->payment_channel_name }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="text-muted fw-bold d-flex align-items-center">
                                            <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                            <span>{{ $order->created_at ? $order->created_at->format('Y-m-d') : $order->updated_at->format('Y-m-d')}}</span>
                                        </div>
                                        <div class="text-muted fs-7 d-flex align-items-center">
                                            <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                            <span>{{ $order->created_at ? $order->created_at->format('h:i a') : $order->updated_at->format('h:i a') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end actions-column">
                                    <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                        @if($order->member_subscription_id)
                                            <!--begin::Invoice-->
                                            <a href="{{route('sw.showOrderSubscription',$order->member_subscription_id)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                               title="{{ trans('sw.invoice')}}">
                                                <i class="ki-outline ki-document fs-2"></i>
                                            </a>
                                            <!--end::Invoice-->
                                        @endif
                                        <!--begin::Change Status-->
                                        <!-- <button type="button"
                                                class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm btn-change-status"
                                                data-id="{{ $order->id }}"
                                                data-status="{{ $order->status }}"
                                                data-url="{{ route('sw.updateOnlinePaymentStatus', $order->id) }}"
                                                title="{{ trans('sw.change_status') }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#kt_modal_change_payment_status">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </button> -->
                                        <!--end::Change Status-->
                                    </div>
                                </td>
                            </tr>
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
                        <i class="ki-outline ki-chart-line fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Report-->

<!--begin::Change Status Modal-->
<div class="modal fade" id="kt_modal_change_payment_status" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">{{ trans('sw.change_status') }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-6">
                <div class="mb-5">
                    <label class="form-label fw-semibold">{{ trans('sw.status') }}</label>
                    <select id="modal_payment_status" class="form-select form-select-solid">
                        <option value="1">{{ trans('sw.successful') }}</option>
                        <option value="0">{{ trans('sw.pending') }}</option>
                        <option value="2">{{ trans('sw.declined') }}</option>
                        <option value="3">{{ trans('sw.cancelled') }}</option>
                    </select>
                </div>
                <div class="alert alert-warning d-none" id="modal_success_warning">
                    <i class="ki-outline ki-information-5 fs-4 me-2"></i>
                    {{ trans('sw.online_payment_success_warning') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('admin.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="btn_confirm_status_change">
                    <span class="indicator-label">{{ trans('sw.update_status') }}</span>
                    <span class="indicator-progress d-none">
                        <span class="spinner-border spinner-border-sm align-middle me-2"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
<!--end::Change Status Modal-->

@endsection

@section('scripts')
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    @parent
    <script>
        var currentInvoiceId = null;
        var currentUpdateUrl = null;

        // Populate modal when change-status button is clicked
        $(document).on('click', '.btn-change-status', function () {
            currentInvoiceId = $(this).data('id');
            currentUpdateUrl = $(this).data('url');
            var currentStatus = $(this).data('status');
            $('#modal_payment_status').val(currentStatus);
            toggleSuccessWarning(currentStatus);
        });

        $('#modal_payment_status').on('change', function () {
            toggleSuccessWarning($(this).val());
        });

        function toggleSuccessWarning(status) {
            if (parseInt(status) === 1) {
                $('#modal_success_warning').removeClass('d-none');
            } else {
                $('#modal_success_warning').addClass('d-none');
            }
        }

        $('#btn_confirm_status_change').on('click', function () {
            var $btn = $(this);
            $btn.find('.indicator-label').addClass('d-none');
            $btn.find('.indicator-progress').removeClass('d-none');
            $btn.prop('disabled', true);

            $.ajax({
                url: currentUpdateUrl,
                type: 'POST',
                data: {
                    status: $('#modal_payment_status').val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#kt_modal_change_payment_status').modal('hide');
                    location.reload();
                },
                error: function () {
                    $btn.find('.indicator-label').removeClass('d-none');
                    $btn.find('.indicator-progress').addClass('d-none');
                    $btn.prop('disabled', false);
                    alert('{{ trans("admin.operation_failed") }}');
                }
            });
        });

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
        });
    </script>
@endsection
