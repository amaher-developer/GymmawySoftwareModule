@extends('software::layouts.list')
@section('list_title') {{ $title }} @endsection

@section('breadcrumb')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
    </ul>
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('resources/assets/admin/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}"/>
    <style>
        .session-status-badge {
            text-transform: capitalize;
        }

        .filters .form-control,
        .filters .form-select {
            min-height: 38px;
        }
    </style>
@endsection

@section('page_body')
    @php
        $filterApplied = $filtersApplied ?? (
            request()->filled('class_id') ||
            request()->filled('trainer_id') ||
            request()->filled('status') ||
            request()->filled('from') ||
            request()->filled('to') ||
            request()->filled('date_from') ||
            request()->filled('date_to')
        );
        $fromValue = request('from', request('date_from', $filters['from'] ?? ''));
        $toValue = request('to', request('date_to', $filters['to'] ?? ''));
    @endphp
    <div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <div class="d-flex align-items-center my-1">
                    <i class="ki-outline ki-calendar fs-2 me-3"></i>
                    <span class="fs-4 fw-semibold text-gray-900">{{ $title }}</span>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
                    <button type="button"
                            class="btn btn-sm btn-flex btn-light-primary"
                            data-bs-toggle="collapse"
                            data-bs-target="#kt_pt_sessions_filter">
                        <i class="ki-outline ki-filter fs-6"></i>
                        {{ trans('sw.filter') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="collapse {{ $filterApplied ? 'show' : '' }}" id="kt_pt_sessions_filter">
                <form method="get" class="filters row g-3 mb-6">
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label fw-semibold">{{ trans('sw.pt_class') }}</label>
                        <select name="class_id" class="form-select select2" data-placeholder="{{ trans('admin.choose')}}...">
                            <option value="">{{ trans('admin.choose')}}...</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>
                                    {{ $class->name }} @if($class->pt_subscription) ({{ $class->pt_subscription->name }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label fw-semibold">{{ trans('sw.pt_trainer') }}</label>
                        <select name="trainer_id" class="form-select select2" data-placeholder="{{ trans('admin.choose')}}...">
                            <option value="">{{ trans('admin.choose')}}...</option>
                            @foreach($trainers as $trainer)
                                <option value="{{ $trainer->id }}" @selected(request('trainer_id') == $trainer->id)>
                                    {{ $trainer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <label class="form-label fw-semibold">{{ trans('sw.status') }}</label>
                        <select name="status" class="form-select select2" data-placeholder="{{ trans('admin.choose')}}...">
                            <option value="">{{ trans('admin.choose')}}...</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="{{ app()->getLocale() === 'ar' ? 'col-xl-2 col-md-6' : 'col-xl-2 col-md-6' }}">
                        <label class="form-label fw-semibold">{{ trans('sw.date_from') }}</label>
                        <input type="text"
                               class="form-control datepicker"
                               name="date_from"
                               value="{{ $fromValue }}"
                               autocomplete="off"
                               placeholder="YYYY-MM-DD">
                    </div>
                    <div class="{{ app()->getLocale() === 'ar' ? 'col-xl-2 col-md-6' : 'col-xl-2 col-md-6' }}">
                        <label class="form-label fw-semibold">{{ trans('sw.date_to') }}</label>
                        <input type="text"
                               class="form-control datepicker"
                               name="date_to"
                               value="{{ $toValue }}"
                               autocomplete="off"
                               placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ki-outline ki-magnifier fs-3 me-1"></i>{{ trans('sw.filter_results') }}
                        </button>
                        <a href="{{ route('sw.listPTSessions') }}" class="btn btn-light">
                            <i class="ki-outline ki-arrows-circle fs-3 me-1"></i>{{ trans('admin.reset') }}
                        </a>
                    </div>
                </form>
            </div>

            @if($sessions->count())
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-200px">{{ trans('sw.session_date') }}</th>
                            <th class="min-w-200px">{{ trans('sw.pt_class') }}</th>
                            <th class="min-w-150px">{{ trans('sw.pt_trainer') }}</th>
                            <th class="min-w-100px text-center">{{ trans('sw.attendees') }}</th>
                            <th class="min-w-120px text-center">{{ trans('sw.status') }}</th>
                            <th class="text-end min-w-120px">{{ trans('sw.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($sessions as $session)
                            @php
                                $class = $session->class;
                                $assignedTrainer = $session->trainer;
                                $maxMembers = $session->max_members ?? $class?->max_members;
                                $attendeesCount = $session->attendee_count ?? 0;
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-gray-900">{{ optional($session->session_date)->format('Y-m-d') }}</div>
                                    <span class="text-muted">{{ optional($session->session_date)->format('H:i') }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-gray-900">{{ $class?->name ?? '-' }}</div>
                                    @if($class?->pt_subscription)
                                        <span class="text-muted fs-7">{{ $class->pt_subscription->name }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold text-gray-900">{{ $assignedTrainer?->name ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-gray-900">
                                        {{ $attendeesCount }}
                                        @if($maxMembers)
                                            / {{ $maxMembers }}
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $status = $session->status;
                                        $badgeClass = [
                                            'pending' => 'badge-light-primary',
                                            'completed' => 'badge-light-success',
                                        ][$status] ?? 'badge-light';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} session-status-badge">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('sw.showPTSession', $session->id) }}" class="btn btn-sm btn-light-primary">
                                        <i class="ki-outline ki-eye fs-3"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-6">
                    <div class="text-muted">
                        {{ trans('sw.showing_results', ['from' => $sessions->firstItem(), 'to' => $sessions->lastItem(), 'total' => $sessions->total()]) }}
                    </div>
                    {{ $sessions->links() }}
                </div>
            @else
                <div class="alert alert-info d-flex align-items-center">
                    <i class="ki-outline ki-information-5 fs-2 me-3"></i>
                    <span>{{ trans('sw.no_sessions_found') }}</span>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js') }}" type="text/javascript"></script>
    <script>
        (function ($) {
            'use strict';

            $('.select2').select2({
                width: '100%',
                allowClear: true,
                placeholder: "{{ trans('admin.choose')}}..."
            });

            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                orientation: 'bottom auto'
            });
        })(jQuery);
    </script>
@endpush



