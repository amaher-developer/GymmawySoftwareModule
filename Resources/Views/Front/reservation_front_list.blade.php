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
    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
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

        @media (max-width: 992px) {
            .actions-column {
                min-width: 150px !important;
            }
        }

        #reservationsCalendar {
            margin-bottom: 20px;
        }

        .slot-btn { min-width:110px; }
        .slot-free { border:1px solid #28a745; color:#28a745; }
        .slot-busy { border:1px solid #ccc; color:#999; cursor:not-allowed; }
        
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

        .slot-free {
            border-color: #28a745;
            color: #28a745;
            background-color: rgba(40, 167, 69, 0.05);
        }

        .slot-free:hover {
            background-color: #28a745;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .slot-free.active {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        .slot-busy {
            border-color: #6c757d;
            color: #6c757d;
            background-color: rgba(108, 117, 125, 0.05);
            cursor: not-allowed;
        }

        .slots-empty-state,
        .slots-loading-state,
        .slots-error-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }

        .slots-empty-state i,
        .slots-loading-state i,
        .slots-error-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .slots-empty-state .empty-title,
        .slots-error-state .error-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .slots-empty-state .empty-subtitle,
        .slots-error-state .error-subtitle {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
@endsection
@section('page_body')

<!--begin::Reservations-->
    <div class="card card-flush">
        <!--begin::Card header-->
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <div class="d-flex align-items-center my-1">
                    <i class="ki-outline ki-calendar fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title }}</span>
                </div>
            </div>
            <div class="card-toolbar">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_reservations_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->
                
                <!--begin::Calendar Toggle-->
                <button type="button" id="toggleCalendar" class="btn btn-sm btn-flex btn-light-info">
                    <i class="ki-outline ki-calendar-tick fs-6"></i>
                    {{ trans('sw.toggle_calendar') }}
                </button>
                <!--end::Calendar Toggle-->
                
                <!--begin::Add Reservation-->
                <!-- @if((in_array('createReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user))
                    <a href="{{ route('sw.createReservation') }}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('sw.add_reservation') }}
                    </a>
                @endif -->
                <!--end::Add Reservation-->
                </div>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body pt-0">
        <!--begin::Calendar View-->
        <div id="calendarWrap" style="display:none;" class="mb-10">
            <div id="reservationsCalendar"></div>
            </div>
        <!--end::Calendar View-->

        <!--begin::Filter-->
        <div class="collapse" id="kt_reservations_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date') }}</label>
                            <input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-solid">
                    </div>
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.status') }}</label>
                            <select name="status" class="form-select form-select-solid">
                                <option value="">{{ trans('admin.choose') }}...</option>
                                <option value="confirmed" @selected(request('status')=='confirmed')>{{ trans('sw.confirmed') }}</option>
                                <option value="pending" @selected(request('status')=='pending')>{{ trans('sw.pending') }}</option>
                                <option value="cancelled" @selected(request('status')=='cancelled')>{{ trans('sw.cancelled') }}</option>
                                <option value="attended" @selected(request('status')=='attended')>{{ trans('sw.attended') }}</option>
                                <option value="missed" @selected(request('status')=='missed')>{{ trans('sw.missed') }}</option>
                            </select>
                </div>
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.activity') }}</label>
                            <select name="activity" class="form-select form-select-solid">
                                <option value="">{{ trans('admin.choose') }}...</option>
                                @foreach($activities ?? [] as $a)
                                    <option value="{{ $a->id }}" @selected(request('activity')==$a->id)>{{ $a->{'name_'.($lang ?? 'ar')} ?? $a->name }}</option>
                                @endforeach
                            </select>
                </div>
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.search') }}</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-solid" placeholder="{{ trans('sw.search') }}">
                    </div>
                </div>
                    <div class="d-flex justify-content-end mt-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-outline ki-magnifier fs-6"></i>
                            {{ trans('sw.search') }}
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
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="{{ request('search') }}" placeholder="{{ trans('sw.search_on') }} ({{ trans('sw.member') }} / {{ trans('sw.code') }})">
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
                    <i class="ki-outline ki-calendar fs-2x text-primary"></i>
                    </div>
                </div>
                <div class="d-flex flex-column">
                <span class="fs-6 fw-semibold text-gray-900">{{ trans('admin.total_count') }}</span>
                <span class="fs-2 fw-bold text-primary">{{ $total ?? ($records->count() ?? $reservations->count() ?? 0) }}</span>
                </div>
            </div>
            <!--end::Total count-->

        <!--begin::Table-->
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_reservations_table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">#</th>
                        <th class="min-w-150px text-nowrap">
                            <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.client') }}
                        </th>
                        <th class="min-w-150px text-nowrap">
                            <i class="ki-outline ki-gym fs-6 me-2"></i>{{ trans('sw.activity') }}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date') }}
                        </th>
                        <th class="min-w-150px text-nowrap">
                            <i class="ki-outline ki-time fs-6 me-2"></i>{{ trans('sw.time') }}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-information-5 fs-6 me-2"></i>{{ trans('sw.status') }}
                        </th>
                        <th class="text-end min-w-150px actions-column">
                            <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($records ?? $reservations as $r)
                    <tr data-id="{{ $r->id }}">
                        <td class="text-gray-800">{{ $r->id }}</td>
                        <td>
                            @if($r->client_type == 'member')
                                @if($r->member)
                                    @php
                                        $isDeleted = $r->member->trashed();
                                        $memberName = $r->member->name ?? 'N/A';
                                        $memberCode = $r->member->code ?? $r->member_id;
                                    @endphp
                                    <span class="badge badge-light-info">
                                        <i class="ki-outline ki-user fs-7 me-1"></i>
                                        <span class="{{ $isDeleted ? 'text-muted text-decoration-line-through' : '' }}">
                                            {{ $memberName }}
                                        </span>
                                        @if($isDeleted)
                                            <span class="badge badge-light-danger ms-1">{{ trans('sw.deleted') }}</span>
                                        @endif
                                        <span class="text-muted fs-7">({{ trans('sw.code') }}: {{ $memberCode }})</span>
                                    </span>
                                @else
                                    <span class="badge badge-light-secondary">
                                        <i class="ki-outline ki-user fs-7 me-1"></i>
                                        {{ trans('sw.member') }} #{{ $r->member_id }}
                                    </span>
                                @endif
                            @else
                                @if($r->nonMember)
                                    @php
                                        $isDeleted = $r->nonMember->trashed();
                                        $nonMemberName = $r->nonMember->name ?? 'N/A';
                                    @endphp
                                    <span class="badge badge-light-warning">
                                        <i class="ki-outline ki-user fs-7 me-1"></i>
                                        <span class="{{ $isDeleted ? 'text-muted text-decoration-line-through' : '' }}">
                                            {{ $nonMemberName }}
                                        </span>
                                        @if($isDeleted)
                                            <span class="badge badge-light-danger ms-1">{{ trans('sw.deleted') }}</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="badge badge-light-secondary">
                                        <i class="ki-outline ki-user fs-7 me-1"></i>
                                        {{ trans('sw.non_member') }} #{{ $r->non_member_id }}
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td>
                                    <div class="d-flex align-items-center">
                                <div class="text-gray-800 fw-bold">
                                    @if($r->activity)
                                        @php
                                            $activityLang = $lang ?? 'ar';
                                            $activityName = $r->activity->{'name_'.$activityLang} ?? $r->activity->name_ar ?? $r->activity->name_en ?? $r->activity->name ?? 'N/A';
                                            $isDeleted = $r->activity->trashed();
                                        @endphp
                                        <span class="{{ $isDeleted ? 'text-muted text-decoration-line-through' : '' }}">
                                            {{ $activityName }}
                                        </span>
                                        @if($isDeleted)
                                            <span class="badge badge-light-danger ms-2">{{ trans('sw.deleted') }}</span>
                                        @endif
                                    @else
                                        {{ trans('sw.activity') }} #{{ $r->activity_id }}
                                    @endif
                    </div>
                </div>
                        </td>
                        <td class="text-gray-800">{{ optional($r->reservation_date)->format('Y-m-d') ?? $r->reservation_date }}</td>
                        <td class="text-gray-800">
                            <span class="badge badge-light-primary">{{ $r->start_time }} - {{ $r->end_time }}</span>
                        </td>
                        <td class="status-cell">
                            @php
                            $colors = [
                                'confirmed' => 'success',
                                'pending' => 'warning',
                                'cancelled' => 'danger',
                                'attended' => 'primary',
                                'missed' => 'secondary'
                            ];
                            @endphp
                            <span class="badge badge-light-{{ $colors[$r->status] ?? 'dark' }}">
                                {{ trans('sw.'.$r->status) }}
                            </span>
                        </td>
                        <td class="text-end actions-cell">
                            <div class="d-flex justify-content-end gap-2">
                                @if($r->status == 'pending')
                                    @if(in_array('confirmReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user)
                                    <button class="btn btn-icon btn-bg-light btn-active-color-success btn-sm action-btn" data-action="confirm" data-id="{{ $r->id }}" data-bs-toggle="tooltip" title="{{ trans('sw.confirm') }}">
                                        <i class="ki-outline ki-check fs-3"></i>
                                    </button>
                                    @endif
                                    @if(in_array('cancelReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user)
                                    <button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm action-btn" data-action="cancel" data-id="{{ $r->id }}" data-bs-toggle="tooltip" title="{{ trans('sw.cancel') }}">
                                        <i class="ki-outline ki-cross fs-3"></i>
                                    </button>
                                    @endif
                                @elseif($r->status == 'confirmed')
                                    @if(in_array('attendReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user)
                                    <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm action-btn" data-action="attend" data-id="{{ $r->id }}" data-bs-toggle="tooltip" title="{{ trans('sw.attend') }}">
                                        <i class="ki-outline ki-check-circle fs-3"></i>
                                    </button>
                                    @endif
                                    @if(in_array('cancelReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user)
                                    <button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm action-btn" data-action="cancel" data-id="{{ $r->id }}" data-bs-toggle="tooltip" title="{{ trans('sw.cancel') }}">
                                        <i class="ki-outline ki-cross fs-3"></i>
                                    </button>
                                    @endif
                                @endif

                                @if(in_array('editReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user)
                                <button class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm edit-reservation-btn" data-reservation-id="{{ $r->id }}" data-bs-toggle="tooltip" title="{{ trans('admin.edit') }}">
                                    <i class="ki-outline ki-notepad-edit fs-3"></i>
                                </button>
                                @endif
                                
                                @if(in_array('deleteReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user)
                                <button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm action-btn" data-action="delete" data-id="{{ $r->id }}" data-bs-toggle="tooltip" title="{{ trans('sw.delete') }}">
                                    <i class="ki-outline ki-trash fs-3"></i>
                                </button>
                                @endif
                                </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ki-outline ki-information-5 fs-3x text-muted mb-3"></i>
                                <span class="text-muted fs-6">{{ trans('admin.no_data') }}</span>
                                        </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
                                    </div>
        <!--end::Table-->

        @if(isset($reservations) && method_exists($reservations, 'links'))
            <div class="d-flex flex-stack flex-wrap pt-10">
                <div class="fs-6 fw-semibold text-gray-700">
                    {{ trans('sw.showing_entries', [
                        'from' => $reservations->firstItem() ?? 0,
                        'to' => $reservations->lastItem() ?? 0,
                        'total' => $reservations->total() ?? 0,
                    ]) }}
                                </div>
                <ul class="pagination">
                    {{ $reservations->appends($search_query ?? [])->links() }}
                </ul>
                            </div>
        @elseif(isset($reservations) && count($reservations) > 0)
            <div class="d-flex flex-stack flex-wrap pt-10">
                <div class="fs-6 fw-semibold text-gray-700">
                    {{ trans('sw.showing_entries', [
                        'from' => 1,
                        'to' => count($reservations),
                        'total' => $total ?? count($reservations),
                    ]) }}
                                        </div>
                                    </div>
        @endif
        </div>
        <!--end::Card body-->
                                </div>
<!--end::Reservations-->

<!--begin::Quick Booking Modal-->
<div class="modal fade" id="quickBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">{{ trans('sw.quick_booking') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                                        </div>
                                    </div>
            <div class="modal-body">
                <input type="hidden" id="qb_reservation_id" value="">
                <input type="hidden" id="qb_client_type" value="">
                <input type="hidden" id="qb_member_id" value="">
                <input type="hidden" id="qb_non_member_id" value="">
                
                <!--begin::Help Text-->
                <div class="alert alert-light-info d-flex align-items-center p-4 mb-5">
                    <i class="ki-outline ki-information-5 fs-2x text-info me-3"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-gray-800">{{ trans('sw.quick_booking_title') }}</span>
                        <span class="text-muted fs-7 mt-1">{{ trans('sw.quick_booking_description') }}</span>
                                </div>
                            </div>
                <!--end::Help Text-->
                
                <!--begin::Input group-->
                <div class="mb-5 fv-row">
                    <label class="required form-label">
                        <i class="ki-outline ki-gym fs-6 me-1"></i>
                        {{ trans('sw.activity') }}
                    </label>
                    <select id="qb_activity" class="form-select form-select-solid qb-activity-select" data-placeholder="{{ trans('sw.select_activity') }}">
                        <option value="">{{ trans('sw.select_activity') }}</option>
                        @foreach($activities ?? [] as $a)
                            <option value="{{ $a->id }}" data-duration="{{ $a->duration_minutes ?? 60 }}">{{ $a->{'name_'.($lang ?? 'ar')} ?? $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="mb-5 fv-row">
                    <label class="required form-label">
                        <i class="ki-outline ki-calendar fs-6 me-1"></i>
                        {{ trans('sw.reservation_date') }}
                    </label>
                    <input type="date" id="qb_date" class="form-control form-control-solid" min="{{ date('Y-m-d') }}" />
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
                    <select id="qb_duration" class="form-select form-select-solid">
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
                    <button type="button" id="qb_load_slots" class="btn btn-light-primary w-100 qb-load-slots-btn">
                        <i class="ki-outline ki-magnifier fs-2"></i>
                        {{ trans('sw.show_available_slots') }}
                    </button>
                    </div>
                <!--end::Button-->

                <!--begin::Slots-->
                <div id="qb_slots" class="mb-5">
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
                    <textarea id="qb_notes" class="form-control form-control-solid" rows="3" placeholder="{{ trans('sw.enter_notes_placeholder') }}"></textarea>
                    <div class="form-text">
                        <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                        {{ trans('sw.notes_optional_help') }}
                </div>
            </div>
                <!--end::Input group-->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.cancel') }}</button>
                <button type="button" id="qb_book" class="btn btn-success qb-book-btn">
                    <i class="ki-outline ki-check-circle fs-2"></i>
                    <span class="qb-book-btn-text">{{ trans('sw.book_now') }}</span>
                </button>
            </div>
        </div>
        </div>
    </div>
<!--end::Quick Booking Modal-->
@endsection

@section('scripts')
<!-- FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Toggle calendar
    const toggleBtn = document.getElementById('toggleCalendar');
    const calendarWrap = document.getElementById('calendarWrap');
    if (toggleBtn && calendarWrap) {
        toggleBtn.addEventListener('click', function() {
            if (calendarWrap.style.display === 'none' || calendarWrap.style.display === '') {
                calendarWrap.style.display = 'block';
                // Re-render calendar if it exists
                if (typeof window.calendar !== 'undefined' && window.calendar) {
                    window.calendar.render();
                }
            } else {
                calendarWrap.style.display = 'none';
            }
        });
    }

    // Initialize calendar
    const calendarEl = document.getElementById('reservationsCalendar');
    let calendar;
    if (calendarEl) {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: { 
                left:'prev,next today', 
                center:'title', 
                right:'dayGridMonth,timeGridWeek,timeGridDay' 
            },
            events: { 
                url: "{{ route('sw.reservation.events') }}", 
                method: 'GET',
                failure: function(info) {
                    // Only show error for actual HTTP errors (not empty data)
                    // FullCalendar calls failure for network errors, 4xx, 5xx status codes
                    console.error('Failed to load calendar events:', info);
                    
                    // Check if it's a real HTTP error
                    // info can be: {status: number, statusText: string, xhr: XMLHttpRequest, error: Error}
                    let hasHttpError = false;
                    
                    if (info) {
                        // Check for HTTP status code >= 400
                        if (typeof info.status === 'number' && info.status >= 400) {
                            hasHttpError = true;
                        } else if (info.xhr && typeof info.xhr.status === 'number' && info.xhr.status >= 400) {
                            hasHttpError = true;
                        } else if (info.error && info.error.status && info.error.status >= 400) {
                            hasHttpError = true;
                        }
                        // Also check for network errors (status 0 or no status)
                        else if ((info.status === 0 || info.status === undefined) && info.statusText) {
                            // Network error or CORS issue
                            hasHttpError = true;
                        }
                    }
                    
                    // Only show error for actual HTTP/network errors
                    if (hasHttpError) {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ trans('sw.error') }}',
                            text: '{{ trans('sw.failed_to_load_calendar_events') }}',
                            confirmButtonText: 'Ok',
                            timer: 3000
                        });
                    }
                    // If no HTTP error detected, it might be empty data or other non-critical issue - don't show error
                    // FullCalendar will display empty calendar without events
                }
            },
            eventClick: function(info){
                Swal.fire({
                    title: info.event.title,
                    text: "{{ trans('sw.quick_booking') }}",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: "{{ trans('admin.edit') }}",
                    cancelButtonText: "{{ trans('sw.cancel') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Load reservation data and open edit modal
                        loadReservationForEdit(info.event.id);
                    }
                });
            },
            loading: function(isLoading) {
                if (isLoading) {
                    console.log('Loading calendar events...');
                } else {
                    console.log('Calendar events loaded successfully');
                }
            },
            locale: "{{ $lang ?? 'ar' }}",
            timeZone: 'local',
            firstDay: 6 // Saturday (for Arabic locale)
        });
        calendar.render();
        
        // Store calendar instance globally for toggle button
        window.calendar = calendar;
    }

    // Action buttons (AJAX quick actions)
    $(document).on('click', '.action-btn', function(event){
        event.preventDefault();
        const id = $(this).data('id');
        const action = $(this).data('action');
        const row = $(this).closest('tr');
        const btn = $(this);

        // Action labels and confirmations
        const actionLabels = {
            confirm: {
                title: '{{ trans('sw.confirm') }}',
                text: '{{ trans('sw.confirm_reservation_question') }}',
                type: 'question'
            },
            cancel: {
                title: '{{ trans('sw.cancel') }}',
                text: '{{ trans('sw.cancel_reservation_question') }}',
                type: 'warning'
            },
            attend: {
                title: '{{ trans('sw.attend') }}',
                text: '{{ trans('sw.mark_attendance_question') }}',
                type: 'question'
            },
            missed: {
                title: '{{ trans('sw.missed') }}',
                text: '{{ trans('sw.mark_missed_question') }}',
                type: 'warning'
            },
            delete: {
                title: '{{ trans('sw.delete') }}',
                text: '{{ trans('sw.delete_reservation_question') }}',
                type: 'warning'
            }
        };

        const actionData = actionLabels[action] || {
            title: '{{ trans('sw.confirm_action') }}',
            text: '{{ trans('sw.are_you_sure') }}',
            type: 'question'
        };

        const routes = {
            confirm: '{{ route('sw.reservation.confirm', ':id') }}',
            cancel: '{{ route('sw.reservation.cancel', ':id') }}',
            attend: '{{ route('sw.reservation.attend', ':id') }}',
            missed: '{{ route('sw.reservation.missed', ':id') }}',
            delete: '{{ route('sw.deleteReservation', ':id') }}'
        };
        const url = routes[action] ? routes[action].replace(':id', id) : `/reservation/${id}/${action}`;

        // Show confirmation dialog using SweetAlert2
        Swal.fire({
            title: actionData.title,
            text: actionData.text,
            icon: actionData.type === 'warning' ? 'warning' : (actionData.type === 'question' ? 'question' : 'info'),
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "{{ trans('admin.yes') }}",
            cancelButtonText: "{{ trans('admin.no_cancel') }}",
            allowOutsideClick: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                if (action === 'delete' && routes.delete) {
                    window.location.href = routes.delete.replace(':id', id);
                    return;
                }

                btn.prop('disabled', true);
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function(res){
                        btn.prop('disabled', false);
                        if(res && res.success){
                            const colors = {
                                confirmed:'success', 
                                pending:'warning', 
                                cancelled:'danger', 
                                attended:'primary', 
                                missed:'secondary'
                            };
                            const labels = {
                                confirmed:'{{ trans("sw.confirmed") }}', 
                                pending:'{{ trans("sw.pending") }}', 
                                cancelled:'{{ trans("sw.cancelled") }}', 
                                attended:'{{ trans("sw.attended") }}', 
                                missed:'{{ trans("sw.missed") }}'
                            };
                            row.find('.status-cell').html(
                                `<span class="badge badge-light-${colors[res.status]}">${labels[res.status] || res.status}</span>`
                            );
                            
                            // Update action buttons based on new status
                            let actionHtml = '';
                            if(res.status == 'pending'){
                                actionHtml = `
                                    <button class="btn btn-icon btn-bg-light btn-active-color-success btn-sm action-btn" data-action="confirm" data-id="${id}" data-bs-toggle="tooltip" title="{{ trans('sw.confirm') }}">
                                        <i class="ki-outline ki-check fs-3"></i>
                                    </button>
                                    <button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm action-btn" data-action="cancel" data-id="${id}" data-bs-toggle="tooltip" title="{{ trans('sw.cancel') }}">
                                        <i class="ki-outline ki-cross fs-3"></i>
                                    </button>
                                `;
                            } else if(res.status == 'confirmed'){
                                actionHtml = `
                                    <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm action-btn" data-action="attend" data-id="${id}" data-bs-toggle="tooltip" title="{{ trans('sw.attend') }}">
                                        <i class="ki-outline ki-check-circle fs-3"></i>
                                    </button>
                                    <button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm action-btn" data-action="cancel" data-id="${id}" data-bs-toggle="tooltip" title="{{ trans('sw.cancel') }}">
                                        <i class="ki-outline ki-cross fs-3"></i>
                                    </button>
                                `;
                            } else {
                                // Show styled badge for final statuses (attended, missed, cancelled)
                                const finalStatusColors = {
                                    'attended': 'primary',
                                    'missed': 'secondary',
                                    'cancelled': 'danger'
                                };
                                const statusColor = finalStatusColors[res.status] || 'dark';
                                actionHtml = `<span class="badge badge-light-${statusColor}">${labels[res.status] || res.status}</span>`;
                            }
                            actionHtml += `
                                <button class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm edit-reservation-btn" data-reservation-id="${id}" data-bs-toggle="tooltip" title="{{ trans('admin.edit') }}">
                                    <i class="ki-outline ki-notepad-edit fs-3"></i>
                                </button>
                            `;
                            row.find('.actions-cell').html(`<div class="d-flex justify-content-end gap-2">${actionHtml}</div>`);
                            
                            // Reinitialize tooltips
                            tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                            tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                                return new bootstrap.Tooltip(tooltipTriggerEl);
                            });
                            
                            Swal.fire({
                                icon: 'success',
                                title: '{{ trans('admin.completed') }}',
                                text: '{{ trans('admin.completed_successfully') }}',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ trans('sw.error') }}',
                                text: res.message || '{{ trans('admin.something_wrong') }}',
                                confirmButtonText: 'Ok'
                            });
                        }
                    },
                    error: function(xhr, status, error){
                        btn.prop('disabled', false);
                        console.error("Request: " + JSON.stringify(xhr));
                        console.error("Error: " + JSON.stringify(error));
                        
                        let errorMsg = '{{ trans('admin.something_wrong') }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            errorMsg = '{{ trans('sw.reservation_not_found') }}';
                        } else if (xhr.status === 403) {
                            errorMsg = '{{ trans('admin.permission_denied') }}';
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: '{{ trans('sw.error') }}',
                            text: errorMsg,
                            confirmButtonText: 'Ok'
                        });
                    }
                });
            }
        });
    });

    // Initialize Select2 for activity dropdown when modal is shown
    $('#quickBookModal').on('shown.bs.modal', function () {
        if (!$('#qb_activity').hasClass('select2-hidden-accessible')) {
            $('#qb_activity').select2({
                placeholder: '{{ trans('sw.select_activity') }}',
                allowClear: true,
                minimumResultsForSearch: 0,
                dropdownParent: $('#quickBookModal'),
                language: {
                    searching: function() {
                        return '{{ trans('sw.searching') }}...';
                    },
                    noResults: function() {
                        return '{{ trans('sw.no_results_found') }}';
                    }
                }
            });
        }
    });

    // Reset modal when closed
    $('#quickBookModal').on('hidden.bs.modal', function () {
        $('#qb_reservation_id').val('');
        $('#qb_client_type').val('');
        $('#qb_member_id').val('');
        $('#qb_non_member_id').val('');
        $('#qb_activity').val(null).trigger('change');
        $('#qb_date').val('');
        $('#qb_duration').val('60');
        $('#qb_notes').val('');
        $('#qb_slots').html(`
            <div class="slots-empty-state">
                <i class="ki-outline ki-calendar-tick"></i>
                <div class="empty-title">{{ trans('sw.select_activity_date_to_show_slots') }}</div>
                <div class="empty-subtitle">{{ trans('sw.choose_activity_and_date_first') }}</div>
            </div>
        `);
        $('.qb-select-slot').removeClass('active');
        $('.qb-book-btn-text').text('{{ trans('sw.book_now') }}');
    });

    // Function to load reservation data and open edit modal (used by both button click and calendar event click)
    function loadReservationForEdit(reservationId) {
        // Fetch reservation data
        $.ajax({
            url: "{{ route('sw.reservation.ajaxGet', ':id') }}".replace(':id', reservationId),
            type: 'GET',
            dataType: 'json',
            success: function(res){
                if(res && res.success && res.data){
                    const data = res.data;
                    
                    // Set reservation ID and client info
                    $('#qb_reservation_id').val(data.id);
                    $('#qb_client_type').val(data.client_type);
                    $('#qb_member_id').val(data.member_id || '');
                    $('#qb_non_member_id').val(data.non_member_id || '');
                    
                    // Populate form fields
                    $('#qb_activity').val(data.activity_id).trigger('change');
                    $('#qb_date').val(data.reservation_date);
                    
                    // Calculate duration from start and end time
                    const start = data.start_time.split(':');
                    const end = data.end_time.split(':');
                    const startMinutes = parseInt(start[0]) * 60 + parseInt(start[1]);
                    const endMinutes = parseInt(end[0]) * 60 + parseInt(end[1]);
                    const duration = endMinutes - startMinutes;
                    $('#qb_duration').val(duration);
                    
                    $('#qb_notes').val(data.notes || '');
                    
                    // Update button text
                    $('.qb-book-btn-text').text('{{ trans('sw.update') }}');
                    
                    // Store reservation time in data attributes for slot selection after loading
                    $('#quickBookModal').data('reservation-start-time', data.start_time);
                    $('#quickBookModal').data('reservation-end-time', data.end_time);
                    
                    // Open modal and load slots after modal is shown
                    $('#quickBookModal').modal('show');
                    
                    // Wait for modal to be fully shown, then automatically load slots
                    $('#quickBookModal').one('shown.bs.modal', function() {
                        // Trigger slots loading after a short delay to ensure select2 is ready
                        setTimeout(function() {
                            // Click load slots button
                            $('.qb-load-slots-btn').click();
                        }, 300);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ trans('sw.error') }}',
                        text: res.message || '{{ trans('sw.failed_to_load_reservation') }}',
                        confirmButtonText: 'Ok'
                    });
                }
            },
            error: function(xhr, status, error){
                console.error('Failed to load reservation:', {xhr, status, error});
                Swal.fire({
                    icon: 'error',
                    title: '{{ trans('sw.error') }}',
                    text: '{{ trans('sw.failed_to_load_reservation') }}',
                    confirmButtonText: 'Ok'
                });
            }
        });
    }

    // Edit reservation button - opens quick modal with reservation data
    $(document).on('click', '.edit-reservation-btn', function(){
        const reservationId = $(this).data('reservation-id');
        loadReservationForEdit(reservationId);
    });

    // Load slots
    $(document).on('click', '.qb-load-slots-btn', function(e){
        e.preventDefault();
        e.stopPropagation();
        
        const activity_id = $('#qb_activity').val();
        const date = $('#qb_date').val();
        const duration = $('#qb_duration').val();
        const reservation_id = $('#qb_reservation_id').val();
        
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
        btn.prop('disabled', true).html('<i class="ki-outline ki-loading fs-2"></i> {{ trans('sw.loading') }}...');
        $('#qb_slots').html(`
            <div class="slots-loading-state">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">{{ trans('sw.loading') }}...</span>
                </div>
                <div class="text-muted mt-3 fw-semibold">{{ trans('sw.loading_slots') }}...</div>
            </div>
        `);

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
                duration: duration,
                reservation_id: reservation_id || null
            },
            success: function(resp) {
                btn.prop('disabled', false).html('<i class="ki-outline ki-magnifier fs-2"></i> {{ trans('sw.show_available_slots') }}');
                $('#qb_slots').empty();
            
                // Check if day is available
                if (resp.day_available === false) {
                    $('#qb_slots').html(`
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
                        const slotBtn = $('<button type="button" class="slot-btn qb-select-slot"></button>');
                        const hasLimit = resp.has_limit || false;
                        const limit = resp.reservation_limit || 0;
                        const current = slot.current_bookings || 0;
                        const remaining = slot.remaining_slots;
                        
                        let timeText = `<span><i class="ki-outline ki-time fs-6"></i> ${slot.start_time} - ${slot.end_time}</span>`;
                        
                        if (hasLimit && slot.available) {
                            timeText += `<small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                                ${remaining > 0 ? remaining + ' {{ trans("sw.slots_remaining") }}' : '{{ trans("sw.last_slot") }}'}
                            </small>`;
                        } else if (hasLimit && !slot.available) {
                            timeText += `<small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                                {{ trans("sw.limit_reached") }} (${current}/${limit})
                            </small>`;
                        }
                        
                        if(slot.available){
                            availableCount++;
                            slotBtn.addClass('slot-free')
                                   .attr('data-start', slot.start_time)
                                   .attr('data-end', slot.end_time)
                                   .html(timeText);
                        } else {
                            occupiedCount++;
                            slotBtn.addClass('slot-busy')
                                   .prop('disabled', true)
                                   .html(timeText);
                        }
                        
                        slotsContainer.append(slotBtn);
                    });
                    
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
                    $('#qb_slots').append(summary).append(slotsContainer);
                    
                    // If editing a reservation, auto-select the matching time slot
                    const currentReservationId = $('#qb_reservation_id').val();
                    if (currentReservationId) {
                        // Get reservation times from modal data attributes
                        const reservationStartTime = $('#quickBookModal').data('reservation-start-time');
                        const reservationEndTime = $('#quickBookModal').data('reservation-end-time');
                        
                        if (reservationStartTime && reservationEndTime) {
                            // Small delay to ensure DOM is fully rendered
                            setTimeout(function() {
                                const matchingSlot = $(`.qb-select-slot[data-start="${reservationStartTime}"][data-end="${reservationEndTime}"]`);
                                if (matchingSlot.length > 0) {
                                    // Remove active class from all slots first
                                    $('.qb-select-slot').removeClass('active');
                                    // Add active class and click the matching slot
                                    matchingSlot.first().addClass('active').click();
                                }
                            }, 100);
                        }
                    }
                } else {
                    $('#qb_slots').html(`
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
                $('#qb_slots').html(`
                    <div class="slots-error-state">
                        <i class="ki-outline ki-cross-circle"></i>
                        <div class="error-title">${errorMsg}</div>
                        <div class="error-subtitle">${error || '{{ trans('sw.please_try_again') }}'}</div>
                    </div>
                `);
            }
        });
    });

    // Choose slot
    $(document).on('click', '.qb-select-slot', function(){
        $('.qb-select-slot').removeClass('active');
        $(this).addClass('active');
    });

    // Book now (create or update)
    $(document).on('click', '.qb-book-btn', function(){
        const reservationId = $('#qb_reservation_id').val();
        const client_type = $('#qb_client_type').val();
        const activity_id = $('#qb_activity').val();
        const date = $('#qb_date').val();
        const selected = $('.qb-select-slot.active');
        const member_id = $('#qb_member_id').val();
        const non_member_id = $('#qb_non_member_id').val();
        
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
        const notes = $('#qb_notes').val();

        const payload = {
            client_type: client_type,
            member_id: member_id || null,
            non_member_id: non_member_id || null,
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
                $('#quickBookModal').modal('hide');
                
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

    // Auto-update duration when activity is selected
    $(document).on('change', '.qb-activity-select', function(){
        const selectedOption = $(this).find('option:selected');
        const duration = selectedOption.data('duration');
        
        if (duration) {
            $('#qb_duration').val(duration);
        }
    });
});
    </script>
@endsection
