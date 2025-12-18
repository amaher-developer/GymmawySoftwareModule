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
        .table th, .table td {
            padding: 0.75rem;
            vertical-align: middle;
            font-size: 1rem;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .freeze-info-badge {
            font-size: 0.9rem;
            padding: 0.4rem 0.6rem;
        }
        .freeze-text-cell {
            max-width: 200px;
            position: relative;
        }
        .freeze-text-content {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.6;
            word-wrap: break-word;
            min-height: 3.2em;
        }
        .freeze-text-full {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .text-expandable {
            cursor: pointer;
            position: relative;
        }
        .text-expandable:hover {
            color: #009ef7;
        }
        .freeze-empty-text {
            color: #a1a5b7;
            font-style: italic;
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
                <i class="ki-outline ki-cross-circle fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_freeze_members_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->

                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportFreezeMemberPDF', 'exportFreezeMemberExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download')}}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportFreezeMemberExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportFreezeMemberExcel', $search_query)}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export')}}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportFreezeMemberPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportFreezeMemberPDF', $search_query)}}" class="menu-link px-3">
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
        <div class="collapse" id="kt_freeze_members_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" id="from_date" value="@php echo @strip_tags($_GET['from']) ? \Carbon\Carbon::parse($_GET['from'])->format('Y-m-d') : '' @endphp" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" name="to" id="to_date" value="@php echo @strip_tags($_GET['to']) ? \Carbon\Carbon::parse($_GET['to'])->format('Y-m-d') : '' @endphp" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.memberships')}}</label>
                            <select name="subscription" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.memberships')}}...</option>
                                @foreach($subscriptions as $subscription)
                                    <option value="{{$subscription->id}}" @if(request('subscription') == $subscription->id) selected="" @endif>{{$subscription->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.status')}}</label>
                            <select name="status" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.status')}}...</option>
                                <option value="active" @if(request('status') == 'active') selected="" @endif>{{ trans('sw.active')}}</option>
                                <option value="approved" @if(request('status') == 'approved') selected="" @endif>{{ trans('sw.approved')}}</option>
                                <option value="pending" @if(request('status') == 'pending') selected="" @endif>{{ trans('sw.pending')}}</option>
                                <option value="completed" @if(request('status') == 'completed') selected="" @endif>{{ trans('sw.completed')}}</option>
                                <option value="rejected" @if(request('status') == 'rejected') selected="" @endif>{{ trans('sw.rejected')}}</option>
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
            <form class="d-flex" action="{{ route('sw.reportFreezeMemberList') }}" method="get" style="max-width: 400px;">
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="@php echo @strip_tags($_GET['search']) @endphp" placeholder="{{ trans('sw.search_on')}}">
                @if(request('subscription'))
                    <input type="hidden" name="subscription" value="{{ request('subscription') }}">
                @endif
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('from'))
                    <input type="hidden" name="from" value="{{ request('from') }}">
                @endif
                @if(request('to'))
                    <input type="hidden" name="to" value="{{ request('to') }}">
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

        @if(count($members) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_freeze_members_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-barcode fs-6 me-2"></i>{{ trans('sw.identification_code')}}
                            </th>
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
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.start_freeze_date')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.end_freeze_date')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-status fs-6 me-2"></i>{{ trans('sw.freeze_status') ?? 'Freeze Status'}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-time fs-6 me-2"></i>{{ trans('sw.freeze_reminder_days')}}
                            </th>
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-text fs-6 me-2"></i>{{ trans('sw.reason')}}
                            </th>
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-notes fs-6 me-2"></i>{{ trans('sw.admin_notes')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.duration')}}
                            </th>
                            <th class="text-end min-w-100px">
                                <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($members as $member)
                            @php
                                // Get the most recent freeze record based on filters
                                $all_freezes = @$member->member_subscription_info->freezes;
                                
                                // If status filter is applied, filter by status
                                if(request('status')) {
                                    $all_freezes = $all_freezes->where('status', request('status'));
                                }
                                
                                // Get the most recent freeze (or the one matching current date range if no filter)
                                $freeze_record = $all_freezes->first();
                                
                                // If date range filter is applied, check if freeze overlaps
                                if(request('from') && request('to') && $freeze_record) {
                                    $fromDate = \Carbon\Carbon::parse(request('from'))->startOfDay();
                                    $toDate = \Carbon\Carbon::parse(request('to'))->startOfDay();
                                    $freezeStart = \Carbon\Carbon::parse($freeze_record->start_date)->startOfDay();
                                    $freezeEnd = \Carbon\Carbon::parse($freeze_record->end_date)->startOfDay();
                                    
                                    // Check if freeze overlaps with date range
                                    $overlaps = ($freezeStart->lte($toDate) && $freezeEnd->gte($fromDate));
                                    if(!$overlaps) {
                                        // Check if there's another freeze that overlaps
                                        $freeze_record = $all_freezes->filter(function($freeze) use ($fromDate, $toDate) {
                                            $fs = \Carbon\Carbon::parse($freeze->start_date)->startOfDay();
                                            $fe = \Carbon\Carbon::parse($freeze->end_date)->startOfDay();
                                            return ($fs->lte($toDate) && $fe->gte($fromDate));
                                        })->first();
                                    }
                                }
                                
                                // If no freeze record found, skip this member
                                if(!$freeze_record) {
                                    continue;
                                }
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $member->code }}</span>
                                </td>
                                <td>
                                    <div class="symbol symbol-50px">
                                        <img alt="avatar" class="rounded-circle" src="{{$member->image}}">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-gray-800">{{ $member->name }}</span>
                                        @if($member->national_id)
                                            <span class="text-muted fs-7">{{ trans('sw.national_id') ?? 'National ID'}}: {{$member->national_id}}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $member->phone }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @$member->member_subscription_info->subscription->name }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ \Carbon\Carbon::parse($freeze_record->start_date)->toDateString() }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ \Carbon\Carbon::parse($freeze_record->end_date)->toDateString() }}</span>
                                </td>
                                <td>
                                    @if($freeze_record->status == 'active')
                                        <span class="badge badge-light-success freeze-info-badge">{{ trans('sw.active') }}</span>
                                    @elseif($freeze_record->status == 'approved')
                                        <span class="badge badge-light-info freeze-info-badge">{{ trans('sw.approved') }}</span>
                                    @elseif($freeze_record->status == 'completed')
                                        <span class="badge badge-light-primary freeze-info-badge">{{ trans('sw.completed') }}</span>
                                    @elseif($freeze_record->status == 'pending')
                                        <span class="badge badge-light-warning freeze-info-badge">{{ trans('sw.pending') }}</span>
                                    @elseif($freeze_record->status == 'rejected')
                                        <span class="badge badge-light-danger freeze-info-badge">{{ trans('sw.rejected') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $end_date = \Carbon\Carbon::parse($freeze_record->end_date)->startOfDay();
                                        $now = \Carbon\Carbon::now()->startOfDay();
                                        $days_remaining = $end_date->isPast() ? 0 : max(0, (int) $now->diffInDays($end_date, false));
                                    @endphp
                                    <span class="fw-bold @if($days_remaining <= 7) text-danger @elseif($days_remaining <= 30) text-warning @else text-success @endif">
                                        {{ $days_remaining }} {{ trans('sw.days') }}
                                    </span>
                                </td>
                                <td class="freeze-text-cell">
                                    @if($freeze_record->reason && trim($freeze_record->reason) != '')
                                        <div class="d-flex flex-column">
                                            <div class="freeze-text-content text-gray-700 mb-1" 
                                                 data-bs-toggle="tooltip" 
                                                 data-bs-placement="top" 
                                                 data-bs-html="true"
                                                 title="{{ htmlspecialchars($freeze_record->reason, ENT_QUOTES, 'UTF-8') }}">
                                                {{ $freeze_record->reason }}
                                            </div>
                                            @if(strlen($freeze_record->reason) > 80)
                                                <button type="button" 
                                                        class="btn btn-sm btn-link p-0 mt-0 text-primary text-expandable align-self-start"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#freeze_reason_modal_{{ $member->id }}_{{ $freeze_record->id }}">
                                                    <i class="ki-outline ki-eye fs-7 me-1"></i>
                                                    {{ trans('sw.view_full') }}
                                                </button>
                                            @endif
                                        </div>
                                        
                                        @if(strlen($freeze_record->reason) > 80)
                                            <!-- Reason Modal -->
                                            <div class="modal fade" id="freeze_reason_modal_{{ $member->id }}_{{ $freeze_record->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title d-flex align-items-center">
                                                                <i class="ki-outline ki-text fs-2 me-2 text-primary"></i>
                                                                <span>{{ trans('sw.reason') }} - {{ $member->name }}</span>
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="freeze-text-full p-4 bg-light-primary rounded border border-primary border-dashed">
                                                                <p class="mb-0 fs-6 text-gray-700">{{ $freeze_record->reason }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">
                                                                <i class="ki-outline ki-cross fs-5 me-1"></i>
                                                                {{ trans('sw.close') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <span class="freeze-empty-text d-flex align-items-center">
                                            <i class="ki-outline ki-minus fs-7 me-1"></i>
                                            <span>{{ trans('sw.not_specified') }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="freeze-text-cell">
                                    @if($freeze_record->admin_note && trim($freeze_record->admin_note) != '')
                                        <div class="d-flex flex-column">
                                            <div class="freeze-text-content text-gray-700 mb-1" 
                                                 data-bs-toggle="tooltip" 
                                                 data-bs-placement="top" 
                                                 data-bs-html="true"
                                                 title="{{ htmlspecialchars($freeze_record->admin_note, ENT_QUOTES, 'UTF-8') }}">
                                                {{ $freeze_record->admin_note }}
                                            </div>
                                            @if(strlen($freeze_record->admin_note) > 80)
                                                <button type="button" 
                                                        class="btn btn-sm btn-link p-0 mt-0 text-info text-expandable align-self-start"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#freeze_admin_note_modal_{{ $member->id }}_{{ $freeze_record->id }}">
                                                    <i class="ki-outline ki-eye fs-7 me-1"></i>
                                                    {{ trans('sw.view_full') }}
                                                </button>
                                            @endif
                                        </div>
                                        
                                        @if(strlen($freeze_record->admin_note) > 80)
                                            <!-- Admin Note Modal -->
                                            <div class="modal fade" id="freeze_admin_note_modal_{{ $member->id }}_{{ $freeze_record->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title d-flex align-items-center">
                                                                <i class="ki-outline ki-notes fs-2 me-2 text-info"></i>
                                                                <span>{{ trans('sw.admin_notes') }} - {{ $member->name }}</span>
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="freeze-text-full p-4 bg-light-info rounded border border-info border-dashed">
                                                                <p class="mb-0 fs-6 text-gray-700">{{ $freeze_record->admin_note }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">
                                                                <i class="ki-outline ki-cross fs-5 me-1"></i>
                                                                {{ trans('sw.close') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <span class="freeze-empty-text d-flex align-items-center">
                                            <i class="ki-outline ki-minus fs-7 me-1"></i>
                                            <span>{{ trans('sw.not_specified') }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold">
                                        {{ (int) \Carbon\Carbon::parse($freeze_record->start_date)->diffInDays(\Carbon\Carbon::parse($freeze_record->end_date)) }} {{ trans('sw.days') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <!--begin::View Profile-->
                                        <!-- <a href="{{route('sw.showMemberProfile', $member->id)}}" target="_blank" 
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                           title="{{ trans('sw.member_profile')}}">
                                            <i class="ki-outline ki-eye fs-2"></i>
                                        </a> -->
                                        <!--end::View Profile-->
                                        
                                        <!--begin::WhatsApp-->
                                        <a href="https://web.whatsapp.com/send?phone={{ ((substr( $member->phone, 0, 1 ) === "+") || (substr( $member->phone, 0, 2 ) === "00")) ? $member->phone : '+'.env('APP_COUNTRY_CODE').$member->phone}}"
                                           target="_blank" 
                                           class="btn btn-icon btn-bg-light btn-active-color-success btn-sm"
                                           title="{{ trans('sw.whatsapp')}}">
                                            <i class="ki-outline ki-message-text-2 fs-2"></i>
                                        </a>
                                        <!--end::WhatsApp-->
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
                        <i class="ki-outline ki-cross-circle fs-2"></i>
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
@endsection

@section('scripts')
    @parent
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}" type="text/javascript"></script>
    <script>
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
            
            // Initialize Bootstrap tooltips for freeze text
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    delay: { "show": 300, "hide": 100 },
                    html: true
                });
            });
        });
    </script>
@endsection



