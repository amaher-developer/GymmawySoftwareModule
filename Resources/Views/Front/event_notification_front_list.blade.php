@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home')}}</a>
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
{{--    <link href="../../assets/admin/pages/css/pricing-table-rtl.css" rel="stylesheet" type="text/css"/>--}}
    <link href="{{asset('resources/assets/new_front/pages/css/pricing-table-rtl.css')}}" rel="stylesheet" type="text/css" />
    <style>
        .form-section {
            margin: 30px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e7ecf1;
        }
        .through {
            text-decoration: line-through;
        }

        /* Responsive table styles */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive table {
            min-width: 900px;
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

<!--begin::Event Notifications-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-notification fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.event_notifications')}}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!-- No toolbar buttons for this simple list -->
            </div>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Search-->
        <div class="d-flex align-items-center position-relative my-1 mb-5">
            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
            <form class="d-flex" action="" method="get" style="max-width: 400px;">
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="{{ request('search') }}" placeholder="{{ trans('sw.search_on')}}">
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

        @if(count($event_notifications) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_event_notifications_table">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px text-nowrap">
                            <i class="ki-outline ki-notification fs-6 me-2"></i>{{ trans('sw.name')}}
                        </th>
                        <th class="min-w-300px text-nowrap">
                            <i class="ki-outline ki-message-text fs-6 me-2"></i>{{ trans('sw.message')}}
                        </th>
                        <th class="text-end min-w-150px text-nowrap actions-column">
                            <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                        </th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($event_notifications as $key=> $event_notification)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-50px me-3">
                                        <div class="symbol-label fs-3 bg-light-primary text-primary">
                                            <i class="ki-outline ki-notification fs-2"></i>
                                        </div>
                                    </div>
                                    <!--end::Avatar-->
                                    <div>
                                        <!--begin::Title-->
                                        <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            {{ $event_notification->title }}
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-gray-700 fs-7" id="message_display_{{$event_notification->id}}" style="white-space: pre-wrap; max-width: 400px;">{{ $event_notification->message }}</div>
                            </td>
                            <td class="text-end actions-column">
                                <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                    <!--begin::Edit Button-->
                                    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                            onclick="openEditModal({{$event_notification->id}}, '{{ addslashes($event_notification->title) }}')"
                                            title="{{ trans('sw.edit_message')}}">
                                        <i class="ki-outline ki-pencil fs-2"></i>
                                    </button>
                                    <!--end::Edit Button-->
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input type="checkbox" value="1" onchange="event_notification({{$event_notification->id}})"
                                               id="event_notification_status_{{$event_notification->id}}"
                                               @if($event_notification->status == true) checked @endif
                                               class="form-check-input" />
                                        <label class="form-check-label fw-semibold text-muted" for="event_notification_status_{{$event_notification->id}}">
                                            {{ $event_notification->status ? trans('sw.on') : trans('sw.off') }}
                                        </label>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <!--end::Table-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                        <i class="ki-outline ki-notification fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Event Notifications-->

<!--begin::Edit Message Modal-->
<div class="modal fade" id="editMessageModal" tabindex="-1" aria-labelledby="editMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMessageModalLabel">{{ trans('sw.edit_message')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_notification_id">
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ trans('sw.name')}}</label>
                    <div class="form-control-plaintext fw-bold" id="edit_notification_title"></div>
                </div>
                <div class="mb-3">
                    <label for="edit_notification_message" class="form-label fw-semibold">{{ trans('sw.message')}}</label>
                    <textarea class="form-control" id="edit_notification_message" rows="6" dir="auto"></textarea>
                </div>
                <!--begin::Dynamic Variables Hint-->
                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4 mt-4">
                    <i class="ki-outline ki-information-4 fs-2tx text-primary me-4"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h6 class="text-gray-900 fw-bold">{{ trans('sw.dynamic_variables')}}</h6>
                            <div class="fs-7 text-gray-700" style="direction: ltr;">
                                <code class="me-2">#member_name</code>
                                <code class="me-2">#member_code</code>
                                <code class="me-2">#member_phone</code><br class="d-block mt-1">
                                <code class="me-2">#membership_name</code>
                                <code class="me-2">#membership_start_date</code>
                                <code class="me-2">#membership_expire_date</code><br class="d-block mt-1">
                                <code class="me-2">#membership_amount_paid</code>
                                <code class="me-2">#membership_amount_remaining</code>
                                <code class="me-2">#freeze_start_date</code>
                                <code class="me-2">#freeze_end_date</code>
                                <code class="me-2">#membership_resume_date</code>
                                <code class="me-2">#days_remaining</code><br class="d-block mt-1">
                                <code class="me-2">#setting_name</code>
                                <code class="me-2">#setting_phone</code>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Dynamic Variables Hint-->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('admin.cancel')}}</button>
                <button type="button" class="btn btn-primary" onclick="saveMessage()">
                    <i class="ki-outline ki-check fs-6 me-1"></i>
                    {{ trans('sw.save')}}
                </button>
            </div>
        </div>
    </div>
</div>
<!--end::Edit Message Modal-->

@endsection

@section('scripts')
    @parent
    <script>
    function event_notification(id){
        var status = 0;
        if ($('#event_notification_status_'+id).is(':checked')) {
            status = 1;
        }
        var url = '{{route('sw.editEventNotificationAjax', [':id', ':status'])}}';
        url = url.replace(':id', id);
        url = url.replace(':status', status);
        $.ajax({
            url: url,
            type: "get",
            success: (data) => {
                // console.log(data);
                swal("{{ trans('admin.done')}}", "{{ trans('admin.successfully_processed')}}", "success");
            },
            error: (reject) => {
                var response = $.parseJSON(reject.responseText);
                console.log(response);
            }
        });
        return false;
    }

    function openEditModal(id, title) {
        $('#edit_notification_id').val(id);
        $('#edit_notification_title').text(title);
        var message = $('#message_display_' + id).text().trim();
        $('#edit_notification_message').val(message);
        var modal = new bootstrap.Modal(document.getElementById('editMessageModal'));
        modal.show();
    }

    function saveMessage() {
        var id = $('#edit_notification_id').val();
        var message = $('#edit_notification_message').val();

        if (!message.trim()) {
            swal("{{ trans('admin.error')}}", "{{ trans('sw.message_required')}}", "error");
            return;
        }

        $.ajax({
            url: '{{ route('sw.updateEventNotificationMessage') }}',
            type: 'POST',
            data: {
                id: id,
                message: message,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#message_display_' + id).text(message);
                    bootstrap.Modal.getInstance(document.getElementById('editMessageModal')).hide();
                    swal("{{ trans('admin.done')}}", "{{ trans('admin.successfully_edited')}}", "success");
                } else {
                    swal("{{ trans('admin.error')}}", response.message, "error");
                }
            },
            error: function(reject) {
                swal("{{ trans('admin.error')}}", "{{ trans('admin.something_went_wrong')}}", "error");
                console.log(reject);
            }
        });
    }
    </script>

@endsection


