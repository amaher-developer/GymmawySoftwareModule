@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
    </ul>
@endsection

@section('page_body')
<div class="card card-flush">
    {{-- Card Header --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-abstract-26 fs-2 me-3 text-primary"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.ai_reports') }}</span>
            </div>
        </div>
        <div class="card-toolbar">
            {{-- Generate New Report --}}
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="ki-outline ki-abstract-26 fs-5 me-1"></i>
                {{ trans('sw.ai_generate_report') }}
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mx-8 mt-4 alert alert-success d-flex align-items-center p-3">
            <i class="ki-outline ki-shield-tick fs-2 text-success me-3"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mx-8 mt-4 alert alert-danger d-flex align-items-center p-3">
            <i class="ki-outline ki-information-5 fs-2 text-danger me-3"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Table --}}
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th class="min-w-50px">#</th>
                        <th class="min-w-100px">{{ trans('sw.ai_report_type') }}</th>
                        <th class="min-w-120px">{{ trans('sw.period') }}</th>
                        <th class="min-w-80px">{{ trans('sw.language') }}</th>
                        <!-- <th class="min-w-80px">{{ trans('sw.model') }}</th> -->
                        <th class="min-w-80px">{{ trans('sw.email') }}</th>
                        <th class="min-w-80px">{{ trans('sw.sms') }}</th>
                        <th class="min-w-100px">{{ trans('sw.created_at') }}</th>
                        <th class="min-w-80px text-end">{{ trans('sw.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $report)
                        <tr>
                            <td><span class="text-gray-500 fw-semibold">{{ $report->id }}</span></td>
                            <td>
                                <span class="badge badge-light-primary fw-bold">
                                    {{ ucfirst($report->type) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-gray-800 fw-semibold fs-7">
                                    {{ $report->from_date }} → {{ $report->to_date }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $report->lang === 'ar' ? 'badge-light-warning' : 'badge-light-info' }} fw-bold">
                                    {{ strtoupper($report->lang) }}
                                </span>
                            </td>
                            <!-- <td>
                                <span class="text-gray-600 fs-7">{{ $report->model_used ?? '—' }}</span>
                            </td> -->
                            <td>
                                @if($report->email_sent)
                                    <span class="badge badge-light-success">
                                        <i class="ki-outline ki-check-circle fs-6"></i> {{ trans('sw.sent') }}
                                    </span>
                                @else
                                    <span class="badge badge-light-secondary">{{ trans('sw.not_sent') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($report->sms_sent)
                                    <span class="badge badge-light-success">
                                        <i class="ki-outline ki-check-circle fs-6"></i> {{ trans('sw.sent') }}
                                    </span>
                                @else
                                    <span class="badge badge-light-secondary">{{ trans('sw.not_sent') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-gray-600 fs-7">{{ $report->created_at->format('Y-m-d H:i') }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('sw.aiReports.show', $report->id) }}"
                                   class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                   title="{{ trans('sw.view') }}">
                                    <i class="ki-outline ki-eye fs-5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-10">
                                <i class="ki-outline ki-abstract-26 fs-3x text-gray-300 mb-4 d-block"></i>
                                {{ trans('sw.no_ai_reports') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($records->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Generate Report Modal --}}
<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="generateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('sw.aiReports.generate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="generateModalLabel">
                        <i class="ki-outline ki-abstract-26 fs-2 me-2 text-primary"></i>
                        {{ trans('sw.ai_generate_report') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4 mb-6">
                        <i class="ki-outline ki-information-5 fs-2 text-primary me-3"></i>
                        <div class="fs-7 text-gray-700">
                            {{ trans('sw.ai_generate_report_hint') }}
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6 fv-row">
                            <label class="form-label fw-semibold required">{{ trans('sw.from') }}</label>
                            <input type="date" class="form-control form-control-solid" name="from" value="{{ $from }}" required>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="form-label fw-semibold required">{{ trans('sw.to') }}</label>
                            <input type="date" class="form-control form-control-solid" name="to" value="{{ $to }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('global.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="generateBtn">
                        <span class="indicator-label">
                            <i class="ki-outline ki-abstract-26 fs-5 me-1"></i>
                            {{ trans('sw.ai_generate_report') }}
                        </span>
                        <span class="indicator-progress">
                            {{ trans('sw.ai_generating') }}
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Show loading state while generating (can take 10-30s)
    document.querySelector('#generateModal form').addEventListener('submit', function () {
        const btn = document.getElementById('generateBtn');
        btn.setAttribute('data-kt-indicator', 'on');
        btn.disabled = true;
    });
</script>
@endsection
