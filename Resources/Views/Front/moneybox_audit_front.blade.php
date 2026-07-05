@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listMoneyBox') }}" class="text-muted text-hover-primary">{{ trans('sw.moneybox') }}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
    </ul>
    <!--end::Breadcrumb-->
@endsection

@section('page_body')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex flex-column">
                    <span class="fs-3 fw-bold">{{ trans('sw.moneybox_audit') }}</span>
                    <span class="text-muted fs-7">{{ trans('sw.moneybox_audit_desc') }}</span>
                </div>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" id="run_audit_btn">
                    <i class="ki-outline ki-arrows-circle fs-6 me-1"></i>
                    <span id="run_audit_btn_label">{{ trans('sw.moneybox_audit_run') }}</span>
                    <span class="spinner-border spinner-border-sm ms-2 d-none" id="run_audit_spinner"></span>
                </button>
            </div>
        </div>
        <div class="card-body pt-0">
            <div id="audit_results">
                <div class="text-muted text-center py-10">{{ trans('sw.moneybox_audit_run') }} &rarr;</div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const auditScanUrl = '{{ route('sw.auditMoneyBoxScan') }}';
        const auditFixUrl  = '{{ route('sw.auditMoneyBoxFix') }}';
        const csrfToken    = '{{ csrf_token() }}';

        function numberFmt(n) {
            return Number(n).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function renderSection(title, rows, columns, rowRenderer) {
            if (!rows || rows.length === 0) return '';
            let html = '<div class="mb-8">';
            html += '<h4 class="mb-3">' + title + ' <span class="badge badge-light-danger">' + rows.length + '</span></h4>';
            html += '<div class="table-responsive"><table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">';
            html += '<thead><tr class="fw-bold text-muted">';
            columns.forEach(c => html += '<th>' + c + '</th>');
            html += '</tr></thead><tbody>';
            rows.forEach(r => html += rowRenderer(r));
            html += '</tbody></table></div></div>';
            return html;
        }

        function runAudit() {
            $('#run_audit_btn').prop('disabled', true);
            $('#run_audit_spinner').removeClass('d-none');

            $.ajax({
                url: auditScanUrl,
                type: 'POST',
                dataType: 'json',
                data: { _token: csrfToken },
                success: function (response) {
                    $('#run_audit_btn').prop('disabled', false);
                    $('#run_audit_spinner').addClass('d-none');

                    if (!response.success) {
                        Swal.fire("{{ trans('admin.operation_failed') }}", response.message || '', 'error');
                        return;
                    }

                    let html = '<div class="text-muted mb-5">' + response.scanned + ' rows scanned.</div>';

                    html += renderSection(
                        "{{ trans('sw.moneybox_audit_amount_mismatches') }}",
                        response.amount_mismatches,
                        ["ID", "{{ trans('sw.date') }}", "{{ trans('sw.amount') }}", "{{ trans('sw.invoice') }}", "{{ trans('sw.moneybox_audit_suggested_amount') }}", "{{ trans('admin.actions') }}"],
                        function (r) {
                            return '<tr>'
                                + '<td>#' + r.id + '</td>'
                                + '<td>' + r.created_at + '</td>'
                                + '<td class="text-danger fw-bold">' + numberFmt(r.stored_amount) + '</td>'
                                + '<td>#' + r.invoice_id + ' (' + numberFmt(r.invoice_total) + ')</td>'
                                + '<td class="text-success fw-bold">'
                                + '<input type="number" step="0.01" class="form-control form-control-sm d-inline-block w-100px suggested-amount-input" value="' + r.suggested_amount + '">'
                                + '</td>'
                                + '<td><button class="btn btn-sm btn-light-primary apply-fix-btn" data-id="' + r.id + '">{{ trans('sw.moneybox_audit_apply_fix') }}</button></td>'
                                + '</tr>';
                        }
                    );

                    html += renderSection(
                        "{{ trans('sw.moneybox_audit_order_issues') }}",
                        response.order_issues,
                        ["ID", "{{ trans('sw.date') }}", "Max ID seen before"],
                        function (r) {
                            return '<tr><td>#' + r.id + '</td><td>' + r.created_at + '</td><td>' + r.max_id_seen_before + '</td></tr>';
                        }
                    );

                    html += renderSection(
                        "{{ trans('sw.moneybox_audit_chain_breaks') }}",
                        response.chain_breaks,
                        ["ID", "{{ trans('sw.date') }}", "Prev ID", "Expected amount_before", "Stored amount_before", "Diff"],
                        function (r) {
                            return '<tr><td>#' + r.id + '</td><td>' + r.created_at + '</td><td>#' + r.prev_id + '</td>'
                                + '<td>' + numberFmt(r.expected_amount_before) + '</td>'
                                + '<td>' + numberFmt(r.stored_amount_before) + '</td>'
                                + '<td>' + numberFmt(r.diff) + '</td></tr>';
                        }
                    );

                    if (!response.amount_mismatches.length && !response.order_issues.length && !response.chain_breaks.length) {
                        html += '<div class="alert alert-success">{{ trans('sw.moneybox_audit_no_issues') }}</div>';
                    }

                    $('#audit_results').html(html);
                },
                error: function () {
                    $('#run_audit_btn').prop('disabled', false);
                    $('#run_audit_spinner').addClass('d-none');
                    Swal.fire("{{ trans('admin.operation_failed') }}", "{{ trans('admin.something_went_wrong') }}", 'error');
                }
            });
        }

        $('#run_audit_btn').on('click', runAudit);

        $(document).on('click', '.apply-fix-btn', function () {
            const id = $(this).data('id');
            const row = $(this).closest('tr');
            const amount = row.find('.suggested-amount-input').val();

            Swal.fire({
                title: "{{ trans('admin.are_you_sure') }}",
                text: "{{ trans('sw.moneybox_audit_fix_warning') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "{{ trans('admin.yes_delete') }}",
                cancelButtonText: "{{ trans('admin.cancel') }}",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: () => {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: auditFixUrl,
                            type: 'POST',
                            dataType: 'json',
                            data: { id: id, amount: amount, _token: csrfToken },
                            success: function (response) {
                                if (response.success) {
                                    resolve(response);
                                } else {
                                    reject(response.message || "{{ trans('admin.something_went_wrong') }}");
                                }
                            },
                            error: function () {
                                reject("{{ trans('admin.something_went_wrong') }}");
                            }
                        });
                    }).catch(error => {
                        Swal.showValidationMessage(error);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "{{ trans('admin.success') }}",
                        text: "{{ trans('sw.moneybox_audit_fix_success') }}",
                        icon: "success",
                        timer: 1200,
                        showConfirmButton: false
                    });
                    setTimeout(runAudit, 1200);
                }
            });
        });
    </script>
@endsection
