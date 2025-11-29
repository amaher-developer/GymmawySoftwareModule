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

        /* Actions column styling */
        .actions-column {
            min-width: 140px !important;
            white-space: nowrap;
        }

        .actions-column .menu-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .actions-column .menu-link i {
            font-size: 1rem;
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

<!--begin::PT Members-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-gym fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <div class="card-toolbar">
        <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
        <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_pt_members_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->
                
                <!--begin::Add PT Member-->
                @if(in_array('createPTMember', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createPTMember')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add PT Member-->
                
                <!--begin::PT Calendar-->
                @if(in_array('listPTTrainerReport', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.listPTTrainerReport')}}" class="btn btn-sm btn-flex btn-light-info">
                        <i class="ki-outline ki-calendar fs-6"></i>
                        {{ trans('sw.pt_training_calender')}}
                    </a>
                @endif
                <!--end::PT Calendar-->
            </div>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Filter-->
        <div class="collapse" id="kt_pt_members_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="{{ route('sw.listPTMember') }}" method="get">
                    <div class="row g-6">
                        <div class="col-md-4">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" id="from_date" name="from" value="@php echo @strip_tags($_GET['from']) @endphp" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" id="to_date" name="to" value="@php echo @strip_tags($_GET['to']) @endphp" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.pt_subscription')}}</label>
                            <select name="pt_subscription" class="form-select form-select-solid">
                                <option value="">{{ trans('admin.choose')}}...</option>
                                @foreach($pt_subscriptions as $subscription)
                                    <option value="{{$subscription->id}}" @if(request('pt_subscription') == $subscription->id) selected="" @endif>{{$subscription->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.pt_trainer')}}</label>
                            <select name="pt_trainer" class="form-select form-select-solid">
                                <option value="">{{ trans('admin.choose')}}...</option>
                                @foreach($pt_trainers as $trainer)
                                    <option value="{{$trainer->id}}" @if(request('pt_trainer') == $trainer->id) selected="" @endif>{{$trainer->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6">{{ trans('admin.reset')}}</button>
                        <button type="submit" class="btn btn-primary fw-semibold px-6">{{ trans('sw.filter')}}</button>
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
{{--            @if((count(array_intersect(@(array)$swUser->permissions, ['exportPTMemberPDF', 'exportPTMemberExcel'])) > 0) || $swUser->is_super_user)--}}

{{--                <div class="col-md-2  col-xs-3  mg-t-20 mg-lg-t-0">--}}

{{--                    <button class="btn btn-primary  btn-block dropdown-toggle  rounded-3" data-toggle="dropdown">--}}
{{--                        <i class="fa fa-download mx-1"></i>--}}
{{--                        {{ trans('sw.download')}}--}}
{{--                        <i class="fa fa-angle-down"></i>--}}
{{--                    </button>--}}
{{--                    <ul class="dropdown-menu pull-right">--}}
{{--                        @if(in_array('exportPTMemberExcel', (array)$swUser->permissions) || $swUser->is_super_user)--}}
{{--                            <li>--}}
{{--                                <a href="{{route('sw.exportPTMemberExcel')}}"><i--}}
{{--                                            class="fa fa-file-excel-o"></i> {{ trans('sw.excel_export')}} </a>--}}
{{--                            </li>--}}
{{--                        @endif--}}
{{--                        @if(in_array('exportPTMemberPDF', (array)$swUser->permissions) || $swUser->is_super_user)--}}
{{--                            <li>--}}
{{--                                <a href="{{route('sw.exportPTMemberPDF')}}"><i--}}
{{--                                            class="fa fa-file-pdf-o"></i> {{ trans('sw.pdf_export')}} </a>--}}
{{--                            </li>--}}
{{--                        @endif--}}
{{--                    </ul>--}}

{{--                </div><!-- end Export div -->--}}
{{--            @endif--}}

{{--            <div class="col-lg-2 col-md-2 col-xs-3 mg-t-20 mg-lg-t-0">--}}
{{--                <button class="btn btn-default btn-block rounded-3" id="members_pt_refresh" onclick="members_pt_refresh()">--}}
{{--                    <i class="fa fa-refresh mx-1"></i>--}}
{{--                    {{ trans('sw.members_refresh')}}--}}
{{--                </button>--}}
{{--            </div>--}}
        

      

        @if(count($members) > 0)
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-check-circle fs-6 me-2"></i>{{ trans('sw.status')}}
                            </th>
                            <th class="min-w-250px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.member_name')}}
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="ki-outline ki-bookmark fs-6 me-2"></i>{{ trans('sw.pt_training_name')}}
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="ki-outline ki-user-tick fs-6 me-2"></i>{{ trans('sw.pt_trainer')}}
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-chart-simple fs-6 me-2"></i>{{ trans('sw.sessions_used')}}
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.joining_date')}}
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.expire_date')}}
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.amount_remaining')}}
                            </th>
                            <th class="min-w-100px text-nowrap text-end">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date')}}
                            </th>
                            <th class="text-end min-w-70px text-nowrap">
                                <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($members as $key=> $member)
                        <tr>
                            <td>
                                <span class="badge @if(@$member->status == 0) badge-success @elseif(@$member->status == \Modules\Software\Classes\TypeConstants::Coming) badge-gray @elseif(@$member->status == \Modules\Software\Classes\TypeConstants::Active) badge-info @elseif(@$member->status == \Modules\Software\Classes\TypeConstants::Expired) badge-danger @endif">{!! @$member->statusName !!}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-5">
                                        <img src="{{$member->member->image}}" class="rounded-circle" alt="">
                                    </div>
                                    <div class="d-flex justify-content-start flex-column">
                                        <a href="#" class="text-gray-900 fw-bold text-hover-primary fs-6">{{ @$member->member->name }}</a>
                                        @if($member->member->national_id)
                                            <span class="text-muted fw-semibold d-block fs-7">
                                                <i class="fa fa-credit-card me-1"></i> {{$member->member->national_id}}
                                            </span>
                                        @endif
                                        <span class="text-muted fw-semibold d-block fs-7">{{ trans('sw.barcode') }}: {{ @$member->member->code }} | {{ trans('sw.id') }}: {{@$member->id}}</span>
                                    </div>
                                </div>
                            </td>
                            <td> {{ @$member->pt_class->name ?? @$member->pt_subscription->name }}
                                @if(@$member->notes)
                                    <br/>
                                    <br/>
                                    <span class="badge badge-info" style="cursor: pointer;" data-bs-target="#pt_subscription_notes_{{$member->id}}" data-bs-toggle="modal">
                                        <i class="fa fa-info-circle"></i> {{ trans('sw.notes')}}
                                    </span>
                                @endif
                            </td>
                            <td> {{ @$member->pt_trainer->name}}</td>
                            @php
                                $sessionsTotal = $member->sessions_total ?? $member->classes ?? 0;
                                $sessionsUsed = $member->sessions_used;
                                $sessionsRemaining = $member->sessions_remaining ?? max($sessionsTotal - $sessionsUsed, 0);
                            @endphp
                            <td>
                                <span style="vertical-align: baseline;height: 10px;padding: 3px 4px;"
                                      class="badge @if(@$member->status == \Modules\Software\Classes\TypeConstants::Coming) badge-gray  @elseif($sessionsRemaining > 0) badge-success @else badge-danger @endif">
                                </span>
                                {{ $sessionsUsed }} / {{ $sessionsTotal ?: '-' }}
                            </td>
                            <td><i class="fa fa-calendar text-muted"></i> {{ \Carbon\Carbon::parse($member->joining_date)->format('Y-m-d') }}
                            </td>
                            <td><i class="fa fa-calendar text-muted"></i> {{ \Carbon\Carbon::parse(@$member->expire_date)->format('Y-m-d')  }}
                                <br/><span style="font-size: 10px;"><i class="fa fa-sort-numeric-desc"></i> {{ trans('sw.reminder_days')}}: {{(\Carbon\Carbon::parse($member->expire_date)->toDateString() > \Carbon\Carbon::now()->toDateString()) ? @\Carbon\Carbon::parse($member->expire_date)->diffInDays(\Carbon\Carbon::now()->toDateString()) : 0}}</span>
                            </td>
                            <td> {{ number_format($member->amount_remaining, 2) }}</td>
                            <td><i class="fa fa-calendar text-muted"></i> {{ $member->created_at->format('Y-m-d') }}
                                <br/>
                                <i class="fa fa-clock-o text-muted"></i> {{ $member->created_at->format('h:i a') }}</td>
                            <td class="text-end actions-column">
                                <a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    {{ trans('admin.actions') }}
                                    <i class="ki-outline ki-down fs-5 ms-1"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-250px py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="https://web.whatsapp.com/send?phone={{ ((substr( $member->member->phone, 0, 1 ) === '+') || (substr( $member->member->phone, 0, 2 ) === '00')) ? $member->member->phone : '+'.env('APP_COUNTRY_CODE').$member->member->phone}}"
                                           target="_blank" class="menu-link px-3" title="{{ trans('sw.whatsapp')}}">
                                            <i class="ki-outline ki-message-text-2 text-success"></i>
                                            <span>{{ trans('sw.whatsapp')}}</span>
                                        </a>
                                    </div>

                                    @if(in_array('createPTMemberPayAmountRemainingForm', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a data-bs-target="#modalPay" data-bs-toggle="modal" href="#"
                                           id="{{@$member->id}}"
                                           class="menu-link px-3 btn-indigo"
                                           title="{{ trans('sw.pay')}}">
                                            <i class="ki-outline ki-dollar text-warning"></i>
                                            <span>{{ trans('sw.pay')}}</span>
                                        </a>
                                    </div>
                                    @endif

                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0)"
                                           class="menu-link px-3"
                                           data-bs-target="#modalQR{{@$member->id}}" data-bs-toggle="modal"
                                           title="{{ trans('sw.qrcode')}}">
                                            <i class="ki-outline ki-scan-barcode text-info"></i>
                                            <span>{{ trans('sw.qrcode')}}</span>
                                        </a>
                                    </div>

                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0)"
                                           onclick="member_calendar({{@$member->id}})"
                                           id="{{$member->id}}"
                                           class="menu-link px-3"
                                           title="{{ trans('sw.pt_training_calender')}}">
                                            <i class="ki-outline ki-calendar text-primary"></i>
                                            <span>{{ trans('sw.pt_training_calender')}}</span>
                                        </a>
                                    </div>

                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.showOrderPTSubscription',@$member->id)}}"
                                           class="menu-link px-3"
                                           title="{{ trans('sw.invoice')}}">
                                            <i class="ki-outline ki-document text-primary"></i>
                                            <span>{{ trans('sw.invoice')}}</span>
                                        </a>
                                    </div>
                                    
                                    @if(in_array('editPTMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.editPTMember',$member->id)}}"
                                           class="menu-link px-3"
                                           title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil text-primary"></i>
                                            <span>{{ trans('admin.edit')}}</span>
                                        </a>
                                    </div>
                                    @endif
                                    
                                    @if(in_array('deletePTMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                        @if(request('trashed'))
                                        <div class="menu-item px-3">
                                            <a title="{{ trans('admin.enable')}}"
                                               href="{{route('sw.deletePTMember',$member->id)}}"
                                               class="menu-link px-3 confirm_delete">
                                                <i class="ki-outline ki-check-circle text-success"></i>
                                                <span>{{ trans('admin.enable')}}</span>
                                            </a>
                                        </div>
                                        @else
                                        <div class="menu-item px-3">
                                            <a title="{{ trans('sw.disable_with_refund', ['amount' => $member->amount_paid])}}"
                                               data-swal-text="{{ trans('sw.disable_with_refund', ['amount' => $member->amount_paid])}}"
                                               data-swal-amount="{{@$member->amount_paid}}"
                                               href="{{route('sw.deletePTMember',$member->id).'?refund=1&total_amount='.@$member->amount_paid}}"
                                               class="menu-link px-3 confirm_delete">
                                                <i class="ki-outline ki-trash text-danger"></i>
                                                <span>{{ trans('admin.disable')}}</span>
                                            </a>
                                        </div>
                                        @endif
                                    @endif
                                </div>

                                <!-- start model QR -->
                                <div class="modal" id="modalQR{{@$member->id}}">
                                    <div class="modal-dialog modal-sm" role="document">
                                        <div class="modal-content modal-content-demo">
                                            <div class="modal-header">
                                                <h6 class="modal-title">{{ trans('sw.qrcode')}}</h6>
                                                <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-12 text-center">
                                                        {!! @\DNS2D::getBarcodeHTML(@$member->id, \Modules\Software\Classes\TypeConstants::QRCodeType,12,12) !!}
                                                    <br/>
                                                    </div>
                                                    <div class="col-md-12 text-center"><b>{{@$member->id}}</b></div>
                                                </div>

                                                <div class="clearfix"><br/></div>
                                                <div class="text-center">
                                                    <a download="{{@$member->id}}.png"
                                                       href="{{route('sw.downloadQRCode', 'code='.@$member->id)}}">{{ trans('sw.download')}}</a>
                                                </div>
                                            </div>
                                            <div class="clearfix"><br/></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End model QR -->
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            
            <!--begin::Pagination-->
            <div class="d-flex flex-stack flex-wrap pt-10">
                <div class="fs-6 fw-semibold text-gray-700">
                    {{ trans('sw.showing_entries', [
                        'from' => $members->firstItem() ?? 0,
                        'to' => $members->lastItem() ?? 0,
                        'total' => $members->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $members->appends($search_query)->render() !!}
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
<!--end::PT Members-->
    
    <!--begin::Notes Modals-->
    @foreach($members as $member)
        @if(@$member->notes)
            <div class="modal" id="pt_subscription_notes_{{$member->id}}">
                <div class="modal-dialog" role="document">
                    <div class="modal-content modal-content-demo">
                        <div class="modal-header">
                            <h6 class="modal-title">{{ trans('sw.notes')}}</h6>
                            <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            {{@$member->notes}}
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
    <!--end::Notes Modals-->



    <!-- start model pay -->
    <div class="modal" id="modalMemberTable">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.pt_training_calender')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="portlet grey-cascade box">
                                <div class="portlet-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered table-striped" id="cart_table">
                                            <thead>
                                            <tr>
                                                <th>
                                                    #
                                                </th>
                                                <th>
                                                    {{ trans('sw.date')}}
                                                </th>
                                                <th>
                                                    {{ trans('sw.membership')}}
                                                </th>

{{--                                                <th>--}}
{{--                                                    {{ trans('sw.status')}}--}}
{{--                                                </th>--}}
                                            </tr>
                                            </thead>
                                            <tbody id="cart_result" @if($lang == 'ar') class="text-right" @endif>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- End model pay -->


    <!-- start model pay -->
    <div class="modal" id="modalPay">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.pay_remaining')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6>{{ trans('sw.amount_paid')}}</h6>
                    <div id="modalPayResult"></div>
                    <form id="form_pay" action="" method="GET">
                        <div class="row">
                            <div class="form-group col-lg-6">
                                <input name="amount_paid" class="form-control" type="number" id="amount_paid"  step="0.01"
                                       placeholder="{{ trans('sw.enter_amount_paid')}}">
                            </div><!-- end pay qty  -->
                            <div class="form-group col-lg-6">
                                <select class="form-control" name="payment_type" id="payment_type">
                                    @foreach($payment_types as $payment_type)
                                        <option value="{{$payment_type->payment_id}}" @if(@old('payment_type',$member->member_subscription_info->payment_type) == $payment_type->payment_id) selected="" @endif >{{$payment_type->name}}</option>
                                    @endforeach
{{--                                    <option value="{{\Modules\Software\Classes\TypeConstants::CASH_PAYMENT}}" >{{ trans('sw.payment_cash')}}</option>--}}
{{--                                    <option value="{{\Modules\Software\Classes\TypeConstants::ONLINE_PAYMENT}}" >{{ trans('sw.payment_online')}}</option>--}}
{{--                                    <option value="{{\Modules\Software\Classes\TypeConstants::BANK_TRANSFER_PAYMENT}}" >{{ trans('sw.payment_bank_transfer')}}</option>--}}
                                </select>
                            </div><!-- end pay qty  -->
                        </div>
                        
                        @if(@$mainSettings->active_loyalty)
                        <!--begin::Loyalty Points Earning Info-->
                        <div class="alert alert-dismissible bg-light-success border border-success border-dashed d-flex flex-column flex-sm-row p-4 mb-3 mt-3" id="pt_pay_loyalty_earning_info" style="display: none !important;">
                            <i class="ki-outline ki-gift fs-2hx text-success me-3 mb-3 mb-sm-0"></i>
                            <div class="d-flex flex-column pe-0 pe-sm-5">
                                <h6 class="mb-1">{{ trans('sw.points_earning_info')}}</h6>
                                <span class="text-gray-700 fs-7">{!! trans('sw.you_will_earn_points', ['points' => '<span id="pt_pay_estimated_earning_points" class="fw-bold text-success">0</span>'])!!}</span>
                                <span class="text-gray-600 fs-8" id="pt_pay_loyalty_earning_rate"></span>
                            </div>
                        </div>
                        <!--end::Loyalty Points Earning Info-->
                        @endif
                        
                        <br/>
                        <button class="btn ripple btn-primary rounded-3" id="form_pay_btn"
                                type="submit">{{ trans('sw.pay')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- End model pay -->

@endsection

@section('scripts')
    @parent
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    <script>
        // Loyalty Points Variables for PT Pay Remaining Modal
        var ptPayLoyaltyMoneyToPointRate = 0;
        
        @if(@$mainSettings->active_loyalty)
        // Load loyalty earning rate on page load
        $(document).ready(function() {
            $.ajax({
                url: '{{ route('sw.getMemberLoyaltyInfo') }}',
                type: 'GET',
                data: { member_id: 0 },
                success: function(response) {
                    if (response.success && response.money_to_point_rate) {
                        ptPayLoyaltyMoneyToPointRate = response.money_to_point_rate;
                        $('#pt_pay_loyalty_earning_rate').text('{{ trans('sw.earning_rate', ['rate' => '']) }}'.replace(':rate عملة', ptPayLoyaltyMoneyToPointRate.toFixed(2) + ' {{ trans('sw.app_currency') }}').replace(':rate currency', ptPayLoyaltyMoneyToPointRate.toFixed(2) + ' {{ trans('sw.app_currency') }}'));
                    }
                }
            });
            
            // Add event listener for amount_paid in PT pay modal
            $('#amount_paid').on('change input keyup', function() {
                const amountPaid = parseFloat($(this).val()) || 0;
                if (ptPayLoyaltyMoneyToPointRate > 0 && amountPaid > 0) {
                    const estimatedPoints = Math.floor(amountPaid / ptPayLoyaltyMoneyToPointRate);
                    if (estimatedPoints > 0) {
                        $('#pt_pay_estimated_earning_points').text(estimatedPoints);
                        $('#pt_pay_loyalty_earning_info').slideDown();
                    } else {
                        $('#pt_pay_loyalty_earning_info').slideUp();
                    }
                } else {
                    $('#pt_pay_loyalty_earning_info').slideUp();
                }
            });
        });
        @endif

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
            id = $('#pay_id').val();
            amount_paid = $('#amount_paid').val();
            payment_type = $('#payment_type').val();
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

        $(document).on('click', '.remove_filter', function (event) {
            event.preventDefault();
            var filter = $(this).attr('id');
            $("#" + filter).val('');
            $("#form_filter").submit();
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

            $('button[type="reset"]').on('click', function() {
                setTimeout(() => {
                    $(this).closest('form').find('select').trigger('change');
                }, 100);
            });
        });

        function member_calendar(id){
            let url  = "{{route('sw.listPTMemberCalendar', '@@id')}}";
            url = url.replace('@@id', id);
            $.ajax({
                url: url,
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {member: id},
                success: function (response) {
                    // $('#trainer_confirm').modal('toggle');
                    // $('#tr_trainer_member_'+id).remove();
                    let result = '';
                    let date_status = '';
                    let response_data = $.parseJSON(response);
                    if(response_data.result.length > 0){
                        for (let i = 0; i < response_data.result.length; i++){
                            date_status = 'background-color: white;';
                            if(response_data.result[i].date_status === 1){
                                date_status = 'background-color: #ffa5004f;';
                            }
                            result+= '<tr style="'+date_status+'">';
                            result+= '<td>' + (i+1) + '</td>';
                            result+= '<td style="direction: ltr;"><i class="fa fa-calendar text-muted"></i> ' + response_data.result[i].member_date + '<br> <i class="fa fa-clock-o text-muted"></i> ' + (response_data.result[i].member_time_from || '') + ' ~ ' + (response_data.result[i].member_time_to || '') + '</td>';
                            result+= '<td>' + response_data.result[i].member_subscription + '</td>';
                            result+= '</tr>';
                        }
                    }else{
                        result = '<tr id="empty_cart"><td colspan="3" class="text-center">{{ trans('sw.no_record_found')}}</td></tr>';

                    }
                    $('#modalMemberTable').modal('show');
                    $('#cart_result').html(result);
                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });
        }


        function members_pt_refresh(){
            $('#members_pt_refresh').hide().after('<div class="col-md-12"><div class="loader"></div></div>');
            $.ajax({
                url: '{{route('sw.membersPTRefresh')}}',
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {},
                success: function (response) {
                    setTimeout(function () {
                        window.location.replace("{{asset(route('sw.listPTMember'))}}");
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


