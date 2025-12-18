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
                <i class="ki-outline ki-user-tick fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_pt_subscription_members_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->

                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportPTSubscriptionMemberPDF', 'exportPTSubscriptionMemberExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download') }}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportPTSubscriptionMemberExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportPTSubscriptionMemberExcel', request()->query())}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export') }}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportPTSubscriptionMemberPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportPTSubscriptionMemberPDF', request()->query())}}" class="menu-link px-3">
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
        <div class="collapse" id="kt_pt_subscription_members_filter_collapse">
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

        @if(count($logs) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_pt_subscription_members_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.name')}}
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="ki-outline ki-list fs-6 me-2"></i>{{ trans('sw.membership')}}
                            </th>
                             <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.amount_remaining')}}
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.joining_date')}}
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-calendar-tick fs-6 me-2"></i>{{ trans('sw.expire_date')}}
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
                        @foreach($logs as $key=> $log)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-50px me-3">
                                            <img alt="avatar" class="rounded-circle" src="{{@$log->member->image}}">
                                        </div>
                                        <div>
                                            <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                                {{ @$log->member->name }}
                                            </div>
                                            <div class="text-muted fs-7">
                                                {{ trans('sw.identification_code') }}: {{ @$log->member->code }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @$log->pt_subscription->name }}</span>
                                </td>
                                 <td>
                                    <span class="fw-bold">{{ number_format(@$log->amount_remaining, 2) }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @\Carbon\Carbon::parse($log->joining_date)->format('Y-m-d') }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @\Carbon\Carbon::parse($log->expire_date)->format('Y-m-d') }}</span>
                                </td>
                                <td>
                                    <span class="badge @if(@$log->status == \Modules\Software\Classes\TypeConstants::Freeze) badge-light-info @elseif(@$log->status == \Modules\Software\Classes\TypeConstants::Active) badge-light-success @elseif(@$log->status == \Modules\Software\Classes\TypeConstants::Expired) badge-light-danger @else badge-light-primary @endif">{!! @$log->statusName !!}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="text-muted fw-bold d-flex align-items-center">
                                            <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                            <span>{{ $log->created_at ? $log->created_at->format('Y-m-d') : $log->updated_at->format('Y-m-d')}}</span>
                                        </div>
                                        <div class="text-muted fs-7 d-flex align-items-center">
                                            <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                            <span>{{ $log->created_at ? $log->created_at->format('h:i a') : $log->updated_at->format('h:i a') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end actions-column">
                                     <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                        @if((in_array('createPTMemberPayAmountRemainingForm', (array)$swUser->permissions) || $swUser->is_super_user) && @$log->member)
                                            <!--begin::Pay-->
                                            <a data-bs-toggle="modal" data-bs-target="#modalPays_{{$log->member->id}}" href="#"
                                               id="{{@$log->id}}" style="cursor: pointer;"
                                               class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm"
                                               title="{{ trans('sw.pay_remaining')}}">
                                                <i class="ki-outline ki-dollar fs-2"></i>
                                            </a>
                                            <!--end::Pay-->
                                        @endif
                                        @if(@$log->id)
                                            <!--begin::Invoice-->
                                            <a href="{{route('sw.showOrderPTSubscription',@$log->id)}}" style="cursor: pointer;"
                                               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                               title="{{ trans('sw.invoice')}}">
                                                <i class="ki-outline ki-document fs-2"></i>
                                            </a>
                                            <!--end::Invoice-->
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!--end::Table-->
            
            @php $processed_members = []; @endphp
            @foreach($logs as $log)
                @if(@$log->member && !in_array($log->member->id, $processed_members))
                    <!-- start model pay -->
                     <div class="modal fade" id="modalPays_{{$log->member->id}}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="fw-bold">{{ trans('sw.total_amount_remaining')}}</h2>
                                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                        <i class="ki-outline ki-cross fs-1"></i>
                                    </div>
                                </div>
                                <div class="modal-body py-10 px-lg-17">
                                    <div class="d-flex flex-column align-items-center mb-5">
                                        <div class="symbol symbol-75px mb-3">
                                            <img alt="avatar" class="rounded-circle" src="{{@$log->member->image}}">
                                        </div>
                                        <h4 class="fw-bold">{{$log->member->name}}</h4>
                                    </div>
                                    <div >
                                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                                            <thead>
                                                <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                                    <th>{{ trans('sw.membership')}}</th>
                                                    <th>{{ trans('sw.amount_remaining')}}</th>
                                                    <th class="text-end">{{ trans('admin.actions')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="fw-semibold text-gray-600">
                                                @php
                                                    $remain_amounts = collect($log->member->pt_member_remain_amount_subscriptions ?? []);
                                                    $remain_amount_subscriptions_check = $remain_amounts->count() > 0;
                                                @endphp
                                                @if($remain_amount_subscriptions_check)
                                                    @foreach($remain_amounts as $member_remain_amount)
                                                    <tr id="tr_pay_{{$member_remain_amount->id}}">
                                                        <td>{{@$member_remain_amount->pt_subscription->name}}</td>
                                                        <td id="td_pay_amount_remaining_{{$member_remain_amount->id}}">{{@number_format($member_remain_amount->amount_remaining, 2)}}</td>
                                                        <td class="text-end">
                                                            <a data-bs-toggle="modal" data-bs-target="#modalPay" href="#"
                                                               id="{{@$member_remain_amount->id}}" style="cursor: pointer;"
                                                               class="btn btn-sm btn-light-primary btn-indigo">
                                                                {{ trans('sw.pay')}}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                                @if($remain_amount_subscriptions_check == false)
                                                    <tr>
                                                        <td colspan="3" class="text-center">{{ trans('admin.no_records')}}</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End model pay -->
                    @php $processed_members[] = $log->member->id; @endphp
                @endif
            @endforeach

            <!--begin::Pagination-->
            <div class="d-flex flex-stack flex-wrap pt-10">
                <div class="fs-6 fw-semibold text-gray-700">
                    {{ trans('sw.showing_entries', [
                        'from' => $logs->firstItem() ?? 0,
                        'to' => $logs->lastItem() ?? 0,
                        'total' => $logs->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $logs->appends($search_query)->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                        <i class="ki-outline ki-user-tick fs-2"></i>
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

<!-- start model pay -->
<div class="modal fade" id="modalPay" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">{{ trans('sw.pay_remaining')}}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-10 px-lg-17">
                <div id="modalPayResult" class="mb-5"></div>
                <form id="form_pay" class="form" action="#">
                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">{{ trans('sw.amount_paid')}}</label>
                            <input name="amount_paid" class="form-control form-control-solid" type="number" id="amount_paid"  step="0.01"
                                   placeholder="{{ trans('sw.enter_amount_paid')}}">
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">{{ trans('sw.payment_type')}}</label>
                            <select class="form-select form-select-solid" name="payment_type" id="payment_type" data-control="select2" data-hide-search="true">
                                @if(isset($payment_types))
                                    @foreach($payment_types as $payment_type)
                                        <option value="{{$payment_type->payment_id}}">{{$payment_type->name}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" id="form_pay_btn" type="submit">
                            <span class="indicator-label">{{ trans('sw.pay')}}</span>
                            <span class="indicator-progress">{{ trans('sw.please_wait')}}...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
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

        $('.btn-indigo').off('click').on('click', function (e) {
            var that = $(this);
            var attr_id = that.attr('id');
            $('#modalPayResult').hide();
            $('#amount_paid').val('');
            $('#pay_id').remove();
            $('#form_pay').append('<input value="' + attr_id + '"  id="pay_id" name="pay_id"  hidden>');
        });

        $(document).on('click', '#form_pay_btn', function (event) {
            event.preventDefault();

            const submitButton = document.getElementById('form_pay_btn');
            submitButton.setAttribute('data-kt-indicator', 'on');
            submitButton.disabled = true;

            let id = $('#pay_id').val();
            let amount_paid = $('#amount_paid').val();
            let payment_type = $('#payment_type').val();
            $('#modalPayResult').show();
            $.ajax({
                url: '{{route('sw.createPTMemberPayAmountRemainingForm')}}',
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {id: id, amount_paid: amount_paid, payment_type: payment_type},
                success: function (response) {
                    if (response == '1') {
                        $('#modalPayResult').html('<div class="alert alert-success">{{ trans('admin.successfully_paid')}}</div>');
                         setTimeout(function () {
                           location.reload();
                        }, 1000);
                    } else {
                        $('#modalPayResult').html('<div class="alert alert-danger">' + response + '</div>');
                    }
                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                },
                complete: function () {
                    submitButton.removeAttribute('data-kt-indicator');
                    submitButton.disabled = false;
                }
            });
        });
    </script>
@endsection

