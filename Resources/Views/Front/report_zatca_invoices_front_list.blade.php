@extends('software::layouts.list')
@section('list_title') {{ $title }} @endsection
@section('breadcrumb')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listReports') }}" class="text-muted text-hover-primary">{{ trans('sw.reports') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
    </ul>
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('/') }}resources/assets/new_front/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css"/>
@endsection

@section('page_body')

<div class="card card-flush">
    {{-- Card header --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 flex-wrap">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-receipt-square fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title }}</span>
            </div>
        </div>
        <div class="card-toolbar flex-wrap gap-2">
            {{-- Bulk generate button (hidden until rows selected) --}}
            <button type="button" id="btn_bulk_generate" class="btn btn-sm btn-success d-none">
                <i class="ki-outline ki-electricity fs-6 me-1"></i>
                <span id="bulk_generate_label">{{ trans('sw.bulk_generate_zatca') }}</span>
                <span class="badge badge-circle badge-white text-success ms-1" id="selected_count_badge">0</span>
            </button>

            <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_zatca_filter_collapse">
                <i class="ki-outline ki-filter fs-6"></i>
                {{ trans('sw.filter') }}
            </button>
        </div>
    </div>

    <div class="card-body pt-0">

        {{-- Filter --}}
        <div class="collapse {{ request()->hasAny(['from','to','search','has_zatca']) ? 'show' : '' }}" id="kt_zatca_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" method="GET" action="{{ route('sw.reportZatcaInvoices') }}">
                    <div class="row g-6">
                        <div class="col-md-4">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.search_on') }}</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ trans('sw.search_on') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.has_zatca') }}</label>
                            <select name="has_zatca" class="form-select" data-control="select2" data-placeholder="{{ trans('sw.choose') }}">
                                <option value="">{{ trans('sw.choose') }}</option>
                                <option value="1" @selected(request('has_zatca') === '1')>{{ trans('sw.has_zatca') }}</option>
                                <option value="0" @selected(request('has_zatca') === '0')>{{ trans('sw.no_zatca') }}</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range') }}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" value="{{ request('from') }}" placeholder="{{ trans('sw.from') }}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to') }}</span>
                                <input type="text" class="form-control" name="to" value="{{ request('to') }}" placeholder="{{ trans('sw.to') }}" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5 gap-2">
                        <a href="{{ route('sw.reportZatcaInvoices') }}" class="btn btn-light btn-active-light-primary fw-semibold px-6">{{ trans('admin.reset') }}</a>
                        <button type="submit" class="btn btn-primary fw-semibold px-6">
                            <i class="ki-outline ki-check fs-6"></i> {{ trans('sw.filter') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Stats row --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-5">
            <div class="d-flex align-items-center gap-6">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-circle badge-light-primary fs-7 fw-bold">{{ $total }}</span>
                    <span class="text-muted fw-semibold">{{ trans('admin.total_count') }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-circle badge-light-success fs-7 fw-bold">{{ $withZatca }}</span>
                    <span class="text-muted fw-semibold">{{ trans('sw.has_zatca') }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-circle badge-light-danger fs-7 fw-bold">{{ $withoutZatca }}</span>
                    <span class="text-muted fw-semibold">{{ trans('sw.no_zatca') }}</span>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="zatca_table">
                <thead>
                    <tr class="text-start text-muted text-uppercase gs-0">
                        <th class="w-30px pe-3">
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="chk_select_all" title="{{ trans('sw.select_all') }}">
                            </div>
                        </th>
                        <th class="min-w-60px">#</th>
                        <th class="min-w-160px">{{ trans('sw.client_name') }}</th>
                        <th class="min-w-120px">{{ trans('sw.source') }}</th>
                        <th class="min-w-120px text-end">{{ trans('sw.amount') }}</th>
                        <th class="min-w-100px text-end">{{ trans('sw.vat') }}</th>
                        <th class="min-w-140px">{{ trans('sw.date') }}</th>
                        <th class="min-w-110px">{{ trans('sw.zatca_qr') }}</th>
                        <th class="min-w-130px">{{ trans('sw.status') }}</th>
                        <th class="min-w-100px text-end">{{ trans('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800">
                    @forelse($records as $row)
                        @php
                            $invoice = $invoicesByMoneyBox->get($row->id);
                            $hasInvoice = !is_null($invoice);
                            $hasQr      = $hasInvoice && !empty($invoice->zatca_qr_code);

                            // Determine client name
                            $clientName = optional($row->member)->name
                                ?? optional(optional($row->member_subscription)->member)->name
                                ?? optional($row->non_member_subscription)->name
                                ?? optional($row->member_pt_subscription)->member?->name
                                ?? $row->member_name
                                ?? $row->client_name
                                ?? '—';

                            // Determine source label
                            if ($row->member_subscription_id) {
                                $sourceLabel = trans('sw.source_member');
                            } elseif ($row->non_member_subscription_id) {
                                $sourceLabel = trans('sw.source_non_member');
                            } elseif ($row->member_pt_subscription_id) {
                                $sourceLabel = trans('sw.source_pt_member');
                            } elseif ($row->store_order_id) {
                                $sourceLabel = trans('sw.source_store_order');
                            } else {
                                $sourceLabel = trans('sw.source_money_box');
                            }
                        @endphp
                        <tr data-mb-id="{{ $row->id }}">
                            {{-- Checkbox --}}
                            <td class="pe-3">
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input row-check" type="checkbox" value="{{ $row->id }}">
                                </div>
                            </td>

                            <td>#{{ $row->id }}</td>

                            <td class="fw-bold">{{ $clientName }}</td>

                            <td>
                                <span class="badge badge-light fw-semibold">{{ $sourceLabel }}</span>
                            </td>

                            <td class="text-end fw-bold">{{ number_format($row->amount, 2) }}</td>

                            <td class="text-end">{{ number_format($row->vat ?? 0, 2) }}</td>

                            <td>{{ optional($row->created_at)->format('Y-m-d H:i') }}</td>

                            {{-- ZATCA status --}}
                            <td>
                                @if($hasQr)
                                    <span class="badge badge-light-success fw-bold">
                                        <i class="ki-outline ki-check-circle fs-6 me-1"></i>{{ trans('sw.has_zatca') }}
                                    </span>
                                @elseif($hasInvoice)
                                    <span class="badge badge-light-warning fw-bold">
                                        <i class="ki-outline ki-information-5 fs-6 me-1"></i>{{ trans('sw.invoice_no_qr') }}
                                    </span>
                                @else
                                    <span class="badge badge-light-danger fw-bold">
                                        <i class="ki-outline ki-cross-circle fs-6 me-1"></i>{{ trans('sw.no_zatca') }}
                                    </span>
                                @endif
                            </td>

                            {{-- Invoice status --}}
                            <td>
                                @if($hasInvoice)
                                    @php
                                        $statusClass = match($invoice->zatca_status) {
                                            'generated', 'approved' => 'badge-light-success',
                                            'pending'               => 'badge-light-warning',
                                            'failed', 'error'       => 'badge-light-danger',
                                            default                 => 'badge-light-primary',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} fw-bold">
                                        {{ ucfirst(str_replace('_', ' ', $invoice->zatca_status ?? '')) }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1 flex-nowrap">
                                    {{-- View QR --}}
                                    @if($hasQr)
                                        <button type="button"
                                                class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#qrModal-{{ $row->id }}"
                                                title="{{ trans('sw.zatca_invoice_details') }}">
                                            <i class="ki-outline ki-eye fs-3"></i>
                                        </button>
                                    @endif

                                    {{-- Generate / Regenerate --}}
                                    <button type="button"
                                            class="btn btn-icon btn-sm {{ $hasQr ? 'btn-bg-light btn-active-color-warning' : 'btn-light-primary' }} btn-single-generate"
                                            data-id="{{ $row->id }}"
                                            title="{{ trans('sw.generate_zatca') }}">
                                        <i class="ki-outline ki-electricity fs-3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-10 text-muted fw-semibold">{{ trans('sw.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer">
        {{ $records->withQueryString()->links() }}
    </div>
</div>

{{-- QR Modals (outside main card to avoid z-index issues) --}}
@foreach($records as $row)
    @php $inv = $invoicesByMoneyBox->get($row->id); @endphp
    @if($inv && !empty($inv->zatca_qr_code))
        <div class="modal fade" id="qrModal-{{ $row->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $inv->invoice_number }}</h5>
                        <button type="button" class="btn btn-sm btn-icon btn-light" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        @php
                            $qrSrc = $inv->zatca_qr_code;
                            if ($qrSrc && !\Illuminate\Support\Str::startsWith($qrSrc, 'data:image')) {
                                $qrSrc = 'data:image/png;base64,' . $qrSrc;
                            }
                        @endphp
                        <img src="{{ $qrSrc }}" alt="QR" class="img-thumbnail mb-3" style="width:180px;height:180px;">
                        <div class="fw-bold fs-6">{{ $inv->invoice_number }}</div>
                        <div class="text-muted fs-7 mt-1">{{ trans('sw.total_amount') }}: {{ number_format($inv->total_amount, 2) }}</div>
                        <div class="text-muted fs-7">{{ trans('sw.vat_amount') }}: {{ number_format($inv->vat_amount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection

@section('scripts')
    <script src="{{ asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js') }}" type="text/javascript"></script>
    @parent
    <script>
    jQuery(document).ready(function ($) {

        // ── Datepicker ────────────────────────────────────────────────────────
        const today = new Date();
        $('.input-daterange').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            clearBtn: true,
            orientation: 'bottom auto',
            defaultViewDate: { year: today.getFullYear(), month: today.getMonth(), day: today.getDate() }
        });

        // ── Checkbox logic ────────────────────────────────────────────────────
        const $selectAll  = $('#chk_select_all');
        const $bulkBtn    = $('#btn_bulk_generate');
        const $countBadge = $('#selected_count_badge');

        function updateBulkBtn() {
            const count = $('.row-check:checked').length;
            $countBadge.text(count);
            count > 0 ? $bulkBtn.removeClass('d-none') : $bulkBtn.addClass('d-none');
        }

        $selectAll.on('change', function () {
            $('.row-check').prop('checked', this.checked);
            updateBulkBtn();
        });

        $(document).on('change', '.row-check', function () {
            const total   = $('.row-check').length;
            const checked = $('.row-check:checked').length;
            $selectAll.prop('indeterminate', checked > 0 && checked < total);
            $selectAll.prop('checked', checked === total);
            updateBulkBtn();
        });

        // ── AJAX generate helper (sends money-box IDs) ────────────────────────
        const generateUrl = '{{ route('sw.bulkGenerateZatca') }}';
        const csrf        = '{{ csrf_token() }}';

        function generateForMoneyBoxes(ids, $btn, originalHtml) {
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>{{ trans('sw.generating') }}');

            $.ajax({
                url: generateUrl,
                method: 'POST',
                data: { ids: ids, _token: csrf },
                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ trans('admin.done') }}',
                            text: res.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: '{{ trans('sw.error') }}', text: res.message });
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: '{{ trans('sw.error') }}', text: '{{ trans('sw.error') }}' });
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        }

        // ── Bulk generate ─────────────────────────────────────────────────────
        $bulkBtn.on('click', function () {
            const ids = $('.row-check:checked').map(function () { return this.value; }).get();
            if (!ids.length) return;

            Swal.fire({
                icon: 'question',
                title: '{{ trans('sw.bulk_generate_zatca') }}',
                text: '{{ trans('sw.select_invoices') }}: ' + ids.length,
                showCancelButton: true,
                confirmButtonText: '{{ trans('admin.yes') }}',
                cancelButtonText: '{{ trans('admin.cancel') }}'
            }).then(result => {
                if (result.isConfirmed) {
                    generateForMoneyBoxes(ids, $bulkBtn, $bulkBtn.html());
                }
            });
        });

        // ── Single row generate ───────────────────────────────────────────────
        $(document).on('click', '.btn-single-generate', function () {
            const $btn     = $(this);
            const id       = $btn.data('id');
            const origHtml = $btn.html();
            generateForMoneyBoxes([id], $btn, origHtml);
        });
    });
    </script>
@endsection
