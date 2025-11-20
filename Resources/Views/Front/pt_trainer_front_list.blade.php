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
        .actions-column {
            min-width: 120px;
            text-align: right;
        }

        .actions-column .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .actions-column .d-flex {
            gap: 0.25rem;
        }

        .trainer-ledger-modal .table thead th {
            white-space: nowrap;
        }
    </style>
@endsection

@section('page_body')
<div class="card card-flush">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-user fs-2 me-3"></i>
                    <span class="fs-4 fw-semibold text-gray-900">{{ $title }}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button"
                        class="btn btn-sm btn-flex btn-light-primary"
                        data-bs-toggle="collapse"
                        data-bs-target="#kt_pt_trainer_filters">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter') }}
                </button>
                    @if(in_array('createPTTrainer', (array) $swUser->permissions) || $swUser->is_super_user)
                        <a href="{{ route('sw.createPTTrainer') }}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                            {{ trans('admin.add') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="d-flex align-items-center position-relative my-1 mb-5">
            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
            <form class="d-flex" action="" method="get" style="max-width: 400px;">
                    <input type="text" name="search" class="form-control form-control-solid ps-12" value="{{ request('search') }}" placeholder="{{ trans('sw.search_on') }}">
                <button class="btn btn-primary" type="submit">
                    <i class="ki-outline ki-magnifier fs-3"></i>
                </button>
            </form>
        </div>

        @php
            $trainerFiltersOpen = request()->filled('from') || request()->filled('to') || request()->filled('class_id');
        @endphp
        <div class="collapse {{ $trainerFiltersOpen ? 'show' : '' }}" id="kt_pt_trainer_filters">
            <form id="trainer_filter_form" method="get" class="row g-3 align-items-end mb-5">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <div class="col-lg-3 col-md-6">
                    <label for="filter_from" class="form-label fw-semibold">{{ trans('sw.date_from') }}</label>
                    <input type="text"
                           class="form-control datepicker"
                           name="from"
                           id="filter_from"
                           value="{{ request('from') }}"
                           autocomplete="off"
                           placeholder="YYYY-MM-DD">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="filter_to" class="form-label fw-semibold">{{ trans('sw.date_to') }}</label>
                    <input type="text"
                           class="form-control datepicker"
                           name="to"
                           id="filter_to"
                           value="{{ request('to') }}"
                           autocomplete="off"
                           placeholder="YYYY-MM-DD">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="filter_class_id" class="form-label fw-semibold">{{ trans('sw.pt_class') }}</label>
                    <select name="class_id"
                            id="filter_class_id"
                            class="form-select select2"
                            data-placeholder="{{ trans('admin.choose')}}...">
                        <option value="">{{ trans('admin.choose')}}...</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ki-outline ki-filter fs-3 me-1"></i>{{ trans('sw.filter_results') }}
                    </button>
                    <a href="{{ route('sw.listPTTrainer') }}" class="btn btn-light flex-grow-1">
                        <i class="ki-outline ki-arrows-circle fs-3 me-1"></i>{{ trans('admin.reset') }}
                    </a>
                </div>
            </form>
        </div>
            <p class="text-muted fs-7 mb-8">{{ trans('sw.trainer_commission_filters_help') }}</p>

        <div class="d-flex align-items-center mb-5">
            <div class="symbol symbol-50px me-5">
                <div class="symbol-label bg-light-primary">
                    <i class="ki-outline ki-chart-simple fs-2x text-primary"></i>
                </div>
            </div>
            <div class="d-flex flex-column">
                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('admin.total_count') }}</span>
                <span class="fs-2 fw-bold text-primary">{{ $total }}</span>
            </div>
        </div>

            <div class="d-flex align-items-center mb-10">
                <div class="symbol symbol-50px me-5">
                    <div class="symbol-label bg-light-warning">
                        <i class="ki-outline ki-dollar fs-2x text-warning"></i>
                    </div>
                </div>
                <div class="d-flex flex-column">
                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.trainer_pending_commission_total') }}</span>
                    <span class="fs-5 fw-bold text-warning" id="pending-total-amount"
                          data-amount="{{ $pendingTotals['amount'] ?? 0 }}"
                          data-count="{{ $pendingTotals['count'] ?? 0 }}">
                        {{ number_format($pendingTotals['amount'] ?? 0, 2) }}
                        <small class="text-muted ms-2" id="pending-total-count">
                            {{ trans('sw.sessions_label', ['count' => $pendingTotals['count'] ?? 0]) }}
                        </small>
                    </span>
                </div>
            </div>

            @if($trainers->count())
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_pt_trainers_table">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.name') }}
                        </th>
                        <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-phone fs-6 me-2"></i>{{ trans('sw.phone') }}
                            </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.trainer_pending_sessions') }}
                        </th>
                            <th class="min-w-120px text-nowrap">
                                <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.trainer_pending_commission') }}
                        </th>
                        <th class="text-end min-w-70px actions-column">
                                <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                        @foreach($trainers as $trainer)
                            <tr id="trainer-row-{{ $trainer->id }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-3">
                                        <div class="symbol-label fs-3 bg-light-primary text-primary">
                                            <i class="ki-outline ki-user fs-2"></i>
                                        </div>
                                    </div>
                                        <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            {{ $trainer->name }}
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ $trainer->phone }}</span>
                            </td>
                            <td class="pe-0">
                                    <span class="fw-bold" id="trainer-{{ $trainer->id }}-pending-count">
                                        {{ $trainer->pending_commission_count }}
                                    </span>
                                </td>
                                <td class="pe-0">
                                    <span class="fw-bold text-primary" id="trainer-{{ $trainer->id }}-pending-amount">
                                        {{ number_format($trainer->pending_commission_total, 2) }}
                                    </span>
                            </td>
                            <td class="text-end actions-column">
                                <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                        <a data-bs-toggle="modal"
                                           data-bs-target="#trainerLedger{{ $trainer->id }}"
                                           data-trainer="{{ $trainer->id }}"
                                           data-fetch-url="{{ route('sw.pendingPTTrainerCommissions', $trainer->id) }}"
                                           class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm"
                                           title="{{ trans('sw.view_commissions') }}">
                                            <i class="ki-outline ki-dollar fs-2"></i>
                                        </a>
                                        @if(in_array('editPTTrainer', (array) $swUser->permissions) || $swUser->is_super_user)
                                            <a href="{{ route('sw.editPTTrainer', $trainer->id) }}"
                                               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                               title="{{ trans('admin.edit') }}">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                    @endif
                                        @if(in_array('deletePTTrainer', (array) $swUser->permissions) || $swUser->is_super_user)
                                        @if(request('trashed'))
                                                <a href="{{ route('sw.deletePTTrainer', $trainer->id) }}"
                                                   class="confirm_delete btn btn-icon btn-bg-light btn-active-color-success btn-sm"
                                                   title="{{ trans('admin.enable') }}">
                                                <i class="ki-outline ki-check-circle fs-2"></i>
                                            </a>
                                        @else
                                                <a href="{{ route('sw.deletePTTrainer', $trainer->id) }}"
                                                   class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                                                   title="{{ trans('admin.disable') }}">
                                                <i class="ki-outline ki-trash fs-2"></i>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            
            <div class="d-flex flex-stack flex-wrap pt-10">
                <div class="fs-6 fw-semibold text-gray-700">
                    {{ trans('sw.showing_entries', [
                        'from' => $trainers->firstItem() ?? 0,
                        'to' => $trainers->lastItem() ?? 0,
                            'total' => $trainers->total(),
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $trainers->appends($search_query)->render() !!}
                </ul>
            </div>
        @else
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                        <i class="ki-outline ki-user fs-2"></i>
                    </div>
                </div>
                    <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found') }}</h4>
            </div>
        @endif
    </div>
</div>

    @foreach($trainers as $trainer)
        <div class="modal fade trainer-ledger-modal"
             id="trainerLedger{{ $trainer->id }}"
             tabindex="-1"
             role="dialog"
             aria-hidden="true"
             data-trainer-id="{{ $trainer->id }}"
             data-fetch-url="{{ route('sw.pendingPTTrainerCommissions', $trainer->id) }}">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ki-outline ki-user-check fs-2 text-warning {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                            {{ trans('sw.trainer_commission_modal_title', ['name' => $trainer->name]) }}
                        </h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ trans('sw.close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info d-flex justify-content-between align-items-center ledger-summary d-none" role="alert">
                                    <div>
                                <strong class="ledger-session-count">{{ trans('sw.sessions_label', ['count' => 0]) }}</strong>
                                <div class="text-muted fs-7">{{ trans('sw.trainer_commission_pending_help') }}</div>
                                    </div>
                                    <div class="text-end">
                                <div class="fw-bold fs-5 text-primary ledger-total-amount">0.00</div>
                                    </div>
                                </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-light" data-action="select-all">{{ trans('sw.select_all') }}</button>
                                <button type="button" class="btn btn-light" data-action="clear-selection">{{ trans('sw.clear_selection') }}</button>
                            </div>
                            <div class="text-muted fs-7">{{ trans('sw.ledger_table_hint') }}</div>
                        </div>

                                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered align-middle">
                                                <thead class="table-light">
                                <tr>
                                    <th class="text-center w-45px">
                                        <input type="checkbox" class="form-check-input" data-role="select-all">
                                                    </th>
                                    <th>{{ trans('sw.session_date') }}</th>
                                    <th>{{ trans('sw.subscriber') }}</th>
                                    <th>{{ trans('sw.pt_class') }}</th>
                                    <th class="text-end">{{ trans('sw.commission_amount') }}</th>
                                    <th class="text-end">{{ trans('sw.commission_rate') }}</th>
                                                </tr>
                                                </thead>
                                <tbody class="trainer-ledger-body">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">{{ trans('sw.loading') }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-action="settle-selected">
                            <i class="ki-outline ki-check-circle {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                            {{ trans('sw.settle_commissions_button') }}
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
    @parent
    <script src="{{ asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js') }}" type="text/javascript"></script>
    <script>
        (function ($) {
            'use strict';

            const trainerLedgerI18n = {
                successTitle: "{{ trans('admin.done') }}",
                successText: "{{ trans('admin.successfully_processed') }}",
                errorTitle: "{{ trans('admin.operation_failed') }}",
                errorText: "{{ trans('sw.trainer_commission_error') }}",
                noSelection: "{{ trans('sw.no_commission_selected') }}",
                loading: "{{ trans('sw.loading') }}",
                noCommissions: "{{ trans('sw.no_pending_commissions') }}",
                sessionsLabel: "{{ trans('sw.sessions_label', ['count' => ':count']) }}",
            };

            function collectFilters() {
                return {
                    from: $('#filter_from').val() || null,
                    to: $('#filter_to').val() || null,
                    class_id: $('#filter_class_id').val() || null,
                };
            }

            function renderLedgerRows(modal, response) {
                const $body = modal.find('.trainer-ledger-body');
                const $summary = modal.find('.ledger-summary');
                const $settleButton = modal.find('[data-action="settle-selected"]');

                if (!response) {
                    console.error('Empty response received');
                    $body.html(`<tr><td colspan="6" class="text-center text-danger">${trainerLedgerI18n.errorText}</td></tr>`);
                    $summary.addClass('d-none');
                    $settleButton.prop('disabled', true);
                    return;
                }

                const commissions = response.commissions || [];
                const totals = response.totals || {amount: 0, count: 0};

                console.log('Rendering ledger rows:', { commissionsCount: commissions.length, totals });

                if (!commissions.length) {
                    $body.html(`<tr><td colspan="6" class="text-center text-muted">${trainerLedgerI18n.noCommissions}</td></tr>`);
                    $summary.addClass('d-none');
                    $settleButton.prop('disabled', true);
                    return;
                }

                const rows = commissions.map(function (item) {
                    const sessionDate = item.session_date || '-';
                    const memberName = item.member_name || '-';
                    const memberCode = item.member_code || '';
                    const className = item.class_name || '-';
                    const commissionAmount = item.commission_amount || '0.00';
                    const commissionRate = item.commission_rate || '0.00';
                    const commissionId = item.id || '';

                    return `<tr>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input commission-select" value="${commissionId}">
                        </td>
                        <td>${sessionDate}</td>
                        <td>
                            <div class="fw-semibold text-gray-900">${memberName}</div>
                            ${memberCode ? `<span class="text-muted fs-7">${memberCode}</span>` : ''}
                        </td>
                        <td>${className}</td>
                        <td class="text-end">${commissionAmount}</td>
                        <td class="text-end">${commissionRate}%</td>
                    </tr>`;
                }).join('');

                $body.html(rows);

                const sessionsLabel = trainerLedgerI18n.sessionsLabel.replace(':count', totals.count || 0);
                const totalAmount = parseFloat(totals.amount || 0).toFixed(2);
                modal.find('.ledger-total-amount').text(totalAmount);
                modal.find('.ledger-session-count').text(sessionsLabel);
                $summary.removeClass('d-none');
                $settleButton.prop('disabled', false);

                bindLedgerInteractions(modal);
            }

            function bindLedgerInteractions(modal) {
                modal.find('[data-role="select-all"]').prop('checked', false);
                modal.find('[data-role="select-all"]').off('change').on('change', function () {
                    const checked = $(this).is(':checked');
                    modal.find('.commission-select').prop('checked', checked);
                });

                modal.find('[data-action="select-all"]').off('click').on('click', function () {
                    modal.find('.commission-select').prop('checked', true);
                });

                modal.find('[data-action="clear-selection"]').off('click').on('click', function () {
                    modal.find('.commission-select').prop('checked', false);
                    modal.find('[data-role="select-all"]').prop('checked', false);
                });
            }

            function loadTrainerLedger(modal) {
                const fetchUrl = modal.data('fetchUrl');
                if (!fetchUrl) {
                    console.error('No fetch URL found for trainer ledger');
                    return;
                }
                const $body = modal.find('.trainer-ledger-body');
                $body.html(`<tr><td colspan="6" class="text-center text-muted">${trainerLedgerI18n.loading}</td></tr>`);

                const filters = collectFilters();
                console.log('Loading trainer ledger:', { fetchUrl, filters });

                $.ajax({
                    url: fetchUrl,
                    type: 'GET',
                    data: filters,
                    dataType: 'json',
                    success: function (response) {
                        console.log('Trainer ledger response:', response);
                        renderLedgerRows(modal, response || {});
                    },
                    error: function (xhr, status, error) {
                        console.error('Trainer ledger error:', { xhr, status, error, responseText: xhr.responseText });
                        $body.html(`<tr><td colspan="6" class="text-center text-danger">${trainerLedgerI18n.errorText} (${xhr.status}: ${error})</td></tr>`);
                        modal.find('.ledger-summary').addClass('d-none');
                        modal.find('[data-action="settle-selected"]').prop('disabled', true);
                    }
                });
            }

            function selectedCommissionIds(modal) {
                return modal.find('.commission-select:checked').map(function () {
                    return $(this).val();
                }).get();
            }

            function updateTrainerRow(trainerId, remaining) {
                const count = remaining?.filtered_count ?? remaining?.count ?? 0;
                const amount = parseFloat(remaining?.filtered_amount ?? remaining?.amount ?? 0).toFixed(2);

                $(`#trainer-${trainerId}-pending-count`).text(count);
                $(`#trainer-${trainerId}-pending-amount`).text(amount);
            }

            function updatePendingTotals(totals) {
                const amount = parseFloat(totals?.amount ?? 0).toFixed(2);
                const count = totals?.count ?? 0;
                const $amount = $('#pending-total-amount');

                $amount.data('amount', amount);
                $amount.data('count', count);
                $amount.text(amount);

                const countLabel = trainerLedgerI18n.sessionsLabel.replace(':count', count);
                $('#pending-total-count').text(countLabel);
            }

            function settleTrainerCommissions(modal) {
                const trainerId = modal.data('trainerId');
                const commissionIds = selectedCommissionIds(modal);

                if (!trainerId || !commissionIds.length) {
                    swal(trainerLedgerI18n.errorTitle, trainerLedgerI18n.noSelection, 'warning');
                    return;
                }

                const $button = modal.find('[data-action="settle-selected"]');
                $button.prop('disabled', true);

            $.ajax({
                    url: '{{ route('sw.createTrainerPayPercentageAmountForm') }}',
                    type: 'POST',
                    data: {
                        trainer_id: trainerId,
                        commission_ids: commissionIds,
                        class_id: $('#filter_class_id').val(),
                        from: $('#filter_from').val(),
                        to: $('#filter_to').val(),
                        _token: '{{ csrf_token() }}',
                    },
                }).done(function (response) {
                    swal(trainerLedgerI18n.successTitle, trainerLedgerI18n.successText, 'success');
                    updateTrainerRow(trainerId, response?.remaining || {});
                    updatePendingTotals(response?.pending_totals || {});
                    loadTrainerLedger(modal);
                }).fail(function () {
                    swal(trainerLedgerI18n.errorTitle, trainerLedgerI18n.errorText, 'error');
                }).always(function () {
                    $button.prop('disabled', false);
                });
            }

            // Ensure DOM is ready before binding events
            $(document).ready(function() {
                console.log('Binding trainer ledger modal events...');
                
                // Bind event to all existing modals
                $('.trainer-ledger-modal').on('show.bs.modal', function (event) {
                    console.log('Modal show event triggered');
                    const modal = $(this);
                    const trigger = $(event.relatedTarget);
                    
                    console.log('Modal:', modal);
                    console.log('Trigger:', trigger);
                    console.log('Fetch URL from trigger:', trigger.data('fetchUrl'));
                    console.log('Trainer ID from trigger:', trigger.data('trainer'));
                    
                    const fetchUrl = trigger.data('fetchUrl') || modal.data('fetchUrl');
                    const trainerId = trigger.data('trainer') || modal.data('trainerId');
                    
                    modal.data('fetchUrl', fetchUrl);
                    modal.data('trainerId', trainerId);
                    
                    console.log('Final fetch URL:', fetchUrl);
                    console.log('Final trainer ID:', trainerId);
                    
                    modal.find('.ledger-summary').addClass('d-none');
                    modal.find('.ledger-total-amount').text('0.00');
                    modal.find('.ledger-session-count').text(trainerLedgerI18n.sessionsLabel.replace(':count', 0));
                    
                    if (fetchUrl) {
                        loadTrainerLedger(modal);
                    } else {
                        console.error('No fetch URL found for modal');
                    }
                });

                // Also bind click event directly to the button as fallback
                $('[data-bs-target^="#trainerLedger"]').on('click', function(e) {
                    console.log('Button clicked:', this);
                    const target = $(this).data('bsTarget') || $(this).attr('data-bs-target');
                    console.log('Target modal:', target);
                    
                    // Small delay to ensure modal is shown before loading data
                    setTimeout(function() {
                        const modal = $(target);
                        if (modal.length) {
                            const fetchUrl = $(e.currentTarget).data('fetchUrl') || modal.data('fetchUrl');
                            const trainerId = $(e.currentTarget).data('trainer') || modal.data('trainerId');
                            
                            console.log('Fallback: Loading ledger for trainer:', trainerId, 'URL:', fetchUrl);
                            
                            if (fetchUrl && modal.is(':visible')) {
                                modal.data('fetchUrl', fetchUrl);
                                modal.data('trainerId', trainerId);
                                loadTrainerLedger(modal);
                            }
                        }
                    }, 300);
                });

                $('.trainer-ledger-modal').on('hidden.bs.modal', function () {
                    $(this).find('.trainer-ledger-body').empty();
                });

                // Use event delegation for settle button (in case it's added dynamically)
                $(document).on('click', '.trainer-ledger-modal [data-action="settle-selected"]', function () {
                    const modal = $(this).closest('.trainer-ledger-modal');
                    settleTrainerCommissions(modal);
                });

                // Initialize Select2
                $('.select2').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: "{{ trans('admin.choose')}}..."
                });

                // Initialize datepicker
                $('.datepicker').datepicker({
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                    todayHighlight: true
                });
            }); // End document.ready
        })(jQuery);
    </script>
@endsection
