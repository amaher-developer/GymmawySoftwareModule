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

        .glyphicon {
            font-size: 20px;
            vertical-align: sub;
        }
        select:first-child  {
            color: #8e8e8e;
        }
        option:first-child  {
            color: #8e8e8e;
        }
        option:not(:first-child)  {
            color: #555;
        }
        .card_img {
            margin: 10px;
            object-fit: cover;
            border-radius: 50% !important;
            -webkit-border-radius: 50%;
            border-radius: 50%;
            -webkit-transition: -webkit-box-shadow 0.3s ease;
            transition: box-shadow 0.3s ease;
            -webkit-box-shadow: 0px 0px 0px 5px;
            box-shadow: 0px 0px 0px 5px;
            color: #f44336c9;
        }

        .card_img_success {
            color: rgba(76, 175, 80, 0.72) !important;
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
                <i class="ki-outline ki-user-cross fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_expire_members_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->

                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportExpireMemberPDF', 'exportExpireMemberExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download')}}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportExpireMemberExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportExpireMemberExcel', $search_query)}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export')}}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportExpireMemberPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportExpireMemberPDF', $search_query)}}" class="menu-link px-3">
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
        <div class="collapse" id="kt_expire_members_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" id="from_date" value="@php echo @strip_tags($_GET['from']) @endphp" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" name="to" id="to_date" value="@php echo @strip_tags($_GET['to']) @endphp" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.status_now')}}</label>
                            <select name="status_now" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.status_now')}}...</option>
                                <option value="{{\Modules\Software\Classes\TypeConstants::Active}}" @if(isset($_GET['status_now']) && ((request('status_now') != "") && (request('status_now') == \Modules\Software\Classes\TypeConstants::Active))) selected="" @endif>{{ trans('sw.active')}}</option>
                                <option value="{{\Modules\Software\Classes\TypeConstants::Freeze}}" @if(request('status_now') == \Modules\Software\Classes\TypeConstants::Freeze) selected="" @endif>{{ trans('sw.frozen')}}</option>
                                <option value="{{\Modules\Software\Classes\TypeConstants::Expired}}" @if(request('status_now') == \Modules\Software\Classes\TypeConstants::Expired) selected="" @endif>{{ trans('sw.expire')}}</option>
                                <option value="{{\Modules\Software\Classes\TypeConstants::Coming}}" @if(request('status_now') == \Modules\Software\Classes\TypeConstants::Coming) selected="" @endif>{{ trans('sw.coming')}}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.memberships')}}</label>
                            <select name="subscription" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.memberships')}}...</option>
                                @foreach($subscriptions as $subscription)
                                    <option value="{{$subscription->id}}" @if(request('subscription') == $subscription->id) selected="" @endif>{{$subscription->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.remaining_status')}}</label>
                            <select name="remaining_status" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.choose_amount_remaining_status')}}...</option>
                                <option value="{{\Modules\Software\Classes\TypeConstants::AMOUNT_REMAINING_STATUS_TURE}}" @if(request('remaining_status') == \Modules\Software\Classes\TypeConstants::AMOUNT_REMAINING_STATUS_TURE) selected="" @endif>{{ trans('sw.amount_remaining_status_true')}}</option>
                                <option value="{{\Modules\Software\Classes\TypeConstants::AMOUNT_REMAINING_STATUS_FALSE}}" @if(request('remaining_status') == \Modules\Software\Classes\TypeConstants::AMOUNT_REMAINING_STATUS_FALSE) selected="" @endif>{{ trans('sw.amount_remaining_status_false')}}</option>
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

        @if(count($logs) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_expire_members_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-barcode fs-6 me-2"></i>{{ trans('sw.identification_code')}}
                            </th>
                            <th class="min-w-50px text-nowrap"></th>
                            <th class="min-w-50px text-nowrap"></th>
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
                                <i class="ki-outline ki-chart-line fs-6 me-2"></i>{{ trans('sw.workouts')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-chart-simple fs-6 me-2"></i>{{ trans('sw.number_of_visits')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.joining_date')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.expire_date')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-status fs-6 me-2"></i>{{ trans('sw.status')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.amount_remaining')}}
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
                                    <span class="fw-bold">{{ @$log->member->code }}</span>
                                </td>
                                <td class="pe-0">
                                    @if(@$log->member->code)
                                        <a download="{{@$log->member->code}}.png" href="{{route('sw.downloadCode', 'code='.@$log->member->code)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                            {!! \DNS1D::getBarcodeHTML($log->member->code, \Modules\Software\Classes\TypeConstants::BarcodeType,1.5,15) !!}
                                        </a>
                                    @endif
                                </td>
                                <td class="pe-0">
                                    <a href="{{route('sw.downloadCard', 'code='.@$log->member->code)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                        <i class="ki-outline ki-credit-card fs-2"></i>
                                    </a>
                                </td>
                                <td class="pe-0">
                                    <div class="symbol symbol-50px">
                                        <img alt="avatar" class="card_img rounded-circle @if(@$log->member->member_subscription_info->status == \Modules\Software\Classes\TypeConstants::Active) card_img_success @endif" src="{{@$log->member->image}}">
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @$log->member->name }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @$log->member->phone }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @$log->subscription->name }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @$log->workouts }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @$log->visits }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @\Carbon\Carbon::parse($log->joining_date)->toDateString() }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @\Carbon\Carbon::parse($log->expire_date)->toDateString() }}</span>
                                </td>
                                <td>
                                    <span class="badge @if(@$log->status == 0) badge-success @elseif(@$log->status == 1) badge-info @elseif(@$log->status == 2) badge-danger @endif">
                                        {!! @$log->statusName !!}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @number_format($log->amount_remaining, 2) }}</span>
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
                                        <!--begin::Invoice-->
                                        <a href="{{route('sw.showOrderSubscription',$log->id)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                           title="{{ trans('sw.invoice')}}">
                                            <i class="ki-outline ki-document fs-2"></i>
                                        </a>
                                        <!--end::Invoice-->

                                        @if(@$log->amount_remaining)
                                            @if(in_array('createMemberPayAmountRemainingForm', (array)$swUser->permissions) || $swUser->is_super_user)
                                                <!--begin::Pay-->
                                                <a data-target="#modalPay" data-toggle="modal" href="#" id="{{$log->id}}" class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm"
                                                   title="{{ trans('sw.pay')}}">
                                                    <i class="ki-outline ki-dollar fs-2"></i>
                                                </a>
                                                <!--end::Pay-->
                                            @endif
                                        @endif
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
                        <i class="ki-outline ki-user-cross fs-2"></i>
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
    <div class="modal" id="modalPay">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.pay_remaining')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6>{{ trans('sw.amount_paid')}}</h6>
                    <div id="modalPayResult"></div>
                    <form id="form_pay" action="" method="GET">
                        <div class="form-group">
                            <input name="amount_paid" class="form-control" type="number" id="amount_paid" placeholder="{{ trans('sw.enter_amount_paid')}}">
                        </div>
                        <br/>
                        <button class="btn ripple btn-primary rounded-3" id="form_pay_btn" type="submit">{{ trans('sw.pay')}}</button>
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
        $('.btn-indigo').off('click').on('click', function (e) {
            var that = $(this);
            var attr_id = that.attr('id');
            $('#form_pay').append('<input value="' + attr_id + '"  id="pay_id" name="pay_id"  hidden>');
        });
        $(document).on('click', '#form_pay_btn', function (event) {
            event.preventDefault();
            id = $('#pay_id').val();
            amount_paid = $('#amount_paid').val();
            $.ajax({
                url: '{{route('sw.createMemberPayAmountRemainingForm')}}',
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {id: id, amount_paid: amount_paid},
                success: function (response) {
                    if (response == '1') {
                        $('#modalPayResult').html('<div class="alert alert-success">{{ trans('admin.successfully_paid')}}</div>');
                        location.reload();
                    } else {
                        $('#modalPayResult').html('<div class="alert alert-danger">' + response + '</div>');
                    }
                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
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
        });
    </script>
@endsection

