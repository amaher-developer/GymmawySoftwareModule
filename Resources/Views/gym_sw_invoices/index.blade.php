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
    <link rel="stylesheet" type="text/css" href="{{ asset('/') }}resources/assets/new_front/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css"/>
@endsection

@section('page_body')
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-bill fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title }}</span>
            </div>
        </div>
        <div class="card-toolbar gap-2">
            @if(config('sw_billing.zatca_enabled'))
            <!--begin::Bulk action toolbar (hidden until rows are selected)-->
            <div id="kt_bulk_zatca_toolbar" class="d-none align-items-center gap-2">
                <span class="fs-7 text-gray-700 fw-semibold me-2">
                    <span id="kt_selected_count">0</span> {{ trans('sw.selected') }}
                </span>
                <button type="button" id="btn-bulk-zatca"
                        class="btn btn-sm btn-flex btn-primary">
                    <i class="ki-outline ki-shield-tick fs-6"></i>
                    {{ trans('sw.bulk_generate_zatca') }}
                </button>
            </div>
            <!--end::Bulk action toolbar-->
            @endif
            <button type="button" class="btn btn-sm btn-flex btn-light-primary"
                    data-bs-toggle="collapse" data-bs-target="#kt_invoices_filter_collapse">
                <i class="ki-outline ki-filter fs-6"></i>
                {{ trans('sw.filter') }}
            </button>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">

        <!--begin::Filter-->
        <div class="collapse @if(request()->hasAny(['type','status','member_id','date_from','date_to'])) show @endif"
             id="kt_invoices_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="{{ route('sw.gymSwInvoices.index') }}" method="get">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.type') }}</label>
                            <select name="type" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.all_types') }}</option>
                                <option value="sales"       {{ request('type') === 'sales'       ? 'selected' : '' }}>{{ trans('sw.sales') }}</option>
                                <option value="purchase"    {{ request('type') === 'purchase'    ? 'selected' : '' }}>{{ trans('sw.purchase') }}</option>
                                <option value="credit_note" {{ request('type') === 'credit_note' ? 'selected' : '' }}>{{ trans('sw.credit_note') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.status') }}</label>
                            <select name="status" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.all_statuses') }}</option>
                                <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>{{ trans('sw.draft') }}</option>
                                <option value="partial"   {{ request('status') === 'partial'   ? 'selected' : '' }}>{{ trans('sw.partial') }}</option>
                                <option value="paid"      {{ request('status') === 'paid'      ? 'selected' : '' }}>{{ trans('sw.paid') }}</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ trans('sw.cancelled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.member_id') }}</label>
                            <input type="text" name="member_id" value="{{ request('member_id') }}"
                                   class="form-control form-control-solid"
                                   placeholder="{{ trans('sw.member_id') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range') }}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control form-control-solid" name="date_from"
                                       value="{{ request('date_from') }}" placeholder="{{ trans('sw.from') }}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to') }}</span>
                                <input type="text" class="form-control form-control-solid" name="date_to"
                                       value="{{ request('date_to') }}" placeholder="{{ trans('sw.to') }}" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <a href="{{ route('sw.gymSwInvoices.index') }}"
                           class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6">
                            {{ trans('admin.reset') }}
                        </a>
                        <button type="submit" class="btn btn-primary fw-semibold px-6">
                            <i class="ki-outline ki-check fs-6"></i>
                            {{ trans('sw.filter') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!--end::Filter-->

        <!--begin::Total count-->
        <div class="d-flex align-items-center mb-5">
            <div class="symbol symbol-50px me-5">
                <div class="symbol-label bg-light-primary">
                    <i class="ki-outline ki-bill fs-2x text-primary"></i>
                </div>
            </div>
            <div class="d-flex flex-column">
                <span class="fs-6 fw-semibold text-gray-900">{{ trans('admin.total_count') }}</span>
                <span class="fs-2 fw-bold text-primary">{{ $invoices->total() }}</span>
            </div>
        </div>
        <!--end::Total count-->

        @if($invoices->count() > 0)
        <!--begin::Table-->
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_invoices_table">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        @if(config('sw_billing.zatca_enabled'))
                        <th class="w-30px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="kt_check_all">
                            </div>
                        </th>
                        @endif
                        <th class="min-w-80px">
                            <i class="ki-outline ki-hash fs-6 me-1"></i>#
                        </th>
                        <th class="min-w-150px">
                            <i class="ki-outline ki-bill fs-6 me-1"></i>
                            {{ trans('sw.invoice_number') }}
                        </th>
                        <th class="min-w-100px">
                            <i class="ki-outline ki-category fs-6 me-1"></i>
                            {{ trans('sw.type') }}
                        </th>
                        <th class="min-w-100px">
                            <i class="ki-outline ki-information-5 fs-6 me-1"></i>
                            {{ trans('sw.status') }}
                        </th>
                        <th class="min-w-100px text-end">
                            <i class="ki-outline ki-dollar fs-6 me-1"></i>
                            {{ trans('sw.total') }}
                        </th>
                        <th class="min-w-100px text-end">
                            <i class="ki-outline ki-check-circle fs-6 me-1"></i>
                            {{ trans('sw.amount_paid') }}
                        </th>
                        <th class="min-w-100px text-end">
                            <i class="ki-outline ki-time fs-6 me-1"></i>
                            {{ trans('sw.amount_remaining') }}
                        </th>
                        <th class="min-w-120px">
                            <i class="ki-outline ki-calendar fs-6 me-1"></i>
                            {{ trans('sw.issued_at') }}
                        </th>
                        <th class="min-w-100px text-end">
                            <i class="ki-outline ki-setting-2 fs-6 me-1"></i>
                            {{ trans('admin.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($invoices as $invoice)
                    @php $zatcaEligible = in_array($invoice->type, ['sales', 'credit_note']) && $invoice->status !== 'cancelled'; @endphp
                    <tr>
                        @if(config('sw_billing.zatca_enabled'))
                        <td class="pe-2">
                            @if($zatcaEligible)
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input kt-check-row" type="checkbox"
                                       value="{{ $invoice->id }}">
                            </div>
                            @endif
                        </td>
                        @endif
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label fs-6
                                        @if($invoice->type === 'sales') bg-light-success text-success
                                        @elseif($invoice->type === 'purchase') bg-light-info text-info
                                        @else bg-light-warning text-warning @endif">
                                        <i class="ki-outline ki-bill fs-3"></i>
                                    </div>
                                </div>
                                <span class="text-gray-800 fw-bold">#{{ $invoice->id }}</span>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('sw.gymSwInvoices.show', $invoice->id) }}"
                               class="text-gray-800 text-hover-primary fw-bold fs-6">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td>
                            @if($invoice->type === 'sales')
                                <span class="badge badge-light-success fs-7">{{ trans('sw.sales') }}</span>
                            @elseif($invoice->type === 'purchase')
                                <span class="badge badge-light-info fs-7">{{ trans('sw.purchase') }}</span>
                            @else
                                <span class="badge badge-light-warning fs-7">{{ trans('sw.credit_note') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($invoice->status === 'paid')
                                <span class="badge badge-light-success fs-7">{{ trans('sw.paid') }}</span>
                            @elseif($invoice->status === 'partial')
                                <span class="badge badge-light-warning fs-7">{{ trans('sw.partial') }}</span>
                            @elseif($invoice->status === 'cancelled')
                                <span class="badge badge-light-danger fs-7">{{ trans('sw.cancelled') }}</span>
                            @else
                                <span class="badge badge-light-secondary fs-7">{{ trans('sw.draft') }}</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-gray-800">
                            {{ number_format($invoice->total, 2) }}
                        </td>
                        <td class="text-end fw-bold text-success">
                            {{ number_format($invoice->amount_paid, 2) }}
                        </td>
                        <td class="text-end fw-bold {{ $invoice->amount_remaining > 0 ? 'text-danger' : 'text-gray-600' }}">
                            {{ number_format($invoice->amount_remaining, 2) }}
                        </td>
                        <td>
                            <span class="text-muted fw-semibold d-block fs-7">
                                <i class="ki-outline ki-calendar fs-7 me-1"></i>
                                {{ $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : '—' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('sw.gymSwInvoices.show', $invoice->id) }}"
                                   class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                   title="{{ trans('admin.view') }}">
                                    <i class="ki-outline ki-eye fs-2"></i>
                                </a>
                                <a href="{{ route('sw.gymSwInvoices.pdf', $invoice->id) }}"
                                   class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                                   target="_blank" title="{{ trans('sw.download_pdf') }}">
                                    <i class="ki-outline ki-file-down fs-2"></i>
                                </a>
                                @if(config('sw_billing.zatca_enabled') && $zatcaEligible)
                                <button type="button"
                                        class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm btn-zatca-submit"
                                        data-id="{{ $invoice->id }}"
                                        data-url="{{ route('sw.gymSwInvoices.submitZatca', $invoice->id) }}"
                                        title="{{ trans('sw.zatca_submit') }}">
                                    <i class="ki-outline ki-shield-tick fs-2"></i>
                                </button>
                                @endif
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
                    'from'  => $invoices->firstItem() ?? 0,
                    'to'    => $invoices->lastItem() ?? 0,
                    'total' => $invoices->total(),
                ]) }}
            </div>
            <ul class="pagination">
                {!! $invoices->appends(request()->query())->render() !!}
            </ul>
        </div>
        <!--end::Pagination-->

        @else
        <!--begin::Empty state-->
        <div class="text-center py-10">
            <div class="symbol symbol-100px mb-5">
                <div class="symbol-label fs-2x fw-semibold text-primary bg-light-primary">
                    <i class="ki-outline ki-bill fs-2x text-primary"></i>
                </div>
            </div>
            <div class="fs-1 fw-bold text-gray-900 mb-3">{{ trans('admin.no_records') }}</div>
            <div class="fs-6 text-gray-600">{{ trans('sw.no_data_found_desc') }}</div>
        </div>
        <!--end::Empty state-->
        @endif

    </div>
    <!--end::Card body-->
</div>
@endsection

@section('scripts')
    @parent
    <script src="{{ asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"
            type="text/javascript"></script>
    <script>
        jQuery(document).ready(function () {
            // ── Datepicker ──────────────────────────────────────────────────
            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true,
                orientation: 'bottom auto'
            });

            @if(config('sw_billing.zatca_enabled'))
            var csrfToken        = "{{ csrf_token() }}";
            var bulkUrl          = "{{ route('sw.gymSwInvoices.bulkSubmitZatca') }}";
            var msgFailed        = "{{ trans('sw.zatca_submission_failed') }}";
            var $toolbar         = $('#kt_bulk_zatca_toolbar');
            var $countBadge      = $('#kt_selected_count');
            var $checkAll        = $('#kt_check_all');

            // ── Selection tracking ──────────────────────────────────────────
            function updateToolbar() {
                var count = $('.kt-check-row:checked').length;
                $countBadge.text(count);
                if (count > 0) {
                    $toolbar.removeClass('d-none').addClass('d-flex');
                } else {
                    $toolbar.addClass('d-none').removeClass('d-flex');
                }
            }

            $checkAll.on('change', function () {
                $('.kt-check-row').prop('checked', this.checked);
                updateToolbar();
            });

            $(document).on('change', '.kt-check-row', function () {
                var all  = $('.kt-check-row').length;
                var chk  = $('.kt-check-row:checked').length;
                $checkAll.prop('indeterminate', chk > 0 && chk < all);
                $checkAll.prop('checked', chk === all && all > 0);
                updateToolbar();
            });

            // ── Single ZATCA submit ─────────────────────────────────────────
            $(document).on('click', '.btn-zatca-submit', function () {
                var $btn = $(this);
                var url  = $btn.data('url');
                $btn.prop('disabled', true).html('<i class="ki-outline ki-loading fs-2"></i>');

                $.ajax({
                    url: url, type: 'POST',
                    data: { _token: csrfToken },
                    success: function (res) {
                        if (res.success) {
                            $btn.html('<i class="ki-outline ki-check fs-2 text-success"></i>');
                            toastr && toastr.success(res.message);
                        } else {
                            $btn.prop('disabled', false).html('<i class="ki-outline ki-shield-tick fs-2"></i>');
                            toastr && toastr.error(res.message);
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false).html('<i class="ki-outline ki-shield-tick fs-2"></i>');
                        toastr && toastr.error(xhr.responseJSON ? xhr.responseJSON.message : msgFailed);
                    }
                });
            });

            // ── Bulk ZATCA submit ───────────────────────────────────────────
            $('#btn-bulk-zatca').on('click', function () {
                var ids = $('.kt-check-row:checked').map(function () { return this.value; }).get();
                if (!ids.length) return;

                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="ki-outline ki-loading fs-6"></i> {{ trans('sw.bulk_generate_zatca') }}');

                $.ajax({
                    url: bulkUrl, type: 'POST',
                    data: { _token: csrfToken, ids: ids },
                    success: function (res) {
                        toastr && toastr[res.success ? 'success' : 'warning'](res.message);
                        $checkAll.prop('checked', false).prop('indeterminate', false);
                        $('.kt-check-row').prop('checked', false);
                        updateToolbar();
                    },
                    error: function (xhr) {
                        toastr && toastr.error(xhr.responseJSON ? xhr.responseJSON.message : msgFailed);
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html('<i class="ki-outline ki-shield-tick fs-6"></i> {{ trans('sw.bulk_generate_zatca') }}');
                    }
                });
            });
            @endif
        });
    </script>
@endsection
