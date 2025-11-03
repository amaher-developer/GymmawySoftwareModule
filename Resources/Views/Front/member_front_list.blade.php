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
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/admin/global/plugins/pick-hours-availability-calendar/mark-your-calendar.css')}}">
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

        .m-datatable {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .m-row {
            display: table-row;
        }

        .m-cell {
            display: table-cell;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: @if($lang == 'ar') right @else left @endif;
        }

        .m-header .m-cell {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        #fingerprint_error_msg {
            display: none;
        }
    </style>
    <style>
        /*#myc-next-week {*/
        /*    display: none !important;*/
        /*}*/
        /*#myc-prev-week {*/
        /*    display: none !important;*/
        /*}*/
        .invoice-block {
            text-align: center;
        }

        .member_balance_less {
            background: #e7505a;
            padding: 0 5px;
            color: #ffffff;
            border-radius: 5px !important;
        }
        .member_balance_more {
            background: #32c5d2;
            padding: 0 5px;
            color: #ffffff;
            border-radius: 5px !important;
        }
    </style>
    <style id="jsbin-css">
        @media (min-width: 768px) {
            .modal-xl {
                width: 90%;
                max-width:1200px;
            }
        }

    </style>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
@endsection

@section('page_body')

<!--begin::Members-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-user fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
                
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_members_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->

                <!--begin::Add Member-->
                @if(in_array('createMember', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createMember')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add Member-->
                
                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportMemberPDF', 'exportMemberExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download')}}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportMemberExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportMemberExcel', $search_query)}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export')}}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportMemberPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportMemberPDF', $search_query)}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.pdf_export')}}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <!--end::Export-->
                
                <!--begin::Calendar Button-->
                <a href="{{route('sw.listNonMemberReport')}}" class="btn btn-sm btn-flex btn-light-info">
                    <i class="ki-outline ki-calendar fs-6"></i>
                    {{ trans('sw.activities_calender')}}
                </a>
                <!--end::Calendar Button-->

                <!--begin::Members Refresh Button-->
                <button class="btn btn-sm btn-flex btn-light-secondary" id="members_refresh" onclick="members_refresh()">
                    <i class="ki-outline ki-refresh fs-6"></i>
                    {{ trans('sw.members_refresh')}}
                </button>
                <!--end::Members Refresh Button-->

                @if(@$mainSettings->active_zk)
                <!--begin::Fingerprint Refresh Button-->
                <button class="btn btn-sm btn-flex btn-light-secondary" id="fingerprint_refresh" onclick="fingerprint_open_popup()">
                    <i class="ki-outline ki-refresh fs-6"></i>
                    {{ trans('sw.fingerprint_refresh')}}
                </button>
                <!--end::Fingerprint Refresh Button-->
                @endif
            </div>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Filter-->
        <div class="collapse" id="kt_members_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" id="from_date" value="@php echo @strip_tags($_GET['from']) ? \Carbon\Carbon::parse($_GET['from'])->format('Y-m-d') : '' @endphp" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" name="to" id="to_date" value="@php echo @strip_tags($_GET['to']) ? \Carbon\Carbon::parse($_GET['to'])->format('Y-m-d') : '' @endphp" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.status_now')}}</label>
                            <select name="status" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.status_now')}}...</option>
                                <option value="{{\Modules\Software\Classes\TypeConstants::Active}}" @if(isset($_GET['status']) && ((request('status') != "") && (request('status') == \Modules\Software\Classes\TypeConstants::Active))) selected="" @endif>{{ trans('sw.active')}}</option>
                                <option value="{{\Modules\Software\Classes\TypeConstants::Freeze}}" @if(request('status') == \Modules\Software\Classes\TypeConstants::Freeze) selected="" @endif>{{ trans('sw.frozen')}}</option>
                                <option value="{{\Modules\Software\Classes\TypeConstants::Expired}}" @if(request('status') == \Modules\Software\Classes\TypeConstants::Expired) selected="" @endif>{{ trans('sw.expire')}}</option>
                                <option value="{{\Modules\Software\Classes\TypeConstants::Coming}}" @if(request('status') == \Modules\Software\Classes\TypeConstants::Coming) selected="" @endif>{{ trans('sw.coming')}}</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.memberships')}}</label>
                            <select name="subscription" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.memberships')}}...</option>
                                @foreach($subscriptions as $subscription)
                                    <option value="{{$subscription->id}}" @if(request('subscription') == $subscription->id) selected="" @endif>{{$subscription->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
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
            <form class="d-flex" action="{{ route('sw.listMember') }}" method="get" style="max-width: 400px;">
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="@php echo @strip_tags($_GET['search']) @endphp" placeholder="{{ trans('sw.search_on')}}">
                <!-- Hidden inputs to preserve filter parameters -->
                @if(request('from'))
                    <input type="hidden" name="from" value="{{ request('from') }}">
                @endif
                @if(request('to'))
                    <input type="hidden" name="to" value="{{ request('to') }}">
                @endif
                @if(request('subscription'))
                    <input type="hidden" name="subscription" value="{{ request('subscription') }}">
                @endif
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

        <div id="fingerprint_error_msg" class="alert alert-warning" @if(@$mainSettings->active_zk && @env('APP_ZK_LOCAL_HOST') && ((@$mainSettings->zk_online_at == null) || (\Carbon\Carbon::parse($mainSettings->zk_online_at)->toDateString() < \Carbon\Carbon::now()->subDays(3)->toDateString() ))) style="display: block;" @endif><i class="ki-outline ki-warning fs-6 me-2"></i>  {!! trans('sw.zk_not_connect_msg') !!}</div>
        @if(count($members) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_members_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-barcode fs-6 me-2"></i>{{ trans('sw.identification_code')}}
                            </th>
                            <th class="min-w-50px text-nowrap"></th>
                            <th class="min-w-50px text-nowrap"></th>
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.name')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-status fs-6 me-2"></i>{{ trans('sw.status')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-phone fs-6 me-2"></i>{{ trans('sw.phone')}}
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="ki-outline ki-geolocation fs-6 me-2"></i>{{ trans('sw.address')}}
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
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-list fs-6 me-2"></i>{{ trans('sw.activities')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.joining_date')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.expire_date')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.amount_remaining')}}
                            </th>
                            @if(@$mainSettings->active_loyalty)
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-gift fs-6 me-2"></i>{{ trans('sw.loyalty_points')}}
                            </th>
                            @endif
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date')}}
                            </th>
                            <th class="text-end actions-column">
                                <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                            </th>
                        </tr>
                    </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($members as $key=> $member)
                        @php
                                $has_coming = false;
                                if(@$member->member_subscription_info_has_active){
                                    (($member->member_subscription_info->status = \Modules\Software\Classes\TypeConstants::Coming) && (@$member->member_subscription_info->id != @$member->member_subscription_info_has_active->id)) ? $has_coming = true : $has_coming = false;
                                    $member->member_subscription_info = $member->member_subscription_info_has_active;
                                }
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex flex-column align-items-start">
                                    <span class="fw-bold text-gray-800 fs-6">{{ $member->code }}</span>
                                    <div class="d-flex gap-1 mt-1">
                                        @if(@$member->code)
                                            <a download="{{@$member->code}}.png"
                                               href="{{route('sw.downloadCode', 'code='.@$member->code)}}" 
                                               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm d-flex align-items-center justify-content-center"
                                               style="overflow: hidden; padding: 4px;"
                                               title="{{ trans('sw.download_barcode')}}">
                                                {!! \DNS1D::getBarcodeHTML($member->code, \Modules\Software\Classes\TypeConstants::BarcodeType, 1, 20) !!}
                                            </a>
                                            <a href="{{route('sw.downloadCard', 'code='.@$member->code)}}" 
                                               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                               title="{{ trans('sw.download_card')}}">
                                                <i class="ki-outline ki-badge fs-3"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                <!-- Empty column for spacing -->
                            </td>
                            <td class="pe-0">
                                <!-- Empty column for spacing -->
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-50px me-3">
                                        <img alt="avatar" class="rounded-circle" src="{{$member->image}}">
                                    </div>
                                    <!--end::Avatar-->
                                    <div>
                                        <!--begin::Title-->
                                        <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            {{ $member->name }}
                                        </div>
                                        @if($member->national_id)
                                            <div class="text-muted fs-7">
                                                <i class="ki-outline ki-credit-cart fs-6 me-1"></i> {{$member->national_id}}
                                            </div>
                                        @endif
                                        @if(@$member->fp_id && (@env('APP_ZK_GATE')))
                                            <div class="text-muted fs-7">
                                                <i class="material-icons" style="font-size: inherit !important;">fingerprint</i> {{$member->fp_id}}
                                            </div>
                                        @endif
                                        <!--end::Title-->
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                <span
                                    @if(@$member->member_subscription_info->status == \Modules\Software\Classes\TypeConstants::Freeze)
                                        @php
                                            $freeze_end_badge = \Carbon\Carbon::parse($member->member_subscription_info->end_freeze_date)->startOfDay();
                                            $now_badge = \Illuminate\Support\Carbon::now()->startOfDay();
                                            $freeze_days_remaining_badge = $freeze_end_badge->isPast() ? 0 : max(0, (int) $now_badge->diffInDays($freeze_end_badge, false));
                                        @endphp
                                        style="cursor: pointer;" data-target="#modalFreezeInfo" data-toggle="modal" href="#" onclick="show_freeze_date('{{@$member->name}}', '{{@$member->member_subscription_info->start_freeze_date}}', '{{@$member->member_subscription_info->end_freeze_date}}', '{{@$member->member_subscription_info->freeze_limit}}', '{{$member->member_subscription_info->number_times_freeze}}', '{{$freeze_days_remaining_badge}}', '{{@$member->member_subscription_info->subscription->number_times_freeze}}')"
                                    @endif
                                    class="badge @if(@$member->member_subscription_info->status == 0) badge-success @elseif(@$member->member_subscription_info->status == 1) badge-info @elseif(@$member->member_subscription_info->status == 2) badge-danger @endif">
                                    @if(@$member->member_subscription_info->status == \Modules\Software\Classes\TypeConstants::Freeze) <i class="fa fa-info-circle"></i> @endif
                                    {!! @$member->member_subscription_info->statusName !!}
                                </span>
                                @if($has_coming)<span class="badge ">{{ trans('sw.coming')}}</span>@endif
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ $member->phone }}</span>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ $member->address }}</span>
                            </td>
                            <td class="pe-0">
                                <div>
                                    <span class="fw-bold">{{ @$member->member_subscription_info->subscription->name }}</span>
                                    @if(@$member->member_subscription_info->notes)
                                        <br/>
                                        <span class="badge badge-light-info" style="cursor: pointer;" data-target="#subscription_notes_{{$member->member_subscription_info->id}}" data-toggle="modal">
                                            <i class="ki-outline ki-information-5 fs-6 me-1"></i> {{ trans('sw.notes')}}
                                        </span>

                                        <div class="modal" id="subscription_notes_{{$member->member_subscription_info->id}}">
                                            <div class="modal-dialog " role="document">
                                                <div class="modal-content modal-content-demo">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">{{ trans('sw.notes')}}</h6>
                                                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        {{@$member->member_subscription_info->notes}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ @$member->member_subscription_info->workouts }}</span>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ @$member->member_subscription_info->visits }}</span>
                            </td>
                            <td class="pe-0">
                                <div class="d-flex flex-wrap gap-1">
                                    @php
                                        $activities = @$member->member_subscription_info->activities ?? [];
                                        if (is_array($activities) && count($activities) > 0) {
                                            echo implode('', array_map(function ($name) use ($member, $lang) {
                                                if (@$name['activity']['id']) {
                                                    static $i = 0;
                                                    return '<button class="btn btn-'.(@$name['training_times'] > @$name['visits'] ? 'primary' : 'gray').' btn-sm rounded-2" id="activity_'.@$member->id.'_'.@$name['activity']['id'].'" onclick="non_membership_reservation('.@$member->id.', '.@$name['activity']['id'].')"  data-target="#modalReservation" data-toggle="modal" style="font-size: 10px; padding: 2px 6px;">'.$name['activity']['name_'.$lang].'</button>';
                                                    $i++;
                                                }
                                            }, $activities));
                                        }
                                    @endphp
                                </div>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ @\Carbon\Carbon::parse($member->member_subscription_info->joining_date)->toDateString() }}</span>
                            </td>
                            <td class="pe-0">
                                <div class="d-flex flex-column">
                                    <div class="text-muted fw-bold d-flex align-items-center">
                                        <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                        <span>{{ @\Carbon\Carbon::parse($member->member_subscription_info->expire_date)->toDateString() }}</span>
                                    </div>
                                    <div class="text-muted fs-7 d-flex align-items-center">
                                        <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                        <span>{{ trans('sw.reminder_days')}}: {{ (\Carbon\Carbon::parse(@$member->member_subscription_info->expire_date)->toDateString() > \Carbon\Carbon::now()->toDateString()) ? (int) @\Carbon\Carbon::parse(@$member->member_subscription_info->expire_date)->diffInDays(\Carbon\Carbon::now()->toDateString()) : 0 }}</span>
                                    </div>
                                    @if(@$member->member_subscription_info->status == \Modules\Software\Classes\TypeConstants::Freeze)
                                        <div class="text-muted fs-7 d-flex align-items-center">
                                            <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                            @php
                                                $freeze_end = \Carbon\Carbon::parse($member->member_subscription_info->end_freeze_date)->startOfDay();
                                                $now = \Illuminate\Support\Carbon::now()->startOfDay();
                                                $freeze_days_remaining = $freeze_end->isPast() ? 0 : max(0, (int) $now->diffInDays($freeze_end, false));
                                            @endphp
                                            <span>{{ trans('sw.reminder_freeze_days')}}: {{ $freeze_days_remaining }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="pe-0">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold" id="span_amount_remaining_{{@$member->member_subscription_info->id}}">{{ @number_format($member->member_subscription_info->amount_remaining, 2) }}</span>
                                    <span class="text-muted fs-7">{{ trans('sw.total_amount_remaining')}}: <span id="span_total_amount_remaining_{{@$member->member_subscription_info->id}}">{{@number_format($member->member_remain_amount_subscriptions->sum('amount_remaining'), 2)}}</span></span>
                                </div>
                            </td>
                            @if(@$mainSettings->active_loyalty)
                            <td class="pe-0">
                                <div class="d-flex align-items-center">
                                    <i class="ki-outline ki-gift fs-3 text-primary me-2"></i>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-primary fs-6"><a href="{{ route('sw.loyalty_transactions.member_history', $member->id) }}"  title="{{ trans('sw.view_full_history') }}">{{ number_format($member->loyalty_points_balance ?? 0) }}</a></span>
                                        <span class="text-muted fs-7">{{ trans('sw.points') }}</span>
                                    </div>
                                </div>
                            </td>
                            @endif
                            <td class="pe-0">
                                <div class="d-flex flex-column">
                                    <div class="text-muted fw-bold d-flex align-items-center">
                                        <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                        <span>{{ $member->created_at->format('Y-m-d') }}</span>
                                    </div>
                                    <div class="text-muted fs-7 d-flex align-items-center">
                                        <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                        <span>{{ $member->created_at->format('h:i a') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end actions-column">
                                <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                    <!--begin::Profile-->
                                    <button data-target="#modalProfileMember" data-toggle="modal" href="#" onclick="show_profile_member('{{$member->id}}')"
                                        style="cursor: pointer;"
                                        member_id="{{$member->id}}"
                                        member_name="{{$member->name}}"
                                       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm modalProfileBtn"
                                       title="{{ trans('sw.member_profile')}}">
                                        <i class="ki-outline ki-user fs-2"></i>
                                    </button>
                                    <!--end::Profile-->
                                
                                    <!--begin::WhatsApp-->
                                    <a href="https://web.whatsapp.com/send?phone={{ ((substr( $member->phone, 0, 1 ) === "+") || (substr( $member->phone, 0, 2 ) === "00")) ? $member->phone : '+'.env('APP_COUNTRY_CODE').$member->phone}}"
                                       target="_blank" class="btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="{{ trans('sw.whatsapp')}}">
                                        <i class="ki-outline ki-message-text-2 fs-2"></i>
                                    </a>
                                    <!--end::WhatsApp-->

                                    @if((@$member->member_subscriptions_count < \Modules\Software\Classes\TypeConstants::RENEW_MEMBERSHIPS_MAX_NUM) && (in_array('memberSubscriptionRenewStore', (array)$swUser->permissions) || $swUser->is_super_user))
                                        <!--begin::Renew-->
                                        <a class="btn btn-icon btn-bg-light btn-active-color-info btn-sm"
                                            onclick="list_renew_membership('{{@$member->member_subscription_info->id}}')"
                                            expire_msg="{{ trans('sw.expire_date_msg', ['date' => @\Carbon\Carbon::parse($member->member_subscription_info->expire_date)->toDateString()])}}"
                                            expire_color="@if(@$member->member_subscription_info->status == 0) green @else red @endif"
                                            title="{{ trans('sw.renew_membership')}}"
                                            id="list_member_{{@$member->member_subscription_info->id}}" style="cursor: pointer;"
                                            data-target="#modelRenew" data-toggle="modal">
                                            <i class="ki-outline ki-arrows-circle fs-2"></i>
                                        </a>
                                        <!--end::Renew-->
                                    @endif

                                    @if(in_array('createMemberPayAmountRemainingForm', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Pay-->
                                        <a data-target="#modalPays_{{$member->id}}" data-toggle="modal" href="#"
                                           id="{{@$member->member_subscription_info->id}}" style="cursor: pointer;"
                                           class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm btn-indigos"
                                           title="{{ trans('sw.pay_remaining')}}">
                                            <i class="ki-outline ki-dollar fs-2"></i>
                                        </a>
                                        <!--end::Pay-->
                                    @endif

                                    @if(in_array('creditMemberBalanceAdd', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Credit-->
                                        <a data-toggle="modal" href="#" onclick="modal_credits('{{@$member->id}}')"
                                           id="{{@$member->member_subscription_info->id}}" style="cursor: pointer;"
                                           class="btn btn-icon btn-bg-light btn-active-color-success btn-sm btn-indigos"
                                           title="{{ trans('sw.add_credit')}}">
                                            <i class="ki-outline ki-dollar fs-2"></i>
                                        </a>
                                        <!--end::Credit-->
                                    @endif

                                    @if(@$member->member_subscription_info->id)
                                        <!--begin::Invoice-->
                                        <a href="{{route('sw.showOrderSubscription',@$member->member_subscription_info->id)}}" style="cursor: pointer;"
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm btn-indigo"
                                           title="{{ trans('sw.invoice')}}">
                                            <i class="ki-outline ki-document fs-2"></i>
                                        </a>
                                        <!--end::Invoice-->
                                    @endif

                                    @if(in_array('freezeMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                        @if((@($member->member_subscription_info->number_times_freeze) > 0) && (@$member->member_subscription_info->status == \Modules\Software\Classes\TypeConstants::Active))
                                            <!--begin::Freeze (open form modal)-->
                                            <a href="#" 
                                               class="btn btn-icon btn-bg-light btn-active-color-info btn-sm open_freeze_modal"
                                               data-member_id="{{$member->id}}"
                                               data-member_name="{{$member->name}}"
                                               data-subscription_name="{{@$member->member_subscription_info->subscription->name}}"
                                               data-freeze_limit="{{@$member->member_subscription_info->freeze_limit}}"
                                               data-times_left="{{@$member->member_subscription_info->number_times_freeze}}"
                                               title="{{ trans('sw.freeze_account')}}">
                                                <i class="ki-outline ki-cross-circle fs-2"></i>
                                            </a>
                                            <!--end::Freeze-->
                                        @endif
                                    @endif

                                    @if(in_array('editMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Edit-->
                                        <a href="{{route('sw.editMember',$member->id)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                           title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                        <!--end::Edit-->
                                    @endif

                                    @if(in_array('deleteMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                        @if(request('trashed'))
                                            <!--begin::Enable-->
                                            <a title="{{ trans('admin.enable')}}"
                                               href="{{route('sw.deleteMember',$member->id)}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="{{ trans('admin.enable')}}">
                                                <i class="ki-outline ki-check-circle fs-2"></i>
                                            </a>
                                            <!--end::Enable-->
                                        @else
                                            <!--begin::Delete-->
                                            {{-- <a title="{{ trans('sw.disable_without_refund')}}"
                                               data-swal-text="{{ trans('sw.disable_without_refund')}}"
                                               href="{{route('sw.deleteMember',$member->id).'?refund=0'}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-secondary btn-sm" title="{{ trans('sw.disable_without_refund')}}">
                                                <i class="ki-outline ki-trash fs-2"></i>
                                            </a> --}}
                                            @if(@$member->member_subscription_info)
                                            <a title="{{ trans('sw.disable_with_refund', ['amount' => $member->member_subscription_info->amount_paid])}}"
                                               data-swal-text="{{ trans('sw.disable_with_refund', ['amount' => $member->member_subscription_info->amount_paid])}}"
                                               data-swal-amount="{{@$member->member_subscription_info->amount_paid}}"
                                               href="{{route('sw.deleteMember',$member->id).'?refund=1&total_amount='.@$member->member_subscription_info->amount_paid}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="{{ trans('sw.disable_with_refund', ['amount' => $member->member_subscription_info->amount_paid])}}">
                                                <i class="ki-outline ki-trash fs-2"></i>
                                            </a>
                                            @endif
                                            <!--end::Delete-->
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- start model pay -->
                        <div class="modal" id="modalPays_{{$member->id}}">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content modal-content-demo">
                                    <div class="modal-header">
                                        <h6 class="modal-title">{{ trans('sw.total_amount_remaining')}}</h6>
                                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                                aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <h6 style="font-weight: bolder">{{$member->name}}</h6>
                                        <div class="m-datatable">
                                            <div class="m-row m-header">
                                                <div class="m-cell"><i class="fa fa-list"></i> {{ trans('sw.membership')}}</div>
                                                <div class="m-cell"><i class="fa fa-dollar"></i> {{ trans('sw.amount_remaining')}}</div>
                                                <div class="m-cell"><i class="fa fa-gears"></i> {{ trans('admin.actions')}}</div>
                                            </div>
                                            @php $remain_amount_subscriptions_check = false @endphp
                                            @if(@count($member->member_remain_amount_subscriptions) > 0)
                                                @php $remain_amount_subscriptions_check = true @endphp
                                                @foreach($member->member_remain_amount_subscriptions as $member_remain_amount)
                                            <div class="m-row" id="tr_pay_{{$member_remain_amount->id}}">
                                                <div class="m-cell">{{@$member_remain_amount->subscription->name}}</div>
                                                <div class="m-cell" id="td_pay_amount_remaining_{{$member_remain_amount->id}}">{{@number_format($member_remain_amount->amount_remaining, 2)}}</div>
                                                <div class="m-cell"><a data-target="#modalPay" data-toggle="modal" href="#"
                                                                       id="{{@$member_remain_amount->id}}" style="cursor: pointer;"
                                                                       class="btn  btn-sm purple rounded-3 btn-indigo"
                                                                       title="{{ trans('sw.pay')}}"><i class="fa fa-dollar" style="width: 10px;"></i></a></div>
                                            </div>

                                            @endforeach
                                            @endif
                                        </div>
                                        @if($remain_amount_subscriptions_check == false)
                                        <div class="row" >
                                            <div class="text-center" >{{ trans('admin.no_records')}}</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- End model pay -->



                    @endforeach
                    </tbody>
                </table>
            </div>
            <!--end::Table-->
            
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
                        <i class="ki-outline ki-user fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Members-->



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
                    <h6 id="payMemberName" style="font-weight: bolder">{{ trans('sw.amount_paid')}}</h6>
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
                                        <option value="{{$payment_type->payment_id}}" @if(@old('payment_type',$order->payment_type) == $payment_type->payment_id) selected="" @endif>{{$payment_type->name}}</option>
                                    @endforeach
{{--                                    <option value="{{\Modules\Software\Classes\TypeConstants::CASH_PAYMENT}}" >{{ trans('sw.payment_cash')}}</option>--}}
{{--                                    <option value="{{\Modules\Software\Classes\TypeConstants::ONLINE_PAYMENT}}" >{{ trans('sw.payment_online')}}</option>--}}
{{--                                    <option value="{{\Modules\Software\Classes\TypeConstants::BANK_TRANSFER_PAYMENT}}" >{{ trans('sw.payment_bank_transfer')}}</option>--}}
                                </select>
                            </div><!-- end pay qty  -->
                        </div>
                        
                        @if(@$mainSettings->active_loyalty)
                        <!--begin::Loyalty Points Earning Info-->
                        <div class="alert alert-dismissible bg-light-success border border-success border-dashed d-flex flex-column flex-sm-row p-4 mb-3 mt-3" id="pay_loyalty_earning_info" style="display: none !important;">
                            <i class="ki-outline ki-gift fs-2hx text-success me-3 mb-3 mb-sm-0"></i>
                            <div class="d-flex flex-column pe-0 pe-sm-5">
                                <h6 class="mb-1">{{ trans('sw.points_earning_info')}}</h6>
                                <span class="text-gray-700 fs-7">{!! trans('sw.you_will_earn_points', ['points' => '<span id="pay_estimated_earning_points" class="fw-bold text-success">0</span>'])!!}</span>
                                <span class="text-gray-600 fs-8" id="pay_loyalty_earning_rate"></span>
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
    <!-- start model pay -->
    <div class="modal" id="modalCredits">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="ki-outline ki-dollar fs-2 me-2"></i>{{ trans('sw.add_credit')}}
                    </h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Current Balance Info -->
                    <div class="mb-4">
                        <div id="credits_score" class="alert alert-success mb-0">
                            <i class="ki-outline ki-wallet fs-3 me-2"></i>{{ trans('sw.balance')}}: 
                            <span id="credits_balance" class="fw-bold">0</span>
                        </div>
                        <div id="credits_score_error"></div>
                        <div id="modalPayResult"></div>
                    </div>

                    <input type="hidden" name="credit_member_id" id="credit_member_id" value="">

                    <!-- Transaction Type -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ trans('sw.type')}} <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="credit_type" id="credit_type_add" 
                                           value="0" onclick="show_credit_add()" required>
                                    <label class="form-check-label fw-bold text-success" for="credit_type_add">
                                        <i class="ki-outline ki-plus fs-4 me-2"></i>{{ trans('sw.store_add')}}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="credit_type" id="credit_type_refund" 
                                           value="1" onclick="show_credit_refund()" required>
                                    <label class="form-check-label fw-bold text-danger" for="credit_type_refund">
                                        <i class="ki-outline ki-minus fs-4 me-2"></i>{{ trans('sw.store_refund')}}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Amount Input - Add Credit -->
                    <div class="mb-4" id="credit_amount_add_div" style="display: none;">
                        <label class="form-label fw-bold">{{ trans('sw.credit_amount_add')}} <span class="text-danger">*</span></label>
                        <input name="credit_amount_add" class="form-control form-control-solid" type="number" 
                               id="credit_amount_add" step="0.01" min="0"
                               placeholder="{{ trans('sw.enter_credit_amount_add')}}">
                    </div>

                    <!-- Amount Input - Refund Credit -->
                    <div class="mb-4" id="credit_amount_refund_div" style="display: none;">
                        <label class="form-label fw-bold">{{ trans('sw.credit_amount_refund')}} <span class="text-danger">*</span></label>
                        <input name="credit_amount_refund" class="form-control form-control-solid" type="number" 
                               id="credit_amount_refund" step="0.01" min="0"
                               placeholder="{{ trans('sw.enter_credit_amount_refund')}}">
                    </div>

                    <!-- Payment Type -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ trans('sw.payment_type')}}</label>
                        <select class="form-control form-control-solid" name="credit_payment_type" id="credit_payment_type">
                            @foreach($payment_types as $payment_type)
                                <option value="{{$payment_type->payment_id}}" 
                                        @if(@old('payment_type',$order->payment_type) == $payment_type->payment_id) selected @endif>
                                    {{$payment_type->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ trans('sw.notes')}}</label>
                        <textarea name="credit_notes" placeholder="{{ trans('sw.enter_notes')}}" 
                                  class="form-control form-control-solid" rows="3" id="credit_notes"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button">
                            {{ trans('sw.cancel')}}
                        </button>
                        <button class="btn btn-primary" id="form_credit_add_btn" onclick="add_to_member_credit();" style="display: none;">
                            <i class="ki-outline ki-plus fs-2 me-2"></i>{{ trans('sw.addition')}}
                        </button>
                        <button class="btn btn-danger" id="form_credit_refund_btn" onclick="add_to_member_credit();" style="display: none;">
                            <i class="ki-outline ki-minus fs-2 me-2"></i>{{ trans('sw.withdraw')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- End model pay -->



    <!-- start model pay -->
    <div class="modal" id="modalFreezeForm">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="ki-outline ki-cross-circle fs-2 me-2"></i>
                        {{ trans('sw.freeze_account')}}
                    </h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="freeze_form_alert" style="display:none;"></div>
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-muted fw-bold">{{ trans('sw.name')}}</div>
                                <div id="freeze_member_name_form" class="fw-semibold">-</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted fw-bold">{{ trans('sw.membership')}}</div>
                                <div id="freeze_subscription_name_form" class="fw-semibold">-</div>
                            </div>
                        </div>
                    </div>

                    <form id="freeze_form" action="" method="get">
                        <input type="hidden" id="freeze_member_id" name="member_id" value="">

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="form-label fw-bold">{{ trans('sw.start_freeze_date')}} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="freeze_start_date_input" name="start_date" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label fw-bold">{{ trans('sw.end_freeze_date')}} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="freeze_end_date_input" name="end_date" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <span class="badge badge-light-info w-100" id="freeze_limit_badge">{{ trans('sw.freeze_limit')}}: 0</span>
                            </div>
                            <div class="col-md-6">
                                <span class="badge badge-light-primary w-100" id="freeze_times_left_badge">{{ trans('sw.number_times_freeze')}}: 0</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted">{{ trans('sw.freeze_reminder_days')}}: <span id="freeze_days_count">0</span></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">{{ trans('sw.reason')}}</label>
                            <textarea class="form-control" name="reason" id="freeze_reason" rows="2" placeholder="{{ trans('sw.enter_reason')}}"></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">{{ trans('sw.admin_notes')}}</label>
                            <textarea class="form-control" name="admin_note" id="freeze_admin_note" rows="2" placeholder="{{ trans('sw.enter_notes')}}"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('sw.cancel')}}</button>
                            <button type="submit" class="btn btn-primary" id="freeze_submit_btn">
                                <i class="ki-outline ki-check fs-2 me-2"></i>{{ trans('sw.freeze_account')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- End modal freeze form -->
    <div class="modal" id="modalFreezeInfo">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.freeze_account')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="row modal-body" style="margin: 0 15px;">
                    <h4 style="font-weight: bolder;margin-bottom: 15px" id="freeze_member_name"></h4>

                    <ul class="col-md-6" style="list-style-type: none;line-height: 32px;">
                        <li><b>{{ trans('sw.start_freeze_date')}}: </b> <span id="freeze_start_date"></span></li>
                        <li><b>{{ trans('sw.end_freeze_date')}}: </b> <span id="freeze_end_date"></span></li>
                        <li><b>{{ trans('sw.freeze_reminder_days')}}: </b> <span id="freeze_reminder_days"></span></li>
                    </ul>
                    <ul class="col-md-6" style="list-style-type: none;line-height: 32px;">
                        <li><b>{{ trans('sw.number_times_freeze')}}: </b> <span id="number_times_freeze"></span></li>
                        <li><b>{{ trans('sw.freeze_limit')}}: </b> <span id="freeze_limit"></span></li>
                        <li><b>{{ trans('sw.number_times_freeze_reminder')}}: </b> <span id="number_times_freeze_reminder"></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- End model pay -->


    <!-- start model pay -->
    <div class="modal" id="modalReservation">
        <div class="modal-dialog  modal-xl" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.reservations')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 id="payMemberName" style="font-weight: bolder">&nbsp;</h6>


                    <div class="row">

                        <div class="row col-md-12">

                            {{--                            <div class="form-group col-md-6">--}}
                            {{--                                <label class="col-md-3 control-label">{{ trans('sw.member_id')}} </label>--}}
                            {{--                                <div class="col-md-9">--}}
                            {{--                                    <div class="input-group">--}}
                            {{--											<span class="input-group-addon">--}}
                            {{--											<i class="fa fa-search"></i>--}}
                            {{--											</span>--}}

                            {{--                                        <input id="member_id" value="{{ old('member_id') }}"--}}
                            {{--                                               placeholder="{{ trans('sw.enter_member_id')}}"--}}
                            {{--                                               name="member_id" type="text" class="form-control"  autocomplete="off" >--}}
                            {{--                                    </div>--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}

                            <div class="form-group col-md-6">
                                {{--                <label class="col-md-3  control-label"> </label>--}}

                                <div class="well">
                                    <div class="row">
                                        <address  class="col-md-6">
                                            <strong>{{ trans('sw.name')}}:</strong>
                                            <span id="store_member_name">-</span>
                                        </address>
                                        <address  class="col-md-6">
                                            <strong>{{ trans('sw.phone')}}:</strong>
                                            <span id="store_member_phone">-</span>
                                        </address>
                                    </div>

                                    <address>
                                        <strong>{{ trans('sw.reservations')}}:</strong><br><br>
                                        <span id="member_reservations">-</span>
                                    </address>

                                </div>

                            </div>

                            <div class="col-md-6"><div id="activity_icons"></div></div>
                            <div class="form-group col-md-12 clearfix"><hr/></div>
                        </div>

                        <div style="clear: none;padding-bottom: 15px"></div>


                        <div class="col-md-12 text-center"><div id="picker"></div></div>
                        {{--        <div>--}}
                        {{--            <p>Selected date: <span id="selected-date"></span></p>--}}
                        {{--            <p>Selected time: <span id="selected-time"></span></p>--}}
                        {{--        </div>--}}
                        <input type="hidden" id="selected_date" value="">
                        <input type="hidden" id="selected_time" value="">
                        <input type="hidden" id="selected_reservation_non_member_id" value="">
                        <input type="hidden" id="selected_reservation_activity_id" value="">
                        <input type="hidden" id="selected_reservation_start_date" value="">
                        <input type="hidden" id="selected_reservation_step" value="">

                        <div class="row" style="clear: none;padding-bottom: 15px"><br/></div>

                        <div class="row">
                            <div class="col-xs-8 col-md-12 invoice-block">
                                <a class="btn btn-lg green hidden-print margin-bottom-5 " onclick="create_reservation();">
                                    {{ trans('sw.reservation_complete')}} <i class="fa fa-check"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- End model pay -->

    <!-- start model profile -->

    <!-- start model pay -->
    <div class="modal" id="modalProfileMember">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title" id="modalProfile_member_name"></h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="row modal-body" >
                    <iframe id="modalProfileIframe" style="width: 100%; height: 500px; border: none;"></iframe>

                </div>
            </div>
        </div>
    </div>

    <!-- End model pay -->

    <!-- End model profile -->
@endsection

@section('scripts')
    @parent
    <script   src="{{asset('resources/assets/admin/global/scripts/software/renew_member.js')}}"></script>

    <script src="https://momentjs.com/downloads/moment.js"></script>
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>--}}
    <script type="text/javascript" src="{{asset('resources/assets/admin/global/plugins/pick-hours-availability-calendar/mark-your-calendar.js')}}"></script>
    <script type="text/javascript">
        // Loyalty Points Variables for Pay Remaining Modal
        var payLoyaltyMoneyToPointRate = 0;
        
        @if(@$mainSettings->active_loyalty)
        // Load loyalty earning rate on page load
        $(document).ready(function() {
            $.ajax({
                url: '{{ route('sw.getMemberLoyaltyInfo') }}',
                type: 'GET',
                data: { member_id: 0 },
                success: function(response) {
                    if (response.success && response.money_to_point_rate) {
                        payLoyaltyMoneyToPointRate = response.money_to_point_rate;
                        $('#pay_loyalty_earning_rate').text('{{ trans('sw.earning_rate', ['rate' => '']) }}'.replace(':rate عملة', payLoyaltyMoneyToPointRate.toFixed(2) + ' {{ trans('sw.app_currency') }}').replace(':rate currency', payLoyaltyMoneyToPointRate.toFixed(2) + ' {{ trans('sw.app_currency') }}'));
                    }
                }
            });
            
            // Add event listener for amount_paid in pay modal
            $('#amount_paid').on('change input keyup', function() {
                const amountPaid = parseFloat($(this).val()) || 0;
                if (payLoyaltyMoneyToPointRate > 0 && amountPaid > 0) {
                    const estimatedPoints = Math.floor(amountPaid / payLoyaltyMoneyToPointRate);
                    if (estimatedPoints > 0) {
                        $('#pay_estimated_earning_points').text(estimatedPoints);
                        $('#pay_loyalty_earning_info').slideDown();
                    } else {
                        $('#pay_loyalty_earning_info').slideUp();
                    }
                } else {
                    $('#pay_loyalty_earning_info').slideUp();
                }
            });
        });
        @endif

        function create_reservation(){

            let selected_date = $('#selected_date').val();
            let selected_time = $('#selected_time').val();
            let selected_non_member_id = $('#selected_reservation_non_member_id').val();
            let selected_activity_id = $('#selected_reservation_activity_id').val();
            let selected_start_date = $('#selected_reservation_start_date').val();
            let selected_step = $('#selected_reservation_step').val();
            if(selected_date && selected_time && selected_non_member_id && selected_activity_id) {
                $.get("{{route('sw.createReservationNonMemberAjax')}}", {  selected_date: selected_date, selected_time: selected_time, selected_activity_id: selected_activity_id,selected_non_member_id :selected_non_member_id, type : 2  },
                    function(result){
                        if(result != 'exist') {
                            $('#ul_member_reservations').append('<li class="list-group-item" id="li_reservation_' + result + '"> <i class="fa fa-calendar text-muted"></i>'
                                + moment(selected_date).format('MM-DD-YYYY')
                                + ' <i class="fa fa-clock-o text-muted"></i>'
                                + moment(selected_date).format('YYYY-MM-DD') + ' ' + selected_time
                                + ' <i class="fa fa-user text-muted"></i> '+ $('#store_member_name').text()
                                + ' <span class="badge badge-danger" onclick="remove_reservation(' + result + ', ' + "'" + selected_time + "'" + ')"><i class="fa fa-times"></i></span>'
                                + '</li>');
                            if(result == 'reload'){
                                location.reload();
                            }else{
                                alert('{{ trans('admin.successfully_added')}}');
                            }
                        }else{
                            alert('{{ trans('sw.reservation_member_exist')}}');
                        }

                    }
                );
            }else{
                alert('{{ trans('sw.reservation_input_error')}}');
            }
        }
        function remove_reservation(id, time){
            swal({
                title: trans_are_you_sure,
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: trans_yes,
                cancelButtonText: trans_no_please,
                showLoaderOnConfirm: true,
//                ,closeOnConfirm: false,
//                closeOnCancel: false
                preConfirm: function (isConfirm) {
                    return new Promise(function (resolve, reject) {
                        setTimeout(function () {
                            if (isConfirm) {


                                $.get("{{route('sw.deleteReservationNonMemberAjax')}}", {  id: id, time: time },
                                    function(result){
                                        if(result) {
                                            swal({
                                                title: trans_done,
                                                text: trans_successfully_processed,
                                                type: "success",
                                                timer: 4000,
                                                confirmButtonText: 'Ok',
                                            });
                                        if(result == 'reload'){
                                            location.reload();
                                        }
                                        $('#li_reservation_'+id).remove();
                                        }else{
                                            swal({
                                                title: trans_operation_failed,
                                                text: trans_operation_failed,
                                                type: "error",
                                                timer: 4000,
                                                confirmButtonText: 'Ok',
                                            });
                                        }
                                    }
                                );

                                return false;
                            } else {
                                swal("Cancelled", "Alright, everything still as it is", "info");
                            }
//            });
                        }, 2000)
                    })
                },
                allowOutsideClick: false
            }).then(function (isConfirm) {

            });
            return false;
        }

        function non_membership_reservation(id, activity_id, step, start_date){
            $('#selected_reservation_non_member_id').val(id);
            $('#selected_reservation_activity_id').val(activity_id);
            $('#selected_reservation_start_date').val(start_date);
            $('#selected_reservation_step').val(step);

            let availability = [];
            $.ajax({
                url: '{{route('sw.getNonMemberReservation')}}',
                cache: false,
                type: 'GET',
                data: {'activity_id': activity_id, 'non_member_id': id, 'start_date': start_date, 'step': step, member_type: 2},
                dataType: 'json',
                success: function (response) {
                    $('#store_member_name').html(response.non_member?.name);
                    $('#store_member_phone').html(response.non_member?.phone);

                    let reservations = '<ul class="list-group" id="ul_member_reservations">';
                    if(response.member_reservations){

                        for(let i=0; i < response.member_reservations.length; i++) {
                            reservations += '<li class="list-group-item" id="li_reservation_' + response.member_reservations[i].id + '"> <i class="fa fa-calendar text-muted"></i>'
                                + moment(response.member_reservations[i].date).format('L')
                                + ' <i class="fa fa-clock-o text-muted"></i>'
                                + response.member_reservations[i].date
                                + ' <i class="fa fa-user text-muted"></i> '+ (response.member_reservations[i]?.non_member?.name || response.member_reservations[i]?.member?.name)
                                + ' <span class="badge badge-danger" onclick="remove_reservation(' + response.member_reservations[i].id + ', ' + "'" + response.member_reservations[i].date + "'" + ')"><i class="fa fa-times"></i></span>'
                                + '</li>';
                        }
                    }
                    reservations+='</ul>';
                    $('#member_reservations').html(reservations);
                    let activity_name = $('#activity_'+id+'_'+activity_id).html(); //document.querySelector('#activity_'+id+'_'+activity_id);
                    let start_date = response.start_date || '{{\Carbon\Carbon::now()->subDay(@\Carbon\Carbon::now()->dayOfWeek)->format('Y-m-d')}}';
                    $('#activity_icons').html('<button class="btn btn-primary btn-md rounded-3">' + activity_name + '</botton>');

                    if(response?.reservation_check === 0) {
                        availability = response.reservations;

                        // https://www.jqueryscript.net/time-clock/pick-hours-availability-calendar.html#google_vignette
                        // $('#myc-next-week').hide();
                        // $('#myc-prev-week').hide();
                        $('#picker').markyourcalendar({
                            months: ['{{ trans('sw.jan')}}','{{ trans('sw.feb')}}','{{ trans('sw.mar')}}','{{ trans('sw.apr')}}','{{ trans('sw.may')}}','{{ trans('sw.jun')}}','{{ trans('sw.jul')}}','{{ trans('sw.aug')}}','{{ trans('sw.sep')}}','{{ trans('sw.oct')}}','{{ trans('sw.nov')}}','{{ trans('sw.dec')}}'],
                            weekdays: ['{{ trans('sw.sun')}}','{{ trans('sw.mon')}}','{{ trans('sw.tue')}}','{{ trans('sw.wed')}}','{{ trans('sw.thurs')}}','{{ trans('sw.fri')}}','{{ trans('sw.sat')}}'],

                            availability: availability,
                            startDate: new Date(start_date),
                            onClick: function(ev, data) {
                                // data is a list of datetimes
                                var d = data[0].split(' ')[0];
                                var t = data[0].split(' ')[1];
                                $('#selected_date').val(d);
                                $('#selected_time').val(t);

                                ev.addClass('selected');
                                // $('#selected-date').html(d);
                                // $('#selected-time').html(t);
                            },prevHtml : '<a onclick="non_membership_reservation('+ id +', '+ activity_id +', '+1+', ' + '\'' + start_date + '\'' + ')" id="myc-prev-week"><</a>',nextHtml : '<a onclick="non_membership_reservation('+ id +', '+ activity_id +', '+2+', ' + '\'' + start_date + '\'' + ')" id="myc-next-week">></a>'
                            , onClickNavigator: function(ev, instance) {
                                console.log(instance);
                                console.log(ev);
                                console.log(instance[0].split(' ')[0]);
                                //     var arr = [
                                //         [
                                //             ['4:01', '5:00', '6:00', '7:00', '8:01'],
                                //             ['1:00', '5:00'],
                                //             ['2:00', '5:00'],
                                //             ['3:30'],
                                //             ['2:00', '5:00'],
                                //             ['2:00', '5:00'],
                                //             ['2:00', '5:00']
                                //         ],
                                //         [
                                //             ['2:00', '5:00'],
                                //             ['4:00', '5:00', '6:00', '7:00', '8:00'],
                                //             ['4:00', '5:00'],
                                //             ['2:00', '5:00'],
                                //             ['2:00', '5:00'],
                                //             ['2:00', '5:00'],
                                //             ['2:00', '5:00']
                                //         ],
                                //         [
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00', '6:00', '7:00', '8:00'],
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00']
                                //         ],
                                //         [
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00', '6:00', '7:00', '8:00'],
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00']
                                //         ],
                                //         [
                                //             ['4:00', '6:00'],
                                //             ['4:00', '6:00'],
                                //             ['4:00', '6:00'],
                                //             ['4:00', '6:00'],
                                //             ['4:00', '5:00', '6:00', '7:00', '8:00'],
                                //             ['4:00', '6:00'],
                                //             ['4:00', '6:00']
                                //         ],
                                //         [
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00'],
                                //             ['4:00', '5:00', '6:00', '7:00', '8:00'],
                                //             ['3:00', '6:00']
                                //         ],
                                //         [
                                //             ['3:00', '4:00'],
                                //             ['3:00', '4:00'],
                                //             ['3:00', '4:00'],
                                //             ['3:00', '4:00'],
                                //             ['3:00', '4:00'],
                                //             ['3:00', '4:00'],
                                //             ['4:00', '5:00', '6:00', '7:00', '8:00']
                                //         ]
                                //     ]
                                //     var rn = Math.floor(Math.random() * 10) % 7;
                                //     instance.setAvailability(arr[rn]);
                            }
                        });

                    }else if(response?.reservation_check === 1) {
                        $('#picker').html('<div class="alert alert-danger">{{ trans('sw.member_time_reservation_activity_exceed_limit_error')}}</div>');
                    }else{
                        $('#picker').html('<div class="alert alert-danger">{{ trans('sw.no_dates_available_for_activity_error')}}</div>');

                    }

                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });


        }

    </script>
    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    <script>
         function list_renew_membership(id) {
             let attr_id = id;
             $('#renew_member_id').val(attr_id);
            // $('#btn_renew_membership').before('<input value="' + attr_id + '"  id="renew_member_id"   hidden>');
            getRenewMembershipsSelection(attr_id);

             let expire_date = $('#list_member_'+id);
             let expire_date_msg = expire_date.attr('expire_msg');
             let expire_date_color = expire_date.attr('expire_color');
             let expire_message = '<span style="color: '+expire_date_color+'">'+expire_date_msg+'</span>';
             $('#membership_expire_date_msg').html(expire_message);

{{--             @if($mainSettings->active_zk)--}}
{{--             fingerprint_open_popup();--}}
{{--             @endif--}}

            return false;
        }
        // $('#btn_renew_membership').on('click', function (e) {
        //     e.preventDefault();
        //     var attr_id = $('#renew_member_id').val();
        //     alert(attr_id);
        //     setRenewMembershipStore(attr_id);
        //     return false;
        // });
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
            let id = $('#pay_id').val();
            let amount_paid = $('#amount_paid').val();
            let payment_type = $('#payment_type').val();
            $('#modalPayResult').show();
            $.ajax({
                url: '{{route('sw.createMemberPayAmountRemainingForm')}}',
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {id: id, amount_paid: amount_paid, payment_type: payment_type},
                success: function (response) {
                    if (response == '1') {
                        $('#modalPayResult').html('<div class="alert alert-success">{{ trans('admin.successfully_paid')}}</div>');
                        let amount_remaining = $('#td_pay_amount_remaining_'+id).text();
                        if(amount_paid === amount_remaining){
                            $('#tr_pay_'+id).remove();
                        }else{
                            let result_amount_remaining = Math.round(amount_remaining) - Math.round(amount_paid);
                            $('#td_pay_amount_remaining_'+id).text(result_amount_remaining);
                            $('#span_amount_remaining_'+id).text(result_amount_remaining);
                            let span_total_amount_remaining = $('#span_total_amount_remaining_'+id).text();
                            let result_total_amount_remaining = Math.round(span_total_amount_remaining) - Math.round(amount_paid);
                            $('#span_total_amount_remaining_'+id).text(result_total_amount_remaining);
                        }
                        //location.reload();
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
            $('button[type="reset"]').on('click', function() {
                setTimeout(() => {
                    $(this).closest('form').find('select').trigger('change');
                }, 100);
            });
        });


        // replaced by form modal: .open_freeze_modal
        $(document).on('click', '.open_freeze_modal', function (e){
            e.preventDefault();
            const memberId = $(this).data('member_id');
            const memberName = $(this).data('member_name');
            const subscriptionName = $(this).data('subscription_name');
            const freezeLimit = parseInt($(this).data('freeze_limit')) || 0;
            const timesLeft = parseInt($(this).data('times_left')) || 0;

            $('#freeze_member_name_form').text(memberName || '-');
            $('#freeze_subscription_name_form').text(subscriptionName || '-');
            $('#freeze_member_id').val(memberId);
            $('#freeze_limit_badge').text('{{ trans('sw.freeze_limit')}}: ' + freezeLimit);
            $('#freeze_times_left_badge').text('{{ trans('sw.number_times_freeze')}}: ' + timesLeft);

            const today = new Date();
            const pad = n => (n<10 ? '0'+n : ''+n);
            const toYmd = d => d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate());
            const start = toYmd(today);
            const endDate = new Date(today.getFullYear(), today.getMonth(), today.getDate() + (freezeLimit > 0 ? freezeLimit : 1));
            const end = toYmd(endDate);
            $('#freeze_start_date_input').val(start).attr('min', start);
            $('#freeze_end_date_input').val(end).attr('min', start);
            updateFreezeDays();

            $('#freeze_form_alert').hide().html('');
            $('#modalFreezeForm').modal('show');
        });

        function updateFreezeDays(){
            const start = $('#freeze_start_date_input').val();
            const end = $('#freeze_end_date_input').val();
            if(!start || !end){ 
                $('#freeze_days_count').text('0');
                return; 
            }
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const endD = new Date(end);
            endD.setHours(0, 0, 0, 0);
            // Calculate days remaining from today to end date
            const daysRemaining = Math.max(0, Math.ceil((endD - today) / (1000*60*60*24)));
            $('#freeze_days_count').text(daysRemaining);
        }
        $(document).on('change', '#freeze_start_date_input, #freeze_end_date_input', function(){
            const start = $('#freeze_start_date_input').val();
            const end = $('#freeze_end_date_input').val();
            if(start && end && new Date(end) < new Date(start)){
                $('#freeze_end_date_input').val(start);
            }
            updateFreezeDays();
        });

        $(document).on('submit', '#freeze_form', function(e){
            e.preventDefault();
            const memberId = $('#freeze_member_id').val();
            const start = $('#freeze_start_date_input').val();
            const end = $('#freeze_end_date_input').val();
            const reason = $('#freeze_reason').val();
            const adminNote = $('#freeze_admin_note').val();
            const freezeLimit = ($('#freeze_limit_badge').text().match(/(\d+)/) || [0,0])[1] | 0;
            const timesLeft = ($('#freeze_times_left_badge').text().match(/(\d+)/) || [0,0])[1] | 0;

            const days = Math.max(0, Math.ceil((new Date(end) - new Date(start)) / (1000*60*60*24)));
            const $alert = $('#freeze_form_alert');
            $alert.hide().html('');
            if(!start || !end){
                $alert.html('<div class="alert alert-danger">{{ trans('sw.reservation_input_error')}}</div>').show();
                return false;
            }
            if(timesLeft <= 0){
                $alert.html('<div class="alert alert-danger">{{ trans('sw.number_times_freeze_reminder')}}: 0</div>').show();
                return false;
            }
            if(freezeLimit > 0 && days > freezeLimit){
                $alert.html('<div class="alert alert-warning">{{ trans('sw.freeze_limit')}}: '+freezeLimit+'</div>').show();
                return false;
            }

            var route = "{{route('sw.freezeMember', ['id' => 'member_id'])}}";
            var url = route.replace('member_id', memberId);
            const qs = $.param({ start_date: start, end_date: end, reason: reason, admin_note: adminNote });
            window.location.href = url + (url.indexOf('?') === -1 ? '?' : '&') + qs;
            return false;
        });

        function fingerprint_refresh(){
            $('#fingerprint_refresh').hide().after('<div class="col-md-12"><div class="loader"></div></div>');
            $.ajax({
                url: '{{route('sw.fingerprintRefresh')}}',
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {},
                success: function (response) {
                    if(response === '1') {
                        $('#fingerprint_refresh').after(
                            '<button class="btn btn-success btn-block rounded-3 " disable>' +
                            ' <i class="fa fa-check mx-1"></i> ' +
                            "{{ trans('admin.successfully_processed')}}" +
                            '</button>');
                        $('#fingerprint_error_msg').hide();
                    }else{
                        $('#fingerprint_refresh').after(
                            '<button class="btn btn-danger btn-block rounded-3 " id="fingerprint_refresh" onclick="fingerprint_refresh()">' +
                            ' <i class="fa fa-times mx-1"></i> ' +
                            "{{ trans('sw.zk_not_connect')}}" +
                            '</button>');

                        $('#fingerprint_error_msg').show();
                    }
                    $('#fingerprint_refresh').remove();
                    $('.loader').hide();
                    {{--if (response == '1') {--}}
                    {{--    $('#modalPayResult').html('<div class="alert alert-success">{{ trans('admin.successfully_paid')}}</div>');--}}
                    {{--    location.reload();--}}
                    {{--} else {--}}
                    {{--    $('#modalPayResult').html('<div class="alert alert-danger">' + response + '</div>');--}}
                    {{--}--}}

                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });

        }

         function fingerprint_open_popup() {
             const myWindow = window.open("{{env('APP_ZK_LOCAL_HOST')}}", "", "width=20, height=20");
             setTimeout(function() {myWindow.close()}, 5000);
         }
         @if(@$mainSettings->active_zk && (request('reload') == 1))
             fingerprint_open_popup();
        @endif

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
                        window.location.replace("{{asset(route('sw.listMember'))}}");
                    }, 500);
                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });
        }


        function show_freeze_date(member_name, start_date, end_date, freeze_limit, number_times_freeze_reminder, freeze_reminder_days, number_times_freeze ){
            $('#freeze_member_name').html(member_name);
            $('#freeze_start_date').html(start_date);
            $('#freeze_end_date').html(end_date);
            $('#freeze_reminder_days').html(freeze_reminder_days);
            $('#number_times_freeze_reminder').html(number_times_freeze_reminder);
            $('#number_times_freeze').html(number_times_freeze);
            $('#freeze_limit').html(freeze_limit);
            $('#modalFreezeInfo').show();
        }

         function datediff(first, second) {
             return Math.round((second - first) / (1000 * 60 * 60 * 24));
         }


         $(document).on('click', '.modalProfileBtn', function (event) {
             // Dynamically set the iframe src
             let member_id = $(this).attr('member_id');
             let member_name = $(this).attr('member_name');
             $('#modalProfile_member_name').html(member_name);
             let member_profile_url = '{{route('sw.showMemberProfile', ':id')}}';
              document.getElementById('modalProfileIframe').src = member_profile_url.replace(':id', member_id);
             $('#modalProfile').show();
         });

         function show_profile_member(member_id ){
             $('#modalProfileMember').show();
         }

         $('#credit_amount_refund_div').hide()
         $('#credit_amount_add_div').hide()
         $('#form_credit_add_btn').hide()
         $('#form_credit_refund_btn').hide()
         function show_credit_add(){
             $('#credit_amount_add_div').show()
             $('#credit_amount_refund_div').hide()
             $('#form_credit_add_btn').show()
             $('#form_credit_refund_btn').hide()
         }
         function show_credit_refund(){
             $('#credit_amount_refund_div').show()
             $('#credit_amount_add_div').hide()
             $('#form_credit_refund_btn').show()
             $('#form_credit_add_btn').hide()
         }
         function modal_credits(id){
             $('#modalCredits').modal();
             $('#credit_member_id').val(id);

             $.ajax({
                 url: '{{route('sw.creditMemberBalance')}}',
                 cache: false,
                 type: 'GET',
                 dataType: 'text',
                 data: {member_id: id},
                 success: function (response) {
                     if(response > 0)
                        $('#credits_balance').addClass('member_balance_more').html(response);
                     else
                         $('#credits_balance').addClass('member_balance_less').html(response);
                 },
                 error: function (request, error) {
                     swal("Operation failed", "Something went wrong.", "error");
                     console.error("Request: " + JSON.stringify(request));
                     console.error("Error: " + JSON.stringify(error));
                 }
             });
         }

         function modal_credits(member_id){
             $('#credit_member_id').val(member_id);
             $('#modalCredits').modal('show');
             
             // Load member balance
             $.ajax({
                 url: '{{route('sw.creditMemberBalance')}}',
                 cache: false,
                 type: 'GET',
                 dataType: 'text',
                 data: {member_id: member_id},
                 success: function (response) {
                     if(response > 0)
                        $('#credits_balance').addClass('member_balance_more').removeClass('member_balance_less').html(response);
                     else
                         $('#credits_balance').addClass('member_balance_less').removeClass('member_balance_more').html(response);
                 },
                 error: function (request, error) {
                     $('#credits_balance').html('0');
                     console.error("Request: " + JSON.stringify(request));
                     console.error("Error: " + JSON.stringify(error));
                 }
             });
             return false;
         }

         function show_credit_add(){
             $('#credit_amount_add_div').show();
             $('#credit_amount_refund_div').hide();
             $('#form_credit_add_btn').show();
             $('#form_credit_refund_btn').hide();
         }

         function show_credit_refund(){
             $('#credit_amount_add_div').hide();
             $('#credit_amount_refund_div').show();
             $('#form_credit_add_btn').hide();
             $('#form_credit_refund_btn').show();
         }

         function add_to_member_credit(){
             $('#credits_score_error').hide();
             var id = $('#credit_member_id').val()
             var data = {
                 'member_id': id,
                 'type': $('#credit_type:checked').val(),
                 'amount_add': $('#credit_amount_add').val(),
                 'amount_refund': $('#credit_amount_refund').val(),
                 'payment_type': $('#credit_payment_type').val(),
                 'notes': $('#credit_notes').val(),
                 "_token": "{{ csrf_token() }}"
             }

             var url = '{{route('sw.creditMemberBalanceAdd', ':id')}}';
             var myurl = url.replace(':id', id);
             $.ajax({
                 url: myurl,
                 data: data,
                 type: "post",
                 success: (data) => {
                     console.log(data);
                        if(data === 'no_balance'){
                            $('#credits_score_error').show().html('<div class="alert alert-danger">{{ trans('sw.no_enough_balance_error')}}</div>')
                        }else {
                            // $("#global-loader").hide();
                            $('#modalCredits').modal('hide');
                            var lang = 'ar';
                            var isRtl = (lang === 'ar');
                            $("#credit_type:checked").prop("checked", false);
                            $('#credit_amount_add').val('');
                            $('#credit_amount_refund').val('');
                            $('#credit_notes').val('');
                            $('#form_credit_add_btn').hide();
                            $('#form_credit_refund_btn').hide();
                            $('#credits_balance').val(data);
                            swal({
                                title: trans_done,
                                text: trans_successfully_processed,
                                type: "success",
                                timer: 4000,
                                confirmButtonText: 'Ok',
                            });
                        }

                 },
                 error: (reject) => {
                     var response = $.parseJSON(reject.responseText);
                     console.log(response);
                 }
             });
             return false;
         }
    </script>

@endsection
