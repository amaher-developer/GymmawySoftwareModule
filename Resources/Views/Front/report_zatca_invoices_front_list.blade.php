@extends('software::layouts.list')
@section('list_title') {{ $title }} @endsection
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
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listReports') }}" class="text-muted text-hover-primary">{{ trans('sw.reports') }}</a>
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
    <link rel="stylesheet" type="text/css" href="{{ asset('/') }}resources/assets/new_front/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css"/>
@endsection

@section('page_body')

<!--begin::Report-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-receipt-square fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title }}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_zatca_invoices_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
            </div>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Filter-->
        <div class="collapse" id="kt_zatca_invoices_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" method="GET" action="{{ route('sw.reportZatcaInvoices') }}">
                    <div class="row g-6">
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.invoice_number') }}</label>
                            <input type="text" name="number" class="form-control" value="{{ request('number') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.buyer_name') }}</label>
                            <input type="text" name="buyer" class="form-control" value="{{ request('buyer') }}" placeholder="{{ trans('sw.buyer_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.status') }}</label>
                            <select name="status" class="form-select" data-control="select2" data-placeholder="{{ trans('sw.choose') }}">
                                <option value="">{{ trans('sw.choose') }}</option>
                                @foreach($statuses as $option)
                                    <option value="{{ $option }}" @selected(request('status') === $option)>{{ ucfirst(str_replace('_', ' ', $option)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.invoice_type') }}</label>
                            <select name="type" class="form-select" data-control="select2" data-placeholder="{{ trans('sw.choose') }}">
                                <option value="">{{ trans('sw.choose') }}</option>
                                @foreach($types as $option)
                                    <option value="{{ $option }}" @selected(request('type') === $option)>{{ ucfirst($option) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.source') }}</label>
                            <select name="source" class="form-select" data-control="select2" data-placeholder="{{ trans('sw.choose') }}">
                                <option value="">{{ trans('sw.choose') }}</option>
                                @foreach($sources as $key => $label)
                                    <option value="{{ $key }}" @selected(request('source') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range') }}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" id="from_date" value="{{ request('from') }}" placeholder="{{ trans('sw.from') }}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to') }}</span>
                                <input type="text" class="form-control" name="to" id="to_date" value="{{ request('to') }}" placeholder="{{ trans('sw.to') }}" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6">
                            {{ trans('admin.reset') }}
                        </button>
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
            <form class="d-flex" action="{{ route('sw.reportZatcaInvoices') }}" method="get" style="max-width: 400px;">
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="{{ request('search') }}" placeholder="{{ trans('sw.search_on') }}">
                <button class="btn btn-primary ms-2" type="submit">
                    <i class="ki-outline ki-magnifier fs-3"></i>
                </button>
            </form>
        </div>
        <!--end::Search-->

        <!--begin::Summary-->
        <div class="d-flex align-items-center mb-5">
            <div class="symbol symbol-50px me-5">
                <div class="symbol-label bg-light-primary">
                    <i class="ki-outline ki-chart-simple fs-2x text-primary"></i>
                </div>
            </div>
            <div>
                <div class="fs-6 fw-semibold text-gray-600">{{ trans('admin.total_count') }}</div>
                <div class="fs-2 fw-bold text-gray-900">{{ $total }}</div>
            </div>
        </div>
        <!--end::Summary-->

        <!--begin::Table-->
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted text-uppercase gs-0">
                            <th class="min-w-70px text-nowrap">
                                <i class="ki-outline ki-barcode fs-5 me-2"></i>{{ trans('sw.id') }}
                            </th>
                            <th class="min-w-160px text-nowrap">
                                <i class="ki-outline ki-document fs-5 me-2"></i>{{ trans('sw.invoice_number') }}
                            </th>
                            <th class="min-w-140px text-nowrap">
                                <i class="ki-outline ki-tag fs-5 me-2"></i>{{ trans('sw.invoice_type') }}
                            </th>
                            <th class="min-w-160px text-nowrap">
                                <i class="ki-outline ki-information-2 fs-5 me-2"></i>{{ trans('sw.source') }}
                            </th>
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-user fs-5 me-2"></i>{{ trans('sw.buyer_name') }}
                            </th>
                            <th class="min-w-180px text-nowrap">
                                <i class="ki-outline ki-shield-tick fs-5 me-2"></i>{{ trans('sw.buyer_tax_number') }}
                            </th>
                            <th class="min-w-140px text-end text-nowrap">
                                <i class="ki-outline ki-dollar fs-5 me-2"></i>{{ trans('sw.total_amount') }}
                            </th>
                            <th class="min-w-140px text-end text-nowrap">
                                <i class="ki-outline ki-percentage fs-5 me-2"></i>{{ trans('sw.vat_amount') }}
                            </th>
                            <th class="min-w-140px text-nowrap">
                                <i class="ki-outline ki-status fs-5 me-2"></i>{{ trans('sw.status') }}
                            </th>
                            <th class="min-w-160px text-nowrap">
                                <i class="ki-outline ki-send fs-5 me-2"></i>{{ trans('sw.sent_at') }}
                            </th>
                            <th class="min-w-160px text-nowrap">
                                <i class="ki-outline ki-calendar fs-5 me-2"></i>{{ trans('sw.created_at') }}
                            </th>
                            <th class="min-w-120px text-end text-nowrap">
                                <i class="ki-outline ki-setting-2 fs-5 me-2"></i>{{ trans('admin.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-800">
                        @forelse($invoices as $invoice)
                            @php
                                $sourceLabel = trans('sw.source_money_box');
                                $sourceLink = null;
                                $sourceRelation = null;

                                if ($invoice->store_order_id) {
                                    $sourceLabel = trans('sw.source_store_order');
                                    $sourceLink = route('sw.showStoreOrder', $invoice->store_order_id);
                                    $sourceRelation = optional($invoice->storeOrder);
                                } elseif ($invoice->non_member_id) {
                                    $sourceLabel = trans('sw.source_non_member');
                                    $sourceLink = route('sw.showOrderSubscriptionNonMember', $invoice->non_member_id);
                                    $sourceRelation = optional($invoice->nonMember);
                                } elseif ($invoice->member_pt_subscription_id) {
                                    $sourceLabel = trans('sw.source_pt_member');
                                    $sourceLink = route('sw.showOrderPTSubscription', $invoice->member_pt_subscription_id);
                                    $sourceRelation = optional($invoice->ptMember);
                                } elseif ($invoice->member_subscription_id) {
                                    $sourceLabel = trans('sw.source_member');
                                    $sourceLink = route('sw.showOrderSubscription', $invoice->member_subscription_id);
                                    $sourceRelation = optional($invoice->memberSubscription);
                                } elseif ($invoice->member_id) {
                                    $sourceLabel = trans('sw.source_member');
                                    $sourceLink = route('sw.editMember', $invoice->member_id);
                                    $sourceRelation = optional($invoice->member);
                                } elseif ($invoice->money_box_id) {
                                    $sourceLabel = trans('sw.source_money_box');
                                    $sourceRelation = optional($invoice->moneyBox);
                                }
                            @endphp
                            <tr>
                                <td>#{{ $invoice->id }}</td>
                                <td class="fw-bold">{{ $invoice->invoice_number }}</td>
                                <td>{{ ucfirst($invoice->invoice_type) }}</td>
                                <td>
                                    @if($sourceLink)
                                        <a href="{{ $sourceLink }}" class="text-primary text-hover-dark fw-semibold">
                                            {{ $sourceLabel }}
                                        </a>
                                    @else
                                        <span class="fw-semibold">{{ $sourceLabel }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $invoice->buyer_name ?? '—' }}</div>
                                    @if($sourceRelation && filled(optional($sourceRelation)->name))
                                        <div class="text-muted fs-7">{{ $sourceRelation->name }}</div>
                                    @endif
                                </td>
                                <td>{{ $invoice->buyer_tax_number ?? '—' }}</td>
                                <td class="text-end">{{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="text-end">{{ number_format($invoice->vat_amount, 2) }}</td>
                                <td>
                                    <span class="badge badge-light-primary fw-bold">
                                        {{ ucfirst(str_replace('_', ' ', $invoice->zatca_status)) }}
                                    </span>
                                </td>
                                <td>{{ optional($invoice->zatca_sent_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>{{ optional($invoice->created_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($invoice->zatca_qr_code)
                                            <button type="button"
                                                    class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#qrModal-{{ $invoice->id }}"
                                                    title="{{ trans('sw.zatca_invoice_details') }}">
                                                <i class="ki-outline ki-eye fs-3"></i>
                                            </button>

                                            <div class="modal fade" id="qrModal-{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ trans('sw.zatca_invoice_details') }}</h5>
                                                            <button type="button" class="btn btn-sm btn-icon btn-light" data-bs-dismiss="modal">
                                                                <i class="ki-outline ki-cross fs-2"></i>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body text-center">
                                                            @php
                                                                $qrSrc = $invoice->zatca_qr_code;
                                                                if ($qrSrc && !\Illuminate\Support\Str::startsWith($qrSrc, 'data:image')) {
                                                                    $qrSrc = 'data:image/png;base64,' . $qrSrc;
                                                                }
                                                            @endphp
                                                            <img src="{{ $qrSrc }}" alt="QR" class="img-thumbnail mb-3" style="width:180px;height:180px;">
                                                            <div class="fw-semibold">{{ $invoice->invoice_number }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-10 text-muted fw-semibold">
                                    {{ trans('sw.no_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $invoices->withQueryString()->links() }}
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"
            type="text/javascript"></script>
    @parent
    <script>
        jQuery(document).ready(function () {
            const today = new Date();
            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true,
                orientation: 'bottom auto',
                defaultDate: {year: today.getFullYear(), month: today.getMonth(), day: today.getDate()},
                defaultViewDate: {year: today.getFullYear(), month: today.getMonth(), day: today.getDate()}
            });

            $('button[type="reset"]').on('click', function () {
                const form = $(this).closest('form');
                setTimeout(() => {
                    form.find('input[type="text"]').val('');
                    form.find('select').val('').trigger('change');
                }, 50);
            });
        });
    </script>
@endsection



