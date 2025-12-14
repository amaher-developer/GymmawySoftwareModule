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
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/new_front/global/plugins/pick-hours-availability-calendar/mark-your-calendar.css')}}">
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
            position: relative;
        }

        .actions-column .actions-menu {
            min-width: 240px;
            z-index: 1050;
        }

        .actions-column .menu-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .actions-column .menu-link i {
            font-size: 1rem;
        }

        .members-table-responsive {
            overflow-x: auto;
            overflow-y: visible;
            position: relative;
        }

        /* Allow dropdowns to escape the table container on iOS Safari */
        @supports (-webkit-touch-callout: none) {
            .members-table-responsive {
                overflow: visible !important;
            }
        }

        @media (max-width: 1200px) {
            .actions-column {
                min-width: 120px !important;
            }
        }

        @media (max-width: 992px) {
            .actions-column {
                min-width: 100px !important;
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
@php
    // Check if activity reservation feature is enabled
    $features = is_array($mainSettings->features ?? null) 
        ? $mainSettings->features 
        : (is_string($mainSettings->features ?? null) 
            ? json_decode($mainSettings->features, true) 
            : []);
    $active_activity_reservation = isset($features['active_activity_reservation']) && $features['active_activity_reservation'];
@endphp

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
                
                @if($active_activity_reservation)
                <!--begin::Calendar Button-->
                <a href="{{route('sw.listReservation')}}" class="btn btn-sm btn-flex btn-light-info">
                    <i class="ki-outline ki-calendar fs-6"></i>
                    {{ trans('sw.activities_calender')}}
                </a>
                <!--end::Calendar Button-->
                @endif

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
                    <div class="row g-6 mt-0">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.remaining_store_status')}}</label>
                            @php
                                $selectedStoreStatus = request()->has('remaining_store_status') ? (string)request('remaining_store_status') : '';
                            @endphp
                            <select name="remaining_store_status" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.choose_remaining_store_status')}}...</option>
                                <option value="1" @selected($selectedStoreStatus === '1')>{{ trans('sw.store_balance_positive')}}</option>
                                <option value="2" @selected($selectedStoreStatus === '2')>{{ trans('sw.store_balance_negative')}}</option>
                                <option value="0" @selected($selectedStoreStatus === '0')>{{ trans('sw.store_balance_zero')}}</option>
                            </select>
                        </div>
                        @if(@env('APP_ZK_GATE'))
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.fp_id')}}</label>
                            <input type="text" class="form-control" name="fp_id" value="{{ request('fp_id') }}" placeholder="{{ trans('sw.enter_fp_id')}}">
                        </div>
                        @endif
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
                @if(request()->has('remaining_store_status'))
                    <input type="hidden" name="remaining_store_status" value="{{ request('remaining_store_status') }}">
                @endif
                @if(@env('APP_ZK_GATE') && request('fp_id'))
                    <input type="hidden" name="fp_id" value="{{ request('fp_id') }}">
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
            <div class="table-responsive members-table-responsive">
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
                                               href="{{route('sw.downloadMemberBarcode', $member->id)}}"
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
                                @if($has_coming)<span class="badge bg-secondary ">{{ trans('sw.coming')}}</span>@endif
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
                                                    return '<button class="btn btn-'.(@$name['training_times'] > @$name['visits'] ? 'primary' : 'gray').' btn-sm rounded-2" id="activity_'.@$member->id.'_'.@$name['activity']['id'].'"  style="font-size: 10px; padding: 2px 6px;">'.$name['activity']['name_'.$lang].'</button>';
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
                                    <span class="text-muted fs-7 d-flex align-items-center gap-1 mt-1">
                                        <i class="ki-outline ki-wallet fs-6"></i>
                                        <span>{{ trans('sw.store_balance')}}: {{ number_format($member->store_balance ?? 0, 2) }}</span>
                                    </span>
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
                                <a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    {{ trans('admin.actions') }}
                                    <i class="ki-outline ki-down fs-5 ms-1"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-250px py-4 actions-menu" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0)" class="menu-link px-3 modalProfileBtn"
                                           data-target="#modalProfileMember"
                                           data-toggle="modal"
                                           onclick="show_profile_member('{{$member->id}}')"
                                           member_id="{{$member->id}}"
                                           member_name="{{$member->name}}"
                                           title="{{ trans('sw.member_profile')}}">
                                            <i class="ki-outline ki-user text-primary"></i>
                                            <span>{{ trans('sw.member_profile')}}</span>
                                        </a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="https://web.whatsapp.com/send?phone={{ ((substr( $member->phone, 0, 1 ) === '+') || (substr( $member->phone, 0, 2 ) === '00')) ? $member->phone : '+'.env('APP_COUNTRY_CODE').$member->phone}}"
                                           target="_blank" class="menu-link px-3" title="{{ trans('sw.whatsapp')}}">
                                            <i class="ki-outline ki-message-text-2 text-success"></i>
                                            <span>{{ trans('sw.whatsapp')}}</span>
                                        </a>
                                    </div>
                                    @if(in_array('memberSubscriptionRenewStore', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0)"
                                           class="menu-link px-3"
                                           onclick="list_renew_membership('{{@$member->member_subscription_info->id}}')"
                                           expire_msg="{{ trans('sw.expire_date_msg', ['date' => @\Carbon\Carbon::parse($member->member_subscription_info->expire_date)->toDateString()])}}"
                                           expire_color="@if(@$member->member_subscription_info->status == 0) green @else red @endif"
                                           id="list_member_{{@$member->member_subscription_info->id}}"
                                           data-target="#modelRenew" data-toggle="modal"
                                           title="{{ trans('sw.renew_membership')}}">
                                            <i class="ki-outline ki-arrows-circle text-info"></i>
                                            <span>{{ trans('sw.renew_membership')}}</span>
                                        </a>
                                    </div>
                                    @endif
                                    @if(in_array('createMemberPayAmountRemainingForm', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0)"
                                           data-target="#modalPays_{{$member->id}}" data-toggle="modal"
                                           id="{{@$member->member_subscription_info->id}}"
                                           class="menu-link px-3 btn-indigo btn-indigos"
                                           title="{{ trans('sw.pay_remaining')}}">
                                            <i class="ki-outline ki-dollar text-warning"></i>
                                            <span>{{ trans('sw.pay_remaining')}}</span>
                                        </a>
                                    </div>
                                    @endif
                                    @if(in_array('creditMemberBalanceAdd', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0)"
                                           class="menu-link px-3 btn-indigos"
                                           id="{{@$member->member_subscription_info->id}}"
                                           onclick="modal_credits('{{@$member->id}}')"
                                           title="{{ trans('sw.add_credit')}}">
                                            <i class="ki-outline ki-dollar text-success"></i>
                                            <span>{{ trans('sw.add_credit')}}</span>
                                        </a>
                                    </div>
                                    @endif
                                    @if(@$member->member_subscription_info->id)
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.showOrderSubscription',@$member->member_subscription_info->id)}}"
                                           class="menu-link px-3 btn-indigo"
                                           title="{{ trans('sw.invoice')}}">
                                            <i class="ki-outline ki-document text-primary"></i>
                                            <span>{{ trans('sw.invoice')}}</span>
                                        </a>
                                    </div>
                                    @endif

                                    @if($active_activity_reservation)
                                        @php
                                            $memberReservations = $upcomingReservations[$member->id] ?? collect();
                                        @endphp
                                        @if($memberReservations->count() > 0)
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0)" class="menu-link px-3 position-relative"
                                               title="{{ trans('sw.upcoming_reservations') }} ({{ $memberReservations->count() }})"
                                               data-bs-toggle="modal"
                                               data-bs-target="#upcomingReservationsModal{{ $member->id }}">
                                                <i class="ki-outline ki-calendar-tick text-primary"></i>
                                                <span>{{ trans('sw.upcoming_reservations') }}</span>
                                                <span class="badge badge-circle bg-danger ms-2">{{ $memberReservations->count() }}</span>
                                            </a>
                                        </div>
                                        @endif

                                        @php
                                            $memberActivities = @$member->member_subscription_info->activities ?? [];
                                            $hasValidActivities = false;
                                            if (!empty($memberActivities) && is_array($memberActivities)) {
                                                foreach ($memberActivities as $act) {
                                                    if (isset($act['activity']['id'])) {
                                                        $hasValidActivities = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        @if((in_array('createReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user) && $hasValidActivities)
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0)"
                                               class="menu-link px-3"
                                               title="{{ trans('sw.quick_booking') }}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#quickBookModal{{ $member->id }}"
                                               onclick="openQuickBookModal({{ $member->id }}, {{ json_encode($memberActivities) }})">
                                                <i class="ki-outline ki-calendar-add text-success"></i>
                                                <span>{{ trans('sw.quick_booking') }}</span>
                                            </a>
                                        </div>
                                        @endif
                                    @endif

                                    @if(in_array('freezeMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                        @if((@($member->member_subscription_info->number_times_freeze) > 0) && (@$member->member_subscription_info->status == \Modules\Software\Classes\TypeConstants::Active))
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0)"
                                               class="menu-link px-3 open_freeze_modal"
                                               data-member_id="{{$member->id}}"
                                               data-member_name="{{$member->name}}"
                                               data-subscription_name="{{@$member->member_subscription_info->subscription->name}}"
                                               data-freeze_limit="{{@$member->member_subscription_info->freeze_limit}}"
                                               data-times_left="{{@$member->member_subscription_info->number_times_freeze}}"
                                               title="{{ trans('sw.freeze_account')}}">
                                                <i class="ki-outline ki-cross-circle text-info"></i>
                                                <span>{{ trans('sw.freeze_account')}}</span>
                                            </a>
                                        </div>
                                        @endif
                                    @endif

                                    @if(in_array('editMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.editMember',$member->id)}}" class="menu-link px-3" title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil text-primary"></i>
                                            <span>{{ trans('admin.edit')}}</span>
                                        </a>
                                    </div>
                                    @endif

                                    @if(in_array('deleteMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                        @if(request('trashed'))
                                        <div class="menu-item px-3">
                                            <a title="{{ trans('admin.enable')}}" href="{{route('sw.deleteMember',$member->id)}}"
                                               class="menu-link px-3 confirm_delete" title="{{ trans('admin.enable')}}">
                                                <i class="ki-outline ki-check-circle text-success"></i>
                                                <span>{{ trans('admin.enable')}}</span>
                                            </a>
                                        </div>
                                        @else
                                            @if(@$member->member_subscription_info)
                                            <div class="menu-item px-3">
                                                <a title="{{ trans('sw.disable_with_refund', ['amount' => $member->member_subscription_info->amount_paid])}}"
                                                   data-swal-text="{{ trans('sw.disable_with_refund', ['amount' => $member->member_subscription_info->amount_paid])}}"
                                                   data-swal-amount="{{@$member->member_subscription_info->amount_paid}}"
                                                   href="{{route('sw.deleteMember',$member->id).'?refund=1&total_amount='.@$member->member_subscription_info->amount_paid}}"
                                                   class="menu-link px-3 confirm_delete"
                                                   title="{{ trans('sw.disable_with_refund', ['amount' => $member->member_subscription_info->amount_paid])}}">
                                                    <i class="ki-outline ki-trash text-danger"></i>
                                                    <span>{{ trans('admin.delete')}}</span>
                                                </a>
                                            </div>
                                            @endif
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
                                    <input class="form-check-input" type="radio" name="credit_type" id="credit_type" 
                                           value="0" onclick="show_credit_add()" required>
                                    <label class="form-check-label fw-bold text-success" for="credit_type_add">
                                        <i class="ki-outline ki-plus fs-4 me-2"></i>{{ trans('sw.store_add')}}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="credit_type" id="credit_type" 
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
                            @if(!empty($payment_types) && count($payment_types))
                                @foreach($payment_types as $payment_type)
                                    <option value="{{ $payment_type->payment_id }}" 
                                            @if(@old('payment_type', $order->payment_type) == $payment_type->payment_id) selected @endif>
                                        {{ $payment_type->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="">{{ trans('sw.no_record_found') }}</option>
                            @endif
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
    
    @if($active_activity_reservation)
    <!--begin::Upcoming Reservations Modal for Each Member-->
    @foreach($members as $member)
        @php
            $memberReservations = $upcomingReservations[$member->id] ?? collect();
        @endphp
        @if($memberReservations->count() > 0)
            <div class="modal fade" id="upcomingReservationsModal{{ $member->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">{{ trans('sw.upcoming_reservations') }}</h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-outline ki-cross fs-1"></i>
                            </div>
                        </div>
                        <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                            <!--begin::Member Info-->
                            <div class="d-flex align-items-center mb-5">
                                <div class="symbol symbol-50px me-3">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-user fs-2x text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-800 fw-bold fs-5">{{ $member->name }}</div>
                                    @if($member->phone)
                                        <div class="text-muted fs-7">
                                            <i class="ki-outline ki-phone fs-6 me-1"></i> {{ $member->phone }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <!--end::Member Info-->
                            
                            <!--begin::Reservations List-->
                            <div class="separator separator-dashed my-5"></div>
                            <div class="mb-5">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="fw-bold text-gray-800 fs-6">
                                        {{ trans('sw.reservations') }} ({{ $memberReservations->count() }})
                                    </h3>
                                    <a href="{{ route('sw.listReservation') }}?member_id={{ $member->id }}" class="btn btn-sm btn-light-primary">
                                        <i class="ki-outline ki-eye fs-6"></i> {{ trans('sw.view_all') }}
                                    </a>
                                </div>
                                
                                <div class="d-flex flex-column gap-3">
                                    @foreach($memberReservations as $reservation)
                                        <div class="card card-flush border border-gray-300 border-dashed">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-4">
                                                        <!--begin::Date-->
                                                        <div class="text-center">
                                                            <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ trans('sw.date') }}</div>
                                                            <span class="badge badge-{{ $reservation->status == 'confirmed' ? 'success' : ($reservation->status == 'pending' ? 'warning' : 'primary') }} badge-lg">
                                                                {{ $reservation->reservation_date->format('Y-m-d') }}
                                                            </span>
                                                        </div>
                                                        <!--end::Date-->
                                                        
                                                        <!--begin::Time-->
                                                        <div class="text-center">
                                                            <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ trans('sw.time') }}</div>
                                                            <div class="text-gray-800 fw-bold fs-6">
                                                                <i class="ki-outline ki-time fs-5 text-primary me-1"></i>
                                                                {{ $reservation->start_time }} - {{ $reservation->end_time }}
                                                            </div>
                                                        </div>
                                                        <!--end::Time-->
                                                        
                                                        <!--begin::Activity-->
                                                        @if($reservation->activity)
                                                            <div class="text-center">
                                                                <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ trans('sw.activity') }}</div>
                                                                <span class="badge badge-light-info badge-lg">
                                                                    <i class="ki-outline ki-list fs-5 me-1"></i>
                                                                    {{ $reservation->activity->{'name_'.($lang ?? 'ar')} ?? $reservation->activity->name }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                        <!--end::Activity-->
                                                    </div>
                                                    
                                                    <!--begin::Status & Actions-->
                                                    <div class="text-end">
                                                        <div class="mb-2">
                                                            <select class="form-select form-select-sm reservation-status-select" 
                                                                    data-reservation-id="{{ $reservation->id }}"
                                                                    style="min-width: 120px;">
                                                                <option value="pending" @selected($reservation->status == 'pending')>{{ trans('sw.pending') }}</option>
                                                                <option value="confirmed" @selected($reservation->status == 'confirmed')>{{ trans('sw.confirmed') }}</option>
                                                                <option value="attended" @selected($reservation->status == 'attended')>{{ trans('sw.attended') }}</option>
                                                                <option value="cancelled" @selected($reservation->status == 'cancelled')>{{ trans('sw.cancelled') }}</option>
                                                                <option value="missed" @selected($reservation->status == 'missed')>{{ trans('sw.missed') }}</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-icon btn-light-primary reservation-edit-btn" 
                                                                    title="{{ trans('admin.edit') }}"
                                                                    data-reservation-id="{{ $reservation->id }}"
                                                                    data-member-id="{{ $member->id }}">
                                                                <i class="ki-outline ki-pencil fs-4"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <!--end::Status & Actions-->
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <!--end::Reservations List-->
                        </div>
                        <div class="modal-footer flex-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
    <!--end::Upcoming Reservations Modal-->
    
    <!--begin::Quick Book Modal for Each Member-->
    @foreach($members as $member)
        @php
            $memberActivities = @$member->member_subscription_info->activities ?? [];
            // Check if member has valid activities with activity data
            $hasValidActivities = false;
            if (!empty($memberActivities) && is_array($memberActivities)) {
                foreach ($memberActivities as $act) {
                    if (isset($act['activity']['id'])) {
                        $hasValidActivities = true;
                        break;
                    }
                }
            }
        @endphp
        @if($hasValidActivities)
            <div class="modal fade" id="quickBookModal{{ $member->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">
                                <i class="ki-outline ki-calendar-tick fs-2 me-2 text-success"></i>
                                {{ trans('sw.quick_booking') }} - {{ $member->name }}
                            </h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-outline ki-cross fs-1"></i>
                            </div>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="qb_member_id_{{ $member->id }}" value="{{ $member->id }}">
                            <input type="hidden" id="qb_reservation_id_{{ $member->id }}" value="">
                            
                            <!--begin::Help Text-->
                            <div class="alert alert-light-info d-flex align-items-center p-4 mb-5">
                                <i class="ki-outline ki-information-5 fs-2x text-info me-3"></i>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-gray-800">{{ trans('sw.quick_booking_title') }}</span>
                                    <span class="text-muted fs-7 mt-1">{{ trans('sw.select_activity_and_time') }}</span>
                                </div>
                            </div>
                            <!--end::Help Text-->
                            
                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="required form-label">
                                    <i class="ki-outline ki-gym fs-6 me-1"></i>
                                    {{ trans('sw.activity') }}
                                </label>
                                <select id="qb_activity_{{ $member->id }}" class="form-select form-select-solid qb-activity-select" data-member-id="{{ $member->id }}" data-placeholder="{{ trans('sw.select_activity') }}">
                                    <option value="">{{ trans('sw.select_activity') }}</option>
                                    @foreach($memberActivities as $activity)
                                        @php
                                            // Structure: $activity['activity']['id'], $activity['activity']['name_ar'], etc.
                                            $activityData = $activity['activity'] ?? null;
                                            $activityId = $activityData['id'] ?? null;
                                            $activityName = $activityData['name_'.($lang ?? 'ar')] ?? $activityData['name_ar'] ?? $activityData['name'] ?? '';
                                            // Duration might be in activity object or in main activity array
                                            $duration = $activityData['duration_minutes'] ?? $activity['duration_minutes'] ?? 60;
                                        @endphp
                                        @if($activityId && $activityName)
                                            <option value="{{ $activityId }}" data-duration="{{ $duration }}">{{ $activityName }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="required form-label">
                                    <i class="ki-outline ki-calendar fs-6 me-1"></i>
                                    {{ trans('sw.date') }}
                                </label>
                                <input type="date" id="qb_date_{{ $member->id }}" class="form-control form-control-solid qb-date-input" data-member-id="{{ $member->id }}" min="{{ date('Y-m-d') }}" />
                                <div class="form-text">
                                    <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                                    {{ trans('sw.select_date_for_slots') }}
                                </div>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="form-label">
                                    <i class="ki-outline ki-time fs-6 me-1"></i>
                                    {{ trans('sw.duration') }}
                                </label>
                                <select id="qb_duration_{{ $member->id }}" class="form-select form-select-solid qb-duration-select" data-member-id="{{ $member->id }}">
                                    <option value="30">30 {{ trans('sw.minutes') }}</option>
                                    <option value="45">45 {{ trans('sw.minutes') }}</option>
                                    <option value="60" selected>60 {{ trans('sw.minutes') }}</option>
                                    <option value="90">90 {{ trans('sw.minutes') }}</option>
                                    <option value="120">120 {{ trans('sw.minutes') }}</option>
                                </select>
                                <div class="form-text">
                                    <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                                    {{ trans('sw.select_duration_help') }}
                                </div>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Button-->
                            <div class="mb-5">
                                <button type="button" class="btn btn-light-primary w-100 qb-load-slots-btn" data-member-id="{{ $member->id }}">
                                    <i class="ki-outline ki-magnifier fs-2"></i>
                                    {{ trans('sw.show_available_slots') }}
                                </button>
                            </div>
                            <!--end::Button-->

                            <!--begin::Slots-->
                            <div id="qb_slots_{{ $member->id }}" class="mb-5">
                                <div class="slots-empty-state">
                                    <i class="ki-outline ki-calendar-tick"></i>
                                    <div class="empty-title">{{ trans('sw.select_activity_date_to_show_slots') }}</div>
                                    <div class="empty-subtitle">{{ trans('sw.choose_activity_and_date_first') }}</div>
                                </div>
                            </div>
                            <!--end::Slots-->

                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="form-label">
                                    <i class="ki-outline ki-note-text fs-6 me-1"></i>
                                    {{ trans('sw.notes') }}
                                </label>
                                <textarea id="qb_notes_{{ $member->id }}" class="form-control form-control-solid" rows="3" placeholder="{{ trans('sw.enter_notes_placeholder') }}"></textarea>
                                <div class="form-text">
                                    <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                                    {{ trans('sw.notes_optional_help') }}
                                </div>
                            </div>
                            <!--end::Input group-->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.cancel') }}</button>
                            <button type="button" class="btn btn-success qb-book-btn" data-member-id="{{ $member->id }}">
                                <i class="ki-outline ki-check-circle fs-2"></i>
                                <span class="qb-book-btn-text">{{ trans('sw.book_now') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
    <!--end::Quick Book Modal for Each Member-->
    @endif
@endsection

@section('scripts')
    @parent
    <script   src="{{asset('resources/assets/new_front/global/scripts/software/renew_member.js')}}"></script>

    <script src="https://momentjs.com/downloads/moment.js"></script>
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>--}}
    <script type="text/javascript" src="{{asset('resources/assets/new_front/global/plugins/pick-hours-availability-calendar/mark-your-calendar.js')}}"></script>
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

      

    </script>
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
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
                    Swal.fire({
                        title: '{{ trans('sw.error') }}',
                        text: 'Something went wrong.',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
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
                    Swal.fire({
                        title: '{{ trans('sw.error') }}',
                        text: 'Something went wrong.',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
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
                    Swal.fire({
                        title: '{{ trans('sw.error') }}',
                        text: 'Something went wrong.',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
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
                    Swal.fire({
                        title: '{{ trans('sw.error') }}',
                        text: 'Something went wrong.',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
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
                     Swal.fire({
                        title: '{{ trans('sw.error') }}',
                        text: 'Something went wrong.',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
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
             //$('#credit_type').val(1);
         }

         function show_credit_refund(){
             $('#credit_amount_add_div').hide();
             $('#credit_amount_refund_div').show();
             $('#form_credit_add_btn').hide();
             $('#form_credit_refund_btn').show();
             //$('#credit_type').val(0);
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
                            Swal.fire({
                                title: trans_done,
                                text: trans_successfully_processed,
                                icon: "success",
                                timer: 4000,
                                showConfirmButton: false
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

@if($active_activity_reservation)
<!-- Quick Booking Styles -->
<style>
/* Time Slots Styling */
.time-slots-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 0.75rem;
    padding: 1rem 0;
}

.slot-btn {
    min-width: 140px;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 0.65rem;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-align: center;
    border: 2px solid;
    background: transparent;
}

.slot-btn i {
    font-size: 1.1rem;
}

/* Available Slot */
.slot-free {
    border-color: #50cd89;
    color: #50cd89;
    background-color: rgba(80, 205, 137, 0.08);
}

.slot-free:hover {
    background-color: rgba(80, 205, 137, 0.15);
    border-color: #47b875;
    color: #47b875;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(80, 205, 137, 0.2);
}

.slot-free.active {
    background: linear-gradient(135deg, #50cd89 0%, #47b875 100%);
    color: #ffffff;
    border-color: #47b875;
    box-shadow: 0 4px 16px rgba(80, 205, 137, 0.4);
    transform: translateY(-2px);
}

.slot-free.active::before {
    content: "\2713";
    margin-left: -0.5rem;
    font-weight: bold;
}

/* Busy/Occupied Slot */
.slot-busy {
    border-color: #e4e6ef;
    color: #a1a5b7;
    background-color: #f5f8fa;
    cursor: not-allowed;
    opacity: 0.65;
    position: relative;
}

.slot-busy::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #a1a5b7;
    transform: rotate(-5deg);
}

/* Empty State */
.slots-empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: linear-gradient(135deg, #f5f8fa 0%, #ffffff 100%);
    border-radius: 0.65rem;
    border: 2px dashed #e4e6ef;
}

.slots-empty-state i {
    font-size: 4rem;
    color: #e4e6ef;
    margin-bottom: 1rem;
    display: block;
}

.slots-empty-state .empty-title {
    font-size: 1rem;
    font-weight: 600;
    color: #5e6278;
    margin-bottom: 0.5rem;
}

.slots-empty-state .empty-subtitle {
    font-size: 0.875rem;
    color: #a1a5b7;
}

/* Error State */
.slots-error-state {
    text-align: center;
    padding: 3rem 1rem;
    background: linear-gradient(135deg, #fff5f8 0%, #ffffff 100%);
    border-radius: 0.65rem;
    border: 2px solid #f1416c;
}

.slots-error-state i {
    font-size: 4rem;
    color: #f1416c;
    margin-bottom: 1rem;
    display: block;
}

.slots-error-state .error-title {
    font-size: 1rem;
    font-weight: 600;
    color: #f1416c;
    margin-bottom: 0.5rem;
}

.slots-error-state .error-subtitle {
    font-size: 0.875rem;
    color: #a1a5b7;
}

/* Loading State */
.slots-loading-state {
    text-align: center;
    padding: 3rem 1rem;
}

.slots-loading-state .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.3rem;
    color: #50cd89;
}

/* Responsive */
@media (max-width: 768px) {
    .time-slots-container {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 0.5rem;
    }
    
    .slot-btn {
        min-width: 120px;
        padding: 0.625rem 0.875rem;
        font-size: 0.85rem;
    }
}
</style>

<!-- Quick Booking JavaScript -->
<script>
// Function to open quick book modal for specific member
function openQuickBookModal(memberId, activities) {
    console.log('Opening modal for member:', memberId);
    // Wait for modal to be shown, then initialize Select2
    setTimeout(function() {
        const select = $(`#qb_activity_${memberId}`);
        if (select.length === 0) {
            console.error('Select element not found for member:', memberId);
            return;
        }
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.select2({
            placeholder: '{{ trans('sw.select_activity') }}',
            allowClear: true,
            minimumResultsForSearch: 0,
            dropdownParent: $(`#quickBookModal${memberId}`),
            language: {
                searching: function() {
                    return '{{ trans('sw.searching') }}...';
                },
                noResults: function() {
                    return '{{ trans('sw.no_results_found') }}';
                }
            }
        });
        console.log('Select2 initialized for member:', memberId);
    }, 300);
}

// Load slots for specific member modal - MUST be outside DOMContentLoaded
$(document).on('click', '.qb-load-slots-btn', function(e){
    e.preventDefault();
    e.stopPropagation();
    
    const memberId = $(this).data('member-id');
    console.log('Button clicked! Loading slots for member:', memberId);
    
    // Get values - handle Select2
    let activity_id;
    const activitySelect = $(`#qb_activity_${memberId}`);
    console.log('Activity select element:', activitySelect.length, activitySelect);
    
    if (activitySelect.length === 0) {
        console.error('Activity select not found for member:', memberId);
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: 'Activity select not found',
            confirmButtonText: 'Ok'
        });
        return false;
    }
    
    if (activitySelect.hasClass('select2-hidden-accessible')) {
        activity_id = activitySelect.select2('val');
    } else {
        activity_id = activitySelect.val();
    }
    
    const date = $(`#qb_date_${memberId}`).val();
    const duration = $(`#qb_duration_${memberId}`).val();
    
    console.log('Form values:', {activity_id, date, duration});

    if(!activity_id || !date) {
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.select_activity_date_first') }}',
            confirmButtonText: 'Ok'
        });
        return false;
    }

    const btn = $(this);
    const originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="ki-outline ki-loading fs-2"></i> {{ trans('sw.loading') }}...');
    $(`#qb_slots_${memberId}`).html(`
        <div class="slots-loading-state">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">{{ trans('sw.loading') }}...</span>
            </div>
            <div class="text-muted mt-3 fw-semibold">{{ trans('sw.loading_slots') }}...</div>
        </div>
    `);
    
    console.log('Sending request to:', "{{ route('sw.reservation.slots') }}");

    $.ajax({
        url: "{{ route('sw.reservation.slots') }}",
        type: 'POST',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: {
            activity_id: activity_id, 
            reservation_date: date, 
            duration: duration
        },
        success: function(resp) {
            console.log('Response received:', resp);
            btn.prop('disabled', false).html('<i class="ki-outline ki-magnifier fs-2"></i> {{ trans('sw.show_available_slots') }}');
            $(`#qb_slots_${memberId}`).empty();
        
            // Check if day is available
            if (resp.day_available === false) {
                $(`#qb_slots_${memberId}`).html(`
                    <div class="slots-empty-state">
                        <i class="ki-outline ki-calendar-tick"></i>
                        <div class="empty-title">{{ trans('sw.day_not_available_for_reservation') }}</div>
                        <div class="empty-subtitle">{{ trans('sw.please_select_different_date') }}</div>
                    </div>
                `);
                return;
            }

            if (resp.slots && resp.slots.length > 0) {
                const slotsContainer = $('<div class="time-slots-container"></div>');
                let availableCount = 0;
                let occupiedCount = 0;
                
                resp.slots.forEach(function(slot){
                    const slotBtn = $('<button type="button" class="slot-btn"></button>');
                    const hasLimit = resp.has_limit || false;
                    const limit = resp.reservation_limit || 0;
                    const current = slot.current_bookings || 0;
                    const remaining = slot.remaining_slots;
                    
                    // Build time text with capacity info if limit exists
                    let timeText = `<span><i class="ki-outline ki-time fs-6"></i> ${slot.start_time} - ${slot.end_time}</span>`;
                    
                    if (hasLimit && slot.available) {
                        // Show remaining slots info
                        timeText += `<small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                            ${remaining > 0 ? remaining + ' {{ trans("sw.slots_remaining") }}' : '{{ trans("sw.last_slot") }}'}
                        </small>`;
                    } else if (hasLimit && !slot.available) {
                        // Show limit reached
                        timeText += `<small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                            {{ trans("sw.limit_reached") }} (${current}/${limit})
                        </small>`;
                    }
                    
                    if(slot.available){
                        availableCount++;
                        slotBtn.addClass('slot-free qb-select-slot-member')
                               .attr('data-start', slot.start_time)
                               .attr('data-end', slot.end_time)
                               .attr('data-member-id', memberId)
                               .html(timeText);
                    } else {
                        occupiedCount++;
                        slotBtn.addClass('slot-busy')
                               .prop('disabled', true)
                               .html(timeText);
                    }
                    
                    slotsContainer.append(slotBtn);
                });
                
                // Add summary header with capacity info
                const summaryHtml = resp.has_limit 
                    ? `
                        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light-primary rounded">
                            <div class="d-flex align-items-center gap-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-circle badge-light-success"></span>
                                    <span class="text-gray-700 fw-semibold">{{ trans('sw.available') }}: ${availableCount}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-circle badge-light-secondary"></span>
                                    <span class="text-gray-700 fw-semibold">{{ trans('sw.occupied') }}: ${occupiedCount}</span>
                                </div>
                            </div>
                            <div class="text-gray-600 fw-semibold">
                                <i class="ki-outline ki-user fs-6 me-1"></i>
                                {{ trans('sw.reservation_limit') }}: ${resp.reservation_limit}
                            </div>
                        </div>
                    `
                    : `
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-circle badge-light-success"></span>
                                    <span class="text-gray-700 fw-semibold">{{ trans('sw.available') }}: ${availableCount}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-circle badge-light-secondary"></span>
                                    <span class="text-gray-700 fw-semibold">{{ trans('sw.occupied') }}: ${occupiedCount}</span>
                                </div>
                            </div>
                        </div>
                    `;
                
                const summary = $(summaryHtml);
                $(`#qb_slots_${memberId}`).append(summary).append(slotsContainer);
                
                // If editing a reservation, auto-select the matching time slot
                const currentReservationId = $(`#qb_reservation_id_${memberId}`).val();
                if (currentReservationId) {
                    // Get reservation times from modal data attributes
                    const reservationStartTime = $(`#quickBookModal${memberId}`).data('reservation-start-time');
                    const reservationEndTime = $(`#quickBookModal${memberId}`).data('reservation-end-time');
                    
                    if (reservationStartTime && reservationEndTime) {
                        // Small delay to ensure DOM is fully rendered
                        setTimeout(function() {
                            const matchingSlot = $(`.qb-select-slot-member[data-member-id="${memberId}"][data-start="${reservationStartTime}"][data-end="${reservationEndTime}"]`);
                            if (matchingSlot.length > 0) {
                                // Remove active class from all slots first
                                $(`.qb-select-slot-member[data-member-id="${memberId}"]`).removeClass('active');
                                // Add active class and click the matching slot
                                matchingSlot.first().addClass('active').click();
                            }
                        }, 100);
                    }
                }
            } else {
                $(`#qb_slots_${memberId}`).html(`
                    <div class="slots-empty-state">
                        <i class="ki-outline ki-calendar-tick"></i>
                        <div class="empty-title">{{ trans('sw.no_slots_available') }}</div>
                        <div class="empty-subtitle">{{ trans('sw.try_different_date') }}</div>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error loading slots:', {xhr, status, error});
            btn.prop('disabled', false).html('<i class="ki-outline ki-magnifier fs-2"></i> {{ trans('sw.show_available_slots') }}');
            let errorMsg = '{{ trans('sw.error_loading_slots') }}';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $(`#qb_slots_${memberId}`).html(`
                <div class="slots-error-state">
                    <i class="ki-outline ki-cross-circle"></i>
                    <div class="error-title">${errorMsg}</div>
                    <div class="error-subtitle">${error || '{{ trans('sw.please_try_again') }}'}</div>
                </div>
            `);
        }
    });
});

// Choose slot for member modal
$(document).on('click', '.qb-select-slot-member', function(){
    const memberId = $(this).data('member-id');
    $(`.qb-select-slot-member[data-member-id="${memberId}"]`).removeClass('active');
    $(this).addClass('active');
});

// Book now for specific member (create or update)
$(document).on('click', '.qb-book-btn', function(){
    const memberId = $(this).data('member-id');
    const reservationId = $(`#qb_reservation_id_${memberId}`).val();
    const activity_id = $(`#qb_activity_${memberId}`).val();
    const date = $(`#qb_date_${memberId}`).val();
    const selected = $(`.qb-select-slot-member[data-member-id="${memberId}"].active`);
    const member_id = $(`#qb_member_id_${memberId}`).val();
    
    if(!activity_id || !date) {
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.select_activity_date_first') }}',
            confirmButtonText: 'Ok'
        });
        return;
    }
    
    if(selected.length === 0) {
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.select_slot') }}',
            confirmButtonText: 'Ok'
        });
        return;
    }
    
    const start_time = selected.data('start');
    const end_time = selected.data('end');
    const notes = $(`#qb_notes_${memberId}`).val();

    const payload = {
        client_type: 'member',
        member_id: member_id,
        non_member_id: null,
        activity_id: activity_id,
        reservation_date: date,
        start_time: start_time,
        end_time: end_time,
        notes: notes
    };

    const btn = $(this);
    const btnText = btn.find('.qb-book-btn-text');
    const isUpdate = reservationId && reservationId !== '';
    const url = isUpdate 
        ? "{{ route('sw.reservation.ajaxUpdate', ':id') }}".replace(':id', reservationId)
        : "{{ route('sw.reservation.ajaxCreate') }}";
    
    btn.prop('disabled', true);
    btnText.text('{{ trans('sw.booking') }}...');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(async r => {
        if(r.status === 422){
            const j = await r.json();
            btn.prop('disabled', false);
            btnText.text(isUpdate ? '{{ trans('sw.update') }}' : '{{ trans('sw.book_now') }}');
            Swal.fire({
                icon: 'error',
                title: '{{ trans('sw.error') }}',
                text: j.message || '{{ trans('sw.slot_conflict') }}',
                confirmButtonText: 'Ok'
            });
            return;
        }
        return r.json();
    })
    .then(res => {
        if(res && res.success){
            // Close modal immediately after successful reservation
            $(`#quickBookModal${memberId}`).modal('hide');
            
            Swal.fire({
                icon: 'success',
                title: '{{ trans('admin.done') }}',
                text: isUpdate ? '{{ trans('admin.successfully_edited') }}' : '{{ trans('sw.reservation_created') }}',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        }
    })
    .catch(() => {
        btn.prop('disabled', false);
        btnText.text(isUpdate ? '{{ trans('sw.update') }}' : '{{ trans('sw.book_now') }}');
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.booking_failed') }}',
            confirmButtonText: 'Ok'
        });
    });
});

// Store initial status when page loads and when modal opens
$(document).ready(function(){
    // Store initial values for all status selects
    $('.reservation-status-select').each(function(){
        const currentVal = $(this).val();
        if (!$(this).data('old-value')) {
            $(this).data('old-value', currentVal);
        }
    });
    
    // Re-store values when modal is shown
    $('[id^="upcomingReservationsModal"]').on('shown.bs.modal', function(){
        $(this).find('.reservation-status-select').each(function(){
            const currentVal = $(this).val();
            $(this).data('old-value', currentVal);
        });
    });
});

// Change reservation status in upcoming reservations modal
$(document).on('change', '.reservation-status-select', function(e){
    e.preventDefault();
    e.stopPropagation();
    
    const reservationId = $(this).data('reservation-id');
    const select = $(this);
    const newStatus = select.val();
    const oldValue = select.data('old-value');
    
    console.log('Status changed:', { reservationId, newStatus, oldValue });
    
    // If status didn't change, do nothing
    if (newStatus === oldValue) {
        console.log('Status unchanged, ignoring');
        return;
    }
    
    // Show confirmation dialog with Yes/No buttons using SweetAlert2
    Swal.fire({
        title: '{{ trans('admin.are_you_sure') }}',
        text: '{{ trans('sw.change_status_confirmation') }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: '{{ trans('admin.yes') }}',
        cancelButtonText: '{{ trans('sw.no') }}',
        allowOutsideClick: false,
        reverseButtons: true
    }).then((result) => {
        if (!result.isConfirmed) {
            // User cancelled, revert to old value
            select.val(oldValue);
            return;
        }
        
        // Determine which action to use based on new status
        let url = '';
        
        if (newStatus === 'confirmed') {
            url = "{{ route('sw.reservation.confirm', ':id') }}".replace(':id', reservationId);
        } else if (newStatus === 'cancelled') {
            url = "{{ route('sw.reservation.cancel', ':id') }}".replace(':id', reservationId);
        } else if (newStatus === 'attended') {
            url = "{{ route('sw.reservation.attend', ':id') }}".replace(':id', reservationId);
        } else if (newStatus === 'missed') {
            url = "{{ route('sw.reservation.missed', ':id') }}".replace(':id', reservationId);
        } else if (newStatus === 'pending') {
            // Revert to old value
            select.val(oldValue);
            Swal.fire({
                title: '{{ trans('admin.info') }}',
                text: '{{ trans('sw.pending_status_not_supported') }}',
                icon: 'info',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        if (!url) {
            select.val(oldValue);
            Swal.fire({
                title: '{{ trans('sw.error') }}',
                text: '{{ trans('sw.invalid_status') }}',
                icon: 'error',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        // Disable select during request
        select.prop('disabled', true);
        
        console.log('Sending AJAX request to:', url);
        
        $.ajax({
            url: url,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(response){
                console.log('AJAX success response:', response);
                
                if(response && response.success && response.status){
                    // Update old value to new status
                    select.data('old-value', response.status);
                    
                    // Update select value to match new status
                    select.val(response.status);
                    
                    // Update badge color if badge exists
                    const card = select.closest('.card');
                    if (card.length) {
                        const badge = card.find('.badge').first();
                        if (badge.length) {
                            const colors = {
                                'confirmed': 'success',
                                'pending': 'warning',
                                'cancelled': 'danger',
                                'attended': 'primary',
                                'missed': 'secondary'
                            };
                            badge.removeClass('badge-success badge-warning badge-danger badge-primary badge-secondary badge-dark')
                                  .addClass('badge-' + (colors[response.status] || 'dark'));
                        }
                    }
                    
                    // Show success message and close modal
                    Swal.fire({
                        title: '{{ trans('admin.done') }}',
                        text: '{{ trans('sw.status_changed_successfully') }}',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Close the upcoming reservations modal
                        const modalId = select.closest('[id^="upcomingReservationsModal"]').attr('id');
                        if (modalId) {
                            $('#' + modalId).modal('hide');
                        }
                    });
                } else {
                    // Revert to old value
                    select.val(oldValue);
                    Swal.fire({
                        title: '{{ trans('sw.error') }}',
                        text: response.message || '{{ trans('sw.status_change_failed') }}',
                        icon: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr, status, error){
                console.error('AJAX error:', { xhr, status, error });
                // Revert to old value
                select.val(oldValue);
                
                let errorMsg = '{{ trans('sw.status_change_failed') }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: '{{ trans('sw.error') }}',
                    text: errorMsg,
                    icon: 'error',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            complete: function(){
                select.prop('disabled', false);
            }
        });
    });
});

// Edit reservation button - opens quick modal with reservation data
$(document).on('click', '.reservation-edit-btn', function(){
    const reservationId = $(this).data('reservation-id');
    const memberId = $(this).data('member-id');
    
    // Close upcoming reservations modal
    $(`#upcomingReservationsModal${memberId}`).modal('hide');
    
    // Fetch reservation data
    $.ajax({
        url: "{{ route('sw.reservation.ajaxGet', ':id') }}".replace(':id', reservationId),
        type: 'GET',
        success: function(res){
            if(res.success && res.data){
                const data = res.data;
                
                // Set reservation ID
                $(`#qb_reservation_id_${memberId}`).val(data.id);
                
                // Populate form fields
                $(`#qb_activity_${memberId}`).val(data.activity_id).trigger('change');
                $(`#qb_date_${memberId}`).val(data.reservation_date);
                
                // Calculate duration from start and end time
                const start = data.start_time.split(':');
                const end = data.end_time.split(':');
                const startMinutes = parseInt(start[0]) * 60 + parseInt(start[1]);
                const endMinutes = parseInt(end[0]) * 60 + parseInt(end[1]);
                const duration = endMinutes - startMinutes;
                $(`#qb_duration_${memberId}`).val(duration);
                
                $(`#qb_notes_${memberId}`).val(data.notes || '');
                
                // Update button text
                $(`#quickBookModal${memberId} .qb-book-btn-text`).text('{{ trans('sw.update') }}');
                
                // Store reservation time in modal data attributes for slot selection after loading
                $(`#quickBookModal${memberId}`).data('reservation-start-time', data.start_time);
                $(`#quickBookModal${memberId}`).data('reservation-end-time', data.end_time);
                
                // Open quick modal first
                $(`#quickBookModal${memberId}`).modal('show');
                
                // Wait for modal to be fully shown, then automatically load slots
                $(`#quickBookModal${memberId}`).one('shown.bs.modal', function() {
                    // Trigger slots loading after a short delay to ensure select2 is ready
                    setTimeout(function(){
                        // Click load slots button
                        $(`#quickBookModal${memberId} .qb-load-slots-btn`).click();
                    }, 300);
                });
            }
        },
        error: function(){
            Swal.fire({
                icon: 'error',
                title: '{{ trans('sw.error') }}',
                text: '{{ trans('sw.failed_to_load_reservation') }}',
                confirmButtonText: 'Ok'
            });
        }
    });
});

// Reset member modal when closed
@foreach($members as $member)
    @php
        $memberActivities = @$member->member_subscription_info->activities ?? [];
        $hasValidActivities = false;
        if (!empty($memberActivities) && is_array($memberActivities)) {
            foreach ($memberActivities as $act) {
                if (isset($act['activity']['id'])) {
                    $hasValidActivities = true;
                    break;
                }
            }
        }
    @endphp
    @if($hasValidActivities)
        $('#quickBookModal{{ $member->id }}').on('hidden.bs.modal', function () {
            $('#qb_reservation_id_{{ $member->id }}').val('');
            $('#qb_activity_{{ $member->id }}').val(null).trigger('change');
            $('#qb_date_{{ $member->id }}').val('');
            $('#qb_duration_{{ $member->id }}').val('60');
            $('#qb_notes_{{ $member->id }}').val('');
            $('#qb_slots_{{ $member->id }}').html(`
                <div class="slots-empty-state">
                    <i class="ki-outline ki-calendar-tick"></i>
                    <div class="empty-title">{{ trans('sw.select_activity_date_to_show_slots') }}</div>
                    <div class="empty-subtitle">{{ trans('sw.choose_activity_and_date_first') }}</div>
                </div>
            `);
            $(`.qb-select-slot-member[data-member-id="{{ $member->id }}"]`).removeClass('active');
            $(`#quickBookModal{{ $member->id }} .qb-book-btn-text`).text('{{ trans('sw.book_now') }}');
        });
    @endif
@endforeach

// Auto-update duration when activity is selected
$(document).on('change', '.qb-activity-select', function(){
    const memberId = $(this).data('member-id');
    const selectedOption = $(this).find('option:selected');
    const duration = selectedOption.data('duration');
    if (duration) {
        $(`#qb_duration_${memberId}`).val(duration);
    }
});
</script>
@endif

@endsection


