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
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css"/>
    <style>
        .avatar-md {
            width: 48px !important;
            height: 48px !important;
            font-size: 24px !important;
        }

        .rounded-circle {
            border-radius: 50% !important;
        }

        .userlist-table .table th, .userlist-table .table td {
            padding: 0.75rem;
            vertical-align: middle;
            display: table-cell;
        }

        .userlist-table {
            overflow-x: scroll;
        }

        .table-vcenter {
            table-layout: fixed;
            overflow-x: auto !important;
            width: 100% !important;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive table {
            min-width: 800px;
        }

        @media (max-width: 767px) {
            .table-vcenter {
                display: block !important;
            }
            
            .table-responsive {
                border: none;
            }
            
            .table-responsive table {
                min-width: 1000px;
            }
        }

        /* Actions column styling */
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
    </style>
@endsection

@section('page_body')

<!--begin::Report-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-user-tick fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
                <!--begin::Create Attendance-->
                @if(in_array('createAttendance', (array)$swUser->permissions) || $swUser->is_super_user)

                <button type="button" class="btn btn-sm btn-flex btn-success" id="btn-create-attendance">
                    <i class="ki-outline ki-plus fs-6"></i>
                    {{ trans('sw.create_attendance') }}
                </button>
                @endif
                <!--end::Create Attendance-->

                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_today_members_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->

                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportTodayMemberPDF', 'exportTodayMemberExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download') }}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportTodayMemberExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportTodayMemberExcel', request()->query())}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export') }}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportTodayMemberPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportTodayMemberPDF', request()->query())}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.pdf_export') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <!--end::Export-->
            </div>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Filter-->
        <div class="collapse" id="kt_today_members_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-4">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" id="from_date" value="@php echo @strip_tags($_GET['from']) @endphp" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" name="to" id="to_date" value="@php echo @strip_tags($_GET['to']) @endphp" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6">{{ trans('admin.reset')}}</button>
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
            <form class="d-flex" action="" method="get" style="max-width: 400px;">
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="@php echo @strip_tags($_GET['search']) @endphp" placeholder="{{ trans('sw.search_on')}}">
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
                    <i class="ki-outline ki-chart-simple fs-2x text-primary"></i>
                </div>
            </div>
            <div class="d-flex flex-column">
                <span class="fs-6 fw-semibold text-gray-900">{{ trans('admin.total_count')}}</span>
                <span class="fs-2 fw-bold text-primary">{{ $total }}</span>
            </div>
        </div>
        <!--end::Total count-->

        @if(count($logs) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_today_members_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-250px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.member')}}
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="ki-outline ki-list fs-6 me-2"></i>{{ trans('sw.membership')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-shield-tick fs-6 me-2"></i>{{ trans('sw.subscription_status')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-status fs-6 me-2"></i>{{ trans('sw.status')}}
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="ki-outline ki-user-tick fs-6 me-2"></i>{{ trans('sw.user')}}
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.remaining_amount')}}
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date')}}
                            </th>
                            <th class="min-w-100px text-center text-nowrap">
                                <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('sw.actions')}}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($logs as $key=> $log)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-50px me-3">
                                            <img alt="avatar" class="rounded-circle" src="{{@$log->member->image}}">
                                        </div>
                                        <div class="d-flex justify-content-start flex-column">
                                            <a href="#" class="text-gray-800 text-hover-primary fs-6 fw-bold">{{ @$log->member->name }}</a>
                                            <span class="text-muted fw-semibold d-block fs-7">{{ trans('sw.identification_code') }}: {{ @$log->member->code }}</span>
                                            <span class="text-muted fw-semibold d-block fs-7">{{ trans('sw.phone') }}: {{ @$log->member->phone }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ @$log->member->member_subscription_info->subscription->name }}</span>
                                </td>
                                <td>
                                    @php
                                        $subscription = @$log->member->member_subscription_info;
                                        $status = $subscription->status ?? null;
                                        $expireDate = $subscription->expire_date ?? null;
                                        $startDate = $subscription->start_date ?? null;
                                        $now = \Carbon\Carbon::now();

                                        if ($status == \Modules\Software\Classes\TypeConstants::Active) {
                                            $badgeClass = 'badge-light-success';
                                            $statusText = trans('sw.active');
                                        } elseif ($status == \Modules\Software\Classes\TypeConstants::Freeze) {
                                            $badgeClass = 'badge-light-info';
                                            $statusText = trans('sw.frozen');
                                        } elseif ($status == \Modules\Software\Classes\TypeConstants::Expired) {
                                            $badgeClass = 'badge-light-danger';
                                            $statusText = trans('sw.expire');
                                        } elseif ($startDate && \Carbon\Carbon::parse($startDate)->isFuture()) {
                                            $badgeClass = 'badge-light-warning';
                                            $statusText = trans('sw.upcoming');
                                        } else {
                                            $badgeClass = 'badge-light-secondary';
                                            $statusText = trans('sw.no_subscription');
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light-success">{{ trans('sw.attend') }}</span>
                                </td>
                                <td>
                                    @if(@$log->user)
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-gray-800">{{ @$log->user->name }}</span>
                                            {{-- <span class="text-muted fs-7">{{ @$log->user->email }}</span> --}}
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $member = @$log->member;
                                        $remainingAmount = (float)(@$member->member_remain_amount_subscriptions->sum('amount_remaining') ?? 0);
                                        $storeRemainingAmount = (float)(@$member->store_balance ?? 0);
                                    @endphp
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="ki-outline ki-dollar fs-7 text-success me-2"></i>
                                            <span class="fw-bold text-success">{{ number_format($remainingAmount, 2) }}</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="ki-outline ki-shop fs-7 text-primary me-2"></i>
                                            <span class="fw-bold text-primary">{{ number_format($storeRemainingAmount, 2) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="text-muted fw-bold d-flex align-items-center">
                                            <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                            <span>{{ $log->created_at ? $log->created_at->format('Y-m-d') : $log->updated_at->format('Y-m-d')}}</span>
                                        </div>
                                        <div class="text-muted fs-7 d-flex align-items-center">
                                            <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                            <span>{{ $log->created_at ? $log->created_at->format('h:i a') : $log->updated_at->format('h:i a') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                @if(in_array('deleteAttendance', (array)$swUser->permissions) || $swUser->is_super_user)

                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger delete-attendance-btn"
                                            data-attendance-id="{{ $log->id }}"
                                            data-member-name="{{ @$log->member->name }}"
                                            title="{{ trans('sw.delete_attendance') }}">
                                        <i class="ki-outline ki-trash fs-5"></i>
                                    </button>
                                    @endif
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
                        'from' => $logs->firstItem() ?? 0,
                        'to' => $logs->lastItem() ?? 0,
                        'total' => $logs->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $logs->appends($search_query)->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                        <i class="ki-outline ki-user-tick fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Report-->

<!--begin::Create Attendance Modal-->
<div class="modal fade" id="modal-create-attendance" tabindex="-1" aria-labelledby="modalCreateAttendanceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCreateAttendanceLabel">
                    <i class="ki-outline ki-user-tick fs-3 me-2"></i>
                    {{ trans('sw.create_attendance') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-create-attendance">
                @csrf
                <div class="modal-body">
                    <!--begin::Member Select-->
                    <div class="mb-5">
                        <label class="form-label required fs-6 fw-semibold">{{ trans('sw.member') }}</label>
                        <select class="form-select" id="member-select" name="member_id" required>
                            <option value="">{{ trans('sw.select_member') }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <!--end::Member Select-->

                    <!--begin::Attendance Date-->
                    <div class="mb-5">
                        <label class="form-label required fs-6 fw-semibold">{{ trans('sw.attendance_date') }}</label>
                        <input type="text" class="form-control" id="attendance-date" name="attendance_date"
                               placeholder="{{ trans('sw.select_date') }}" required autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>
                    <!--end::Attendance Date-->

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-6"></i>
                        {{ trans('sw.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-success" id="btn-submit-attendance">
                        <i class="ki-outline ki-check fs-6"></i>
                        {{ trans('sw.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end::Create Attendance Modal-->

@endsection

@section('scripts')
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    @parent
    <script>
        $(document).on('click', '#export', function (event) {
            event.preventDefault();
            $.ajax({
                url: $(this).attr('url'),
                cache: false,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var a = document.createElement("a");
                    a.href = response.file;
                    a.download = response.name;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });
        });

        $(document).on('click', '.remove_filter', function (event) {
            event.preventDefault();
            var filter = $(this).attr('id');
            $("#" + filter).val('');
            $("#form_filter").submit();
        });
        jQuery(document).ready(function() {
            var today = new Date();
            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true,
                orientation: 'bottom auto',
                defaultDate: { year: today.getFullYear(), month: today.getMonth(), day: today.getDate() },
                defaultViewDate: { year: today.getFullYear(), month: today.getMonth(), day: today.getDate() }
            });

            $('button[type="reset"]').on('click', function() {
                setTimeout(() => {
                    $(this).closest('form').find('select').trigger('change');
                }, 100);
            });

            // Initialize attendance date picker
            $('#attendance-date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true,
                orientation: 'bottom auto',
                defaultDate: { year: today.getFullYear(), month: today.getMonth(), day: today.getDate() },
                defaultViewDate: { year: today.getFullYear(), month: today.getMonth(), day: today.getDate() }
            }).datepicker('setDate', new Date());

            // Initialize Select2 for member search
            function initMemberSelect() {
                $('#member-select').select2({
                    dropdownParent: $('#modal-create-attendance'),
                    placeholder: '{{ trans("sw.search_member_by_code_or_name") }}',
                    allowClear: true,
                    ajax: {
                        url: '{{ route("sw.getMembersBySearch") }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.data.map(function(member) {
                                    return {
                                        id: member.id,
                                        text: member.name + ' (' + member.code + ')',
                                        member: member
                                    };
                                }),
                                pagination: {
                                    more: data.current_page < data.last_page
                                }
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0,
                    templateResult: formatMemberOption,
                    templateSelection: formatMemberSelection
                });
            }

            function formatMemberOption(member) {
                if (!member.id || !member.member) {
                    return member.text;
                }
                var $member = $(
                    '<div class="d-flex align-items-center">' +
                        '<div class="me-3">' +
                            '<img src="' + (member.member.image || '/assets/default-avatar.png') + '" class="rounded-circle" style="width: 32px; height: 32px;" />' +
                        '</div>' +
                        '<div>' +
                            '<div class="fw-bold">' + member.member.name + '</div>' +
                            '<div class="text-muted fs-7">{{ trans("sw.code") }}: ' + member.member.code + ' | {{ trans("sw.phone") }}: ' + (member.member.phone || '-') + '</div>' +
                        '</div>' +
                    '</div>'
                );
                return $member;
            }

            function formatMemberSelection(member) {
                return member.text || member.member?.name || '{{ trans("sw.select_member") }}';
            }

            // Open create attendance modal
            $('#btn-create-attendance').on('click', function() {
                $('#form-create-attendance')[0].reset();
                $('#member-select').val(null).trigger('change');
                $('#attendance-date').datepicker('setDate', new Date());
                $('.invalid-feedback').text('').hide();
                $('#form-create-attendance .form-control').removeClass('is-invalid');
                initMemberSelect();
                $('#modal-create-attendance').modal('show');
            });

            // Handle delete attendance
            $(document).on('click', '.delete-attendance-btn', function() {
                var attendanceId = $(this).data('attendance-id');
                var memberName = $(this).data('member-name');

                Swal.fire({
                    title: '{{ trans("sw.are_you_sure") }}',
                    text: '{{ trans("sw.delete_attendance_confirmation") }}' + ' ' + memberName + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '{{ trans("sw.yes_delete") }}',
                    cancelButtonText: '{{ trans("sw.cancel") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url("/user/log/attendance/delete") }}/' + attendanceId,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '{{ trans("admin.successfully_added") }}',
                                        text: response.message || '{{ trans("sw.attendance_deleted_successfully") }}',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: '{{ trans("sw.error") }}',
                                        text: response.message || '{{ trans("sw.operation_failed") }}'
                                    });
                                }
                            },
                            error: function(xhr) {
                                var errorMessage = '{{ trans("sw.operation_failed") }}';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ trans("sw.error") }}',
                                    text: errorMessage
                                });
                            }
                        });
                    }
                });
            });

            // Handle create attendance form submission
            $('#form-create-attendance').on('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                $('.invalid-feedback').text('').hide();
                $('.form-control').removeClass('is-invalid');

                var submitBtn = $('#btn-submit-attendance');
                var originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>{{ trans("sw.saving") }}...');

                var formData = {
                    member_id: $('#member-select').val(),
                    attendance_date: $('#attendance-date').val(),
                    _token: '{{ csrf_token() }}'
                };

                $.ajax({
                    url: '{{ route("sw.createAttendance") }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#modal-create-attendance').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: '{{ trans("admin.successfully_added") }}',
                                text: response.message || '{{ trans("sw.attendance_created_successfully") }}',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ trans("sw.error") }}',
                                text: response.message || '{{ trans("sw.operation_failed") }}'
                            });
                        }
                        submitBtn.prop('disabled', false).html(originalText);
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(originalText);

                        if (xhr.status === 422) {
                            // Validation errors
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                var input = $('[name="' + field + '"]');
                                input.addClass('is-invalid');
                                input.siblings('.invalid-feedback').text(messages[0]).show();
                            });
                        } else {
                            var errorMessage = '{{ trans("sw.operation_failed") }}';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: '{{ trans("sw.error") }}',
                                text: errorMessage
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection

