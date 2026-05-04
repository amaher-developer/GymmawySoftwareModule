@extends('software::layouts.list')
@section('list_title') {{ $title }} @endsection

@section('breadcrumb')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.gymSwInvoices.index') }}" class="text-muted text-hover-primary">{{ trans('sw.invoices') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-gray-900">{{ $invoice->invoice_number }}</li>
    </ul>
@endsection

@section('page_body')
<div class="row g-5 g-xl-10">

    <!--begin::Main column-->
    <div class="col-xl-8">

        <!--begin::Invoice card-->
        <div class="card card-flush mb-5">
            <div class="card-header align-items-center py-5 gap-2">
                <div class="card-title">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50px me-4">
                            <div class="symbol-label
                                @if($invoice->type === 'sales') bg-light-success
                                @elseif($invoice->type === 'purchase') bg-light-info
                                @else bg-light-warning @endif">
                                <i class="ki-outline ki-bill fs-2
                                    @if($invoice->type === 'sales') text-success
                                    @elseif($invoice->type === 'purchase') text-info
                                    @else text-warning @endif"></i>
                            </div>
                        </div>
                        <div>
                            <span class="fs-4 fw-bold text-gray-900 d-block">{{ $invoice->invoice_number }}</span>
                            <span class="fs-7 text-muted">
                                @if($invoice->type === 'sales')
                                    <span class="badge badge-light-success">{{ trans('sw.sales') }}</span>
                                @elseif($invoice->type === 'purchase')
                                    <span class="badge badge-light-info">{{ trans('sw.purchase') }}</span>
                                @else
                                    <span class="badge badge-light-warning">{{ trans('sw.credit_note') }}</span>
                                @endif
                                &nbsp;
                                @if($invoice->status === 'paid')
                                    <span class="badge badge-light-success">{{ trans('sw.paid') }}</span>
                                @elseif($invoice->status === 'partial')
                                    <span class="badge badge-light-warning">{{ trans('sw.partial') }}</span>
                                @elseif($invoice->status === 'cancelled')
                                    <span class="badge badge-light-danger">{{ trans('sw.cancelled') }}</span>
                                @else
                                    <span class="badge badge-light-secondary">{{ trans('sw.draft') }}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-toolbar gap-2">
                    <a href="{{ route('sw.gymSwInvoices.pdf', $invoice->id) }}"
                       class="btn btn-sm btn-flex btn-light-danger" target="_blank">
                        <i class="ki-outline ki-file-down fs-6"></i>
                        {{ trans('sw.download_pdf') }}
                    </a>
                    @if($invoice->status !== 'cancelled' && $invoice->type === 'sales')
                    <form method="POST" action="{{ route('sw.gymSwInvoices.cancel', $invoice->id) }}"
                          class="d-inline"
                          onsubmit="return confirm('{{ trans('sw.confirm_cancel') }}')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-flex btn-light-danger">
                            <i class="ki-outline ki-cross-circle fs-6"></i>
                            {{ trans('sw.cancel_invoice') }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="card-body pt-0">
                <!--begin::Details grid-->
                <div class="row g-5 mb-7">
                    <div class="col-md-6">
                        <table class="table table-borderless align-middle fs-6 gy-3">
                            <tr>
                                <td class="text-muted fw-semibold min-w-120px">{{ trans('sw.issued_at') }}</td>
                                <td class="fw-bold text-gray-800">
                                    <i class="ki-outline ki-calendar fs-6 me-1 text-muted"></i>
                                    {{ $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : '—' }}
                                </td>
                            </tr>
                            @if($invoice->due_at)
                            <tr>
                                <td class="text-muted fw-semibold">{{ trans('sw.due_at') }}</td>
                                <td class="fw-bold text-gray-800">
                                    <i class="ki-outline ki-calendar fs-6 me-1 text-muted"></i>
                                    {{ $invoice->due_at->format('Y-m-d') }}
                                </td>
                            </tr>
                            @endif
                            @if($invoice->reference_invoice_id)
                            <tr>
                                <td class="text-muted fw-semibold">{{ trans('sw.original_invoice') }}</td>
                                <td>
                                    <a href="{{ route('sw.gymSwInvoices.show', $invoice->reference_invoice_id) }}"
                                       class="fw-bold text-primary text-hover-primary">
                                        {{ optional($invoice->originalInvoice)->invoice_number ?? '#'.$invoice->reference_invoice_id }}
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @if($invoice->notes)
                            <tr>
                                <td class="text-muted fw-semibold">{{ trans('sw.notes') }}</td>
                                <td class="text-gray-700">{{ $invoice->notes }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>

                    <!--begin::Member info-->
                    @if($invoice->member)
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-4">
                            <div class="symbol symbol-40px me-3">
                                <div class="symbol-label bg-light-primary">
                                    <i class="ki-outline ki-profile-circle fs-2 text-primary"></i>
                                </div>
                            </div>
                            <span class="fs-6 fw-semibold text-gray-700">{{ trans('sw.member_info') }}</span>
                        </div>
                        <table class="table table-borderless align-middle fs-6 gy-2">
                            <tr>
                                <td class="text-muted fw-semibold min-w-120px">{{ trans('sw.member_name') }}</td>
                                <td class="fw-bold text-gray-800">{{ $invoice->member->name }}</td>
                            </tr>
                            @if($invoice->member->phone)
                            <tr>
                                <td class="text-muted fw-semibold">{{ trans('sw.phone') }}</td>
                                <td class="fw-bold text-gray-800">{{ $invoice->member->phone }}</td>
                            </tr>
                            @endif
                            @if($invoice->member->national_id)
                            <tr>
                                <td class="text-muted fw-semibold">{{ trans('sw.national_id') }}</td>
                                <td class="fw-bold text-gray-800">{{ $invoice->member->national_id }}</td>
                            </tr>
                            @endif
                            @if($invoice->member->email)
                            <tr>
                                <td class="text-muted fw-semibold">{{ trans('sw.email') }}</td>
                                <td class="text-gray-700">{{ $invoice->member->email }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    @elseif($invoice->supplier_id)
                    <div class="col-md-6">
                        <table class="table table-borderless align-middle fs-6 gy-3">
                            <tr>
                                <td class="text-muted fw-semibold min-w-120px">{{ trans('sw.supplier_id') }}</td>
                                <td class="fw-bold text-gray-800">{{ $invoice->supplier_id }}</td>
                            </tr>
                        </table>
                    </div>
                    @endif
                    <!--end::Member info-->
                </div>

                <div class="separator separator-dashed mb-5"></div>

                <!--begin::Financial summary-->
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-dashed">
                        <span class="text-muted fw-semibold fs-6">{{ trans('sw.subtotal') }}</span>
                        <span class="fw-bold text-gray-800 fs-6">{{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-dashed">
                        <span class="text-muted fw-semibold fs-6">
                            {{ trans('sw.vat') }}
                            @if($invoice->vat_rate)
                                <span class="badge badge-light-secondary ms-1">{{ $invoice->vat_rate }}%</span>
                            @endif
                        </span>
                        <span class="fw-bold text-gray-800 fs-6">{{ number_format($invoice->vat_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-dashed">
                        <span class="fw-bold text-gray-900 fs-5">{{ trans('sw.total') }}</span>
                        <span class="fw-bolder text-gray-900 fs-4">{{ number_format($invoice->total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-dashed">
                        <span class="text-muted fw-semibold fs-6">{{ trans('sw.amount_paid') }}</span>
                        <span class="fw-bold text-success fs-6">{{ number_format($invoice->amount_paid, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted fw-semibold fs-6">{{ trans('sw.amount_remaining') }}</span>
                        <span class="fw-bold fs-6 {{ $invoice->amount_remaining > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($invoice->amount_remaining, 2) }}
                        </span>
                    </div>
                </div>
                <!--end::Financial summary-->
            </div>
        </div>
        <!--end::Invoice card-->

        <!--begin::Credit notes card-->
        @if($invoice->creditNotes->isNotEmpty())
        <div class="card card-flush">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <i class="ki-outline ki-document fs-2 me-3 text-warning"></i>
                    <span class="fs-5 fw-semibold text-gray-900">{{ trans('sw.credit_notes') }}</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-gray-500 fw-bold fs-7 text-uppercase">
                                <th>{{ trans('sw.invoice_number') }}</th>
                                <th class="text-end">{{ trans('sw.total') }}</th>
                                <th>{{ trans('sw.issued_at') }}</th>
                                <th class="text-end">{{ trans('admin.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                            @foreach($invoice->creditNotes as $cn)
                            <tr>
                                <td class="fw-bold text-gray-800">{{ $cn->invoice_number }}</td>
                                <td class="text-end">{{ number_format($cn->total, 2) }}</td>
                                <td><span class="text-muted fs-7">{{ $cn->issued_at ? $cn->issued_at->format('Y-m-d') : '—' }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('sw.gymSwInvoices.show', $cn->id) }}"
                                       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                        <i class="ki-outline ki-eye fs-2"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        <!--end::Credit notes card-->

    </div>
    <!--end::Main column-->

    <!--begin::Sidebar-->
    <div class="col-xl-4">

        <!--begin::ZATCA status-->
        @if(config('sw_billing.zatca_enabled'))
        @php $zatca = $invoice->zatcaBillingInvoice; @endphp
        <div class="card card-flush mb-5">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <i class="ki-outline ki-shield-tick fs-2 me-3 text-primary"></i>
                    <span class="fs-5 fw-semibold text-gray-900">{{ trans('sw.zatca_status') }}</span>
                </div>
                @if(!$zatca && in_array($invoice->type, ['sales', 'credit_note']) && $invoice->status !== 'cancelled')
                <div class="card-toolbar">
                    <button type="button" id="btn-zatca-submit-show"
                            class="btn btn-sm btn-flex btn-primary"
                            data-url="{{ route('sw.gymSwInvoices.submitZatca', $invoice->id) }}">
                        <i class="ki-outline ki-shield-tick fs-6"></i>
                        {{ trans('sw.zatca_submit') }}
                    </button>
                </div>
                @endif
            </div>
            <div class="card-body pt-0">
                @if($zatca)
                <!--begin::Status badge-->
                <div class="mb-4">
                    @php
                        $phase2 = $zatca->zatca_phase2_status;
                        $badgeClass = match($phase2) {
                            'REPORTED', 'CLEARED' => 'badge-light-success',
                            'WARNING'             => 'badge-light-warning',
                            'ERROR'               => 'badge-light-danger',
                            default               => 'badge-light-secondary',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} fs-6 px-4 py-2">
                        {{ $phase2 ?? trans('sw.zatca_pending') }}
                    </span>
                    @if($zatca->zatca_sent_at)
                    <div class="text-muted fs-8 mt-2">
                        <i class="ki-outline ki-calendar fs-8 me-1"></i>
                        {{ $zatca->zatca_sent_at->format('Y-m-d H:i') }}
                    </div>
                    @endif
                </div>
                <!--end::Status badge-->

                <!--begin::QR code-->
                @if($zatca->zatca_qr_code)
                <div class="text-center mb-3">
                    <img src="data:image/png;base64,{{ $zatca->zatca_qr_code }}"
                         alt="ZATCA QR" style="width:160px;height:160px;"
                         class="border rounded p-2">
                    <div class="text-muted fs-8 mt-1">{{ trans('sw.zatca_qr_code') }}</div>
                </div>
                @endif
                <!--end::QR code-->

                <!--begin::Invoice details-->
                <div class="d-flex justify-content-between py-2 border-top border-dashed">
                    <span class="text-muted fw-semibold fs-7">{{ trans('sw.zatca_invoice_number') }}</span>
                    <span class="fw-bold text-gray-800 fs-7">{{ $zatca->invoice_number }}</span>
                </div>
                @if($zatca->zatca_uuid)
                <div class="d-flex justify-content-between py-2 border-top border-dashed">
                    <span class="text-muted fw-semibold fs-7">UUID</span>
                    <span class="fw-bold text-gray-700 fs-8" style="word-break:break-all;">{{ Str::limit($zatca->zatca_uuid, 20) }}</span>
                </div>
                @endif
                <!--end::Invoice details-->
                @else
                <div class="text-center py-5 text-muted fs-7">
                    <i class="ki-outline ki-shield fs-2x text-gray-400 d-block mb-2"></i>
                    {{ trans('sw.zatca_pending') }}
                </div>
                @endif
            </div>
        </div>
        @endif
        <!--end::ZATCA status-->

        <!--begin::Status history-->
        @if($invoice->statusLogs->isNotEmpty())
        <div class="card card-flush mb-5">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <i class="ki-outline ki-time fs-2 me-3 text-info"></i>
                    <span class="fs-5 fw-semibold text-gray-900">{{ trans('sw.status_history') }}</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="timeline">
                    @foreach($invoice->statusLogs->sortByDesc('created_at') as $log)
                    <div class="timeline-item mb-4">
                        <div class="timeline-line w-40px"></div>
                        <div class="timeline-icon symbol symbol-circle symbol-40px">
                            <div class="symbol-label bg-light-info">
                                <i class="ki-outline ki-abstract-26 fs-3 text-info"></i>
                            </div>
                        </div>
                        <div class="timeline-content ms-5">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                @if($log->from_status)
                                    <span class="badge badge-light-secondary fs-8">{{ $log->from_status }}</span>
                                    <i class="ki-outline ki-arrow-right fs-7 text-muted"></i>
                                @endif
                                <span class="badge badge-light-primary fs-8">{{ $log->to_status }}</span>
                            </div>
                            @if($log->created_at)
                            <span class="text-muted fs-8">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                            @endif
                            @if($log->notes)
                            <div class="text-gray-600 fs-7 mt-1">{{ $log->notes }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        <!--end::Status history-->

        <!--begin::Payments-->
        @if($invoice->moneyBoxes->isNotEmpty())
        <div class="card card-flush">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <i class="ki-outline ki-wallet fs-2 me-3 text-success"></i>
                    <span class="fs-5 fw-semibold text-gray-900">{{ trans('sw.payments') }}</span>
                </div>
            </div>
            <div class="card-body pt-0">
                @foreach($invoice->moneyBoxes as $mb)
                <div class="d-flex align-items-center justify-content-between py-3
                    {{ !$loop->last ? 'border-bottom border-dashed' : '' }}">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-35px me-3">
                            <div class="symbol-label bg-light-success">
                                <i class="ki-outline ki-dollar fs-4 text-success"></i>
                            </div>
                        </div>
                        <span class="text-muted fw-semibold fs-7">
                            {{ $mb->created_at ? $mb->created_at->format('Y-m-d') : '—' }}
                        </span>
                    </div>
                    <span class="fw-bold text-success fs-6">{{ number_format($mb->amount, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        <!--end::Payments-->

    </div>
    <!--end::Sidebar-->

</div>
@endsection

@section('scripts')
@parent
@if(config('sw_billing.zatca_enabled'))
<script>
jQuery(document).ready(function () {
    var zatcaLabelSubmit  = "{{ trans('sw.zatca_submit') }}";
    var zatcaLabelFailed  = "{{ trans('sw.zatca_submission_failed') }}";
    var csrfToken         = "{{ csrf_token() }}";

    var btnIdleHtml    = '<i class="ki-outline ki-shield-tick fs-6"></i> ' + zatcaLabelSubmit;
    var btnLoadingHtml = '<i class="ki-outline ki-loading fs-6"></i> ' + zatcaLabelSubmit;

    $('#btn-zatca-submit-show').on('click', function () {
        var $btn = $(this);
        var url  = $btn.data('url');
        $btn.prop('disabled', true).html(btnLoadingHtml);

        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: csrfToken },
            success: function (res) {
                if (res.success) {
                    toastr && toastr.success(res.message);
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    $btn.prop('disabled', false).html(btnIdleHtml);
                    toastr && toastr.error(res.message);
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html(btnIdleHtml);
                var msg = xhr.responseJSON ? xhr.responseJSON.message : zatcaLabelFailed;
                toastr && toastr.error(msg);
            }
        });
    });
});
</script>
@endif
@endsection
