@extends('software::layouts.form')

@section('breadcrumb')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listPTSessions') }}" class="text-muted text-hover-primary">{{ trans('sw.pt_sessions') }}</a>
        </li>
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
    </ul>
@endsection

@section('form_title') {{ $title }} @endsection

@section('page_body')
    <div class="row g-5 g-xl-10">
        <div class="col-xl-8">
            <div class="card card-flush mb-5">
                <div class="card-header align-items-center">
                    <div class="card-title">
                        <h2 class="fw-bold">{{ trans('sw.session_information') }}</h2>
                    </div>
                    <div class="card-toolbar">
                        <span class="badge {{ $session->status === 'completed' ? 'badge-light-success' : 'badge-light-primary' }}">
                            {{ $session->status === 'completed' ? trans('sw.session_status_completed') : trans('sw.session_status_pending') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-5">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-4">
                                <span class="symbol symbol-45px me-3">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-calendar fs-2 text-primary"></i>
                                    </span>
                                </span>
                                <div>
                                    <div class="fw-semibold text-gray-900">{{ optional($session->session_date)->format('Y-m-d') }}</div>
                                    <span class="text-muted">{{ optional($session->session_date)->format('H:i') }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-4">
                                <span class="symbol symbol-45px me-3">
                                    <span class="symbol-label bg-light-success">
                                        <i class="ki-outline ki-notebook fs-2 text-success"></i>
                                    </span>
                                </span>
                                <div>
                                    <div class="fw-semibold text-gray-900">{{ $session->class?->name ?? '-' }}</div>
                                    @if($session->class?->pt_subscription)
                                        <span class="text-muted">{{ $session->class->pt_subscription->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-4">
                                <span class="symbol symbol-45px me-3">
                                    <span class="symbol-label bg-light-info">
                                        <i class="ki-outline ki-user fs-2 text-info"></i>
                                    </span>
                                </span>
                                <div>
                                    <div class="fw-semibold text-gray-900">{{ $session->trainer?->name ?? '-' }}</div>
                                    <span class="text-muted">{{ trans('sw.pt_trainer') }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="symbol symbol-45px me-3">
                                    <span class="symbol-label bg-light-warning">
                                        <i class="ki-outline ki-people fs-2 text-warning"></i>
                                    </span>
                                </span>
                                <div>
                                    <div class="fw-semibold text-gray-900" id="total_attendees_count">
                                        {{ $summary['total_attendees'] }}
                                        @if($summary['max_members'])
                                            / {{ $summary['max_members'] }}
                                        @endif
                                    </div>
                                    <span class="text-muted">{{ trans('sw.attendees') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-flush mb-5">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold">{{ trans('sw.scan_member_title') }}</h2>
                    </div>
                </div>
                <div class="card-body">
                    <form id="session_attendance_form" class="row g-3">
                        <div class="col-lg-9">
                            <label class="form-label fw-semibold" for="attendance_code">{{ trans('sw.scan_member_label') }}</label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="attendance_code"
                                   name="code"
                                   autocomplete="off"
                                   placeholder="{{ trans('sw.scan_member_placeholder') }}">
                        </div>
                        <div class="col-lg-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-lg btn-primary w-100">
                                <i class="ki-outline ki-scan-position fs-2 me-2"></i>{{ trans('sw.record_attendance') }}
                            </button>
                        </div>
                    </form>
                    <div id="attendance_message" class="alert mt-5 d-none"></div>
                </div>
            </div>

            <div class="card card-flush">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold">{{ trans('sw.attendees_list') }}</h2>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                            <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th>{{ trans('sw.member') }}</th>
                                <th>{{ trans('sw.pt_subscription') }}</th>
                                <th class="text-center">{{ trans('sw.sessions_used') }}</th>
                                <th class="text-center">{{ trans('sw.sessions_remaining') }}</th>
                                <th>{{ trans('sw.recorded_at') }}</th>
                            </tr>
                            </thead>
                            <tbody id="session_attendees_body">
                            @forelse($attendees as $attendee)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-gray-900">{{ $attendee['member_name'] }}</div>
                                        <span class="text-muted">{{ $attendee['member_code'] }}</span>
                                    </td>
                                    <td>{{ $attendee['subscription'] ?? '-' }}</td>
                                    <td class="text-center">{{ $attendee['sessions_used'] }} / {{ $attendee['sessions_total'] }}</td>
                                    <td class="text-center">{{ $attendee['sessions_remaining'] }}</td>
                                    <td>{{ $attendee['recorded_at'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">{{ trans('sw.no_attendees_yet') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card card-flush">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold">{{ trans('sw.upcoming_sessions') }}</h2>
                    </div>
                </div>
                <div class="card-body">
                    @if($upcomingSessions->count())
                        <div class="timeline">
                            @foreach($upcomingSessions as $upcoming)
                                <div class="timeline-item">
                                    <div class="timeline-label fw-bold text-gray-800">
                                        {{ optional($upcoming['session_date'])->format('M d, H:i') }}
                                    </div>
                                    <div class="timeline-content text-muted">
                                        {{ $upcoming['trainer_name'] ?? '-' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted">{{ trans('sw.no_upcoming_sessions') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function ($) {
            'use strict';

            const attendanceRoute = "{{ route('sw.memberPTAttendees') }}";

            function showMessage(message, status) {
                const $message = $('#attendance_message');
                $message
                    .removeClass('d-none alert-success alert-danger alert-info')
                    .addClass(status ? 'alert-success' : 'alert-danger')
                    .html(message);
            }

            $('#session_attendance_form').on('submit', function (event) {
                event.preventDefault();
                const code = $('#attendance_code').val().trim();
                if (!code) {
                    showMessage("{{ trans('sw.scan_input_required') }}", false);
                    return;
                }

                $.get(attendanceRoute, {
                    code: code,
                    enquiry: 0
                }).done(function (data) {
                    showMessage(data.msg || "{{ trans('sw.attendance_recorded_successfully') }}", data.status);
                    if (data.status) {
                        setTimeout(function () {
                            window.location.reload();
                        }, 800);
                    }
                }).fail(function () {
                    showMessage("{{ trans('sw.attendance_error') }}", false);
                });
            });

            // auto focus
            $('#attendance_code').focus();
        })(jQuery);
    </script>
@endpush



