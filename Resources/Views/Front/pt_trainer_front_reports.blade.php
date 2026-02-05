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
        <li class="breadcrumb-item text-muted">
            <a href="{{route('sw.listPTMember')}}" class="text-muted text-hover-primary">{{ trans('sw.pt_members') }}</a>
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
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="{{asset('resources/assets/new_front/global/plugins/fullcalendar/fullcalendar.min.css')}}" rel="stylesheet"/>
    <link href='{{asset('resources/assets/new_front/global/plugins/fullcalendar/fullcalendar.print.css')}}' rel='stylesheet' media='print' />
    <!-- END PAGE LEVEL STYLES -->
@endsection
@section('page_body')
    <div class="row">
        <!-- Filter Card -->
        <div class="col-12 mb-5">
            <div class="card card-flush">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">
                            <i class="ki-outline ki-filter fs-3 me-2"></i>
                            {{ trans('sw.filter') }}
                        </h3>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-icon btn-light-primary" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                            <i class="ki-outline ki-minus fs-2"></i>
                        </button>
                    </div>
                </div>
                <div class="collapse show" id="filterCollapse">
                    <div class="card-body pt-0">
                        <form id="form_filter" action="" method="get">
                            <div class="row g-4">
                                <!-- Quick Month Selector -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label fw-semibold">{{ trans('sw.quick_select') }}</label>
                                    <select id="month_selector" class="form-select" onchange="selectMonth(this.value)">
                                        <option value="">{{ trans('sw.select_month') }}...</option>
                                        @php
                                            $currentMonth = \Carbon\Carbon::now();
                                            for ($i = 0; $i < 12; $i++) {
                                                $month = $currentMonth->copy()->subMonths($i);
                                                $monthStart = $month->copy()->startOfMonth()->format('Y-m-d');
                                                $monthEnd = $month->copy()->endOfMonth()->format('Y-m-d');
                                                $isSelected = (request('from') == $monthStart && request('to') == $monthEnd);
                                        @endphp
                                        <option value="{{ $monthStart }}|{{ $monthEnd }}" @if($isSelected) selected @endif>
                                            {{ $month->translatedFormat('F Y') }}
                                        </option>
                                        @php } @endphp
                                    </select>
                                </div>

                                <!-- Date Range -->
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label fw-semibold">{{ trans('sw.date_range') }}</label>
                                    <div class="input-group date-picker input-daterange">
                                        <input type="text" class="form-control" name="from" id="filter_from"
                                               value="{{ request('from') }}" placeholder="{{ trans('sw.from') }}" autocomplete="off">
                                        <span class="input-group-text bg-light">
                                            <i class="ki-outline ki-arrow-right fs-4"></i>
                                        </span>
                                        <input type="text" class="form-control" name="to" id="filter_to"
                                               value="{{ request('to') }}" placeholder="{{ trans('sw.to') }}" autocomplete="off">
                                    </div>
                                </div>

                                <!-- Class Selector -->
                                <div class="col-lg-2 col-md-6">
                                    <label class="form-label fw-semibold">{{ trans('sw.pt_class') }}</label>
                                    <select name="pt_class_id" id="pt_class_id" class="form-select select2" data-placeholder="{{ trans('sw.all_classes') }}...">
                                        <option value="">{{ trans('sw.all_classes') }}...</option>
                                        @foreach($subscriptions as $subscription)
                                            <optgroup label="{{ $subscription->name }}">
                                                @foreach($subscription->pt_classes as $class)
                                                    <option value="{{ $class->id }}" @if(request('pt_class_id') == $class->id) selected @endif>{{ $class->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Trainer Selector -->
                                <div class="col-lg-2 col-md-6">
                                    <label class="form-label fw-semibold">{{ trans('sw.pt_trainer') }}</label>
                                    <select name="pt_trainer" class="form-select select2" data-placeholder="{{ trans('admin.choose') }}...">
                                        <option value="">{{ trans('admin.choose') }}...</option>
                                        @foreach($pt_trainers as $trainer)
                                            <option value="{{ $trainer->id }}" @if(request('pt_trainer') == $trainer->id) selected @endif>{{ $trainer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filter Button -->
                                <div class="col-lg-2 col-md-6 d-flex align-items-end">
                                    <button class="btn btn-primary w-100" id="filter" type="submit">
                                        <i class="ki-outline ki-filter fs-4"></i>
                                        <span class="d-none d-lg-inline ms-1">{{ trans('sw.filter') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="col-12 mb-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card bg-light-primary">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <span class="symbol-label bg-primary">
                                        <i class="ki-outline ki-calendar-8 fs-2x text-white"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-gray-800">{{ count($reservations) }}</div>
                                    <div class="fs-7 text-gray-500">{{ trans('sw.total_sessions') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light-success">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <span class="symbol-label bg-success">
                                        <i class="ki-outline ki-people fs-2x text-white"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-gray-800">{{ $pt_trainers->count() }}</div>
                                    <div class="fs-7 text-gray-500">{{ trans('sw.pt_trainers') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light-info">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <span class="symbol-label bg-info">
                                        <i class="ki-outline ki-abstract-26 fs-2x text-white"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-gray-800">{{ $classes->count() }}</div>
                                    <div class="fs-7 text-gray-500">{{ trans('sw.pt_classes') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
{{--        <div class="row">--}}

{{--            <div class="col-md-4 col-xs-6 mg-t-20 mg-lg-t-0">--}}
{{--                <div class="input-group">--}}

{{--                    <form class="d-flex w-100" action=""--}}
{{--                          method="get">--}}
{{--                        <div class="input-group ">--}}
{{--                            <input type="text" name="search" value="@php echo @strip_tags($_GET['search']) @endphp"--}}
{{--                                   class="form-control" placeholder="{{ trans('sw.search_on')}}">--}}
{{--                            <span class="input-group-btn ">--}}
{{--											<button class="btn blue  rounded-3" type="submit"><i--}}
{{--                                                        class="fa fa-search"></i></button>--}}
{{--											</span>--}}
{{--                        </div>--}}
{{--                        <span--}}
{{--                                class="input-group-btn "><i--}}
{{--                                    class="fa fa-search"></i></span>--}}

{{--                    </form>--}}
{{--                </div><!-- end search button-->--}}
{{--            </div><!-- end search div -->--}}

{{--            <div class="col-md-2 col-xs-3 mg-t-20 mg-lg-t-0">--}}
{{--                <div class="input-group-btn">--}}
{{--                    @if(in_array('createPTTrainer', (array)$swUser->permissions) || $swUser->is_super_user)--}}
{{--                        <a href="{{route('sw.createPTTrainer')}}" class="btn btn-primary btn-block rounded-3"--}}
{{--                           type="button"><i class="fa fa-plus mx-1"> </i> {{ trans('admin.add')}}</a>--}}
{{--                    @endif--}}
{{--                </div><!-- end add button -->--}}
{{--            </div><!-- end add div -->--}}
{{--            @if((count(array_intersect(@(array)$swUser->permissions, ['exportPTTrainerPDF', 'exportPTTrainerExcel'])) > 0) || $swUser->is_super_user)--}}

{{--                <div class="col-md-2  col-xs-3  mg-t-20 mg-lg-t-0">--}}

{{--                    <button class="btn btn-primary  btn-block dropdown-toggle  rounded-3" data-toggle="dropdown">--}}
{{--                        <i class="fa fa-download mx-1"></i>--}}
{{--                        {{ trans('sw.download')}}--}}
{{--                        <i class="fa fa-angle-down"></i>--}}
{{--                    </button>--}}
{{--                    <ul class="dropdown-menu pull-right">--}}
{{--                        @if(in_array('exportPTTrainerExcel', (array)$swUser->permissions) || $swUser->is_super_user)--}}
{{--                            <li>--}}
{{--                                <a href="{{route('sw.exportPTTrainerExcel')}}"><i--}}
{{--                                            class="fa fa-file-excel-o"></i> {{ trans('sw.excel_export')}} </a>--}}
{{--                            </li>--}}
{{--                        @endif--}}
{{--                        @if(in_array('exportPTTrainerPDF', (array)$swUser->permissions) || $swUser->is_super_user)--}}
{{--                            <li>--}}
{{--                                <a href="{{route('sw.exportPTTrainerPDF')}}"><i--}}
{{--                                            class="fa fa-file-pdf-o"></i> {{ trans('sw.pdf_export')}} </a>--}}
{{--                            </li>--}}
{{--                        @endif--}}
{{--                    </ul>--}}

{{--                </div><!-- end Export div -->--}}
{{--            @endif--}}
{{--        </div>--}}

        <!-- Calendar Section -->
        <div class="col-12">
            <div class="card card-flush">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">
                            <i class="ki-outline ki-calendar fs-3 me-2"></i>
                            {{ trans('sw.calender') }}
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar" class="has-toolbar"></div>
                </div>
            </div>
        </div>
        <!-- END Calendar Section -->

    </div>





    <!-- Members Modal -->
    <div class="modal fade" id="modalMembersTable" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="ki-outline ki-people fs-3 me-2 text-primary"></i>
                        {{ trans('sw.pt_members') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-4" id="cart_table">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start">#</th>
                                    <th>{{ trans('sw.name') }}</th>
                                    <th>{{ trans('sw.pt_class') }}</th>
                                    <th class="rounded-end">{{ trans('sw.status') }}</th>
                                </tr>
                            </thead>
                            <tbody id="cart_result" @if($lang == 'ar') class="text-end" @endif>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('admin.close') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Members Modal -->
@endsection

@section('scripts')
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    @parent


    <!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
{{--    <script src="{{asset('resources/assets/new_front/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js')}}" type="text/javascript"></script>--}}

    <!-- END CORE PLUGINS -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <!-- IMPORTANT! fullcalendar depends on jquery-ui-1.10.3.custom.min.js for drag & drop support -->
    <script src="{{asset('resources/assets/new_front/global/plugins/moment.min.js')}}"></script>
    <script src="{{asset('resources/assets/new_front/global/plugins/fullcalendar/fullcalendar.min.js')}}"></script>
    <!-- END PAGE LEVEL PLUGINS -->

    <script src='{{asset('resources/assets/new_front/global/plugins/fullcalendar/lang-all.js')}}'></script>
    <script>
        // Month selector function
        function selectMonth(value) {
            if (!value) return;
            const parts = value.split('|');
            if (parts.length === 2) {
                document.getElementById('filter_from').value = parts[0];
                document.getElementById('filter_to').value = parts[1];
            }
        }

        $(document).ready(function() {

            ComponentsPickers.init();

            var currentLangCode = '{{$lang == 'ar' ? 'ar-sa' : 'en'}}';
            var currentTimezone = 'UTC';

            // Initialize select2
            $('.select2').select2({
                allowClear: true,
                width: '100%'
            });

            function renderCalendar() {
                $('#calendar').fullCalendar({
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                    },
                    defaultDate: '{{\Carbon\Carbon::now()->toDateString()}}',
                    timezone: currentTimezone,
                    lang: currentLangCode,
                    buttonIcons: false,
                    weekNumbers: true,
                    editable: false,
                    eventLimit: true,
                    eventClick: function (arg) {
                        let url = "{{route('sw.listPTMemberInClassCalendar', ['pt_class_id' => '@@pt_class_id', 'pt_trainer_id' => '@@pt_trainer_id'])}}";
                        url = url.replace('@@pt_class_id', arg.pt_class_id ?? '');
                        url = url.replace('@@pt_trainer_id', arg.pt_trainer_id ?? '');
                        $.ajax({
                            url: url,
                            cache: false,
                            type: 'GET',
                            dataType: 'json',
                            data: {},
                            success: function (response_data) {
                                let result = '';
                                const today = new Date();
                                today.setHours(0, 0, 0, 0);

                                if(response_data.result && response_data.result.length > 0){
                                    for (let i = 0; i < response_data.result.length; i++){
                                        const item = response_data.result[i];
                                        const memberName = item.member && item.member.name ? item.member.name : '';
                                        const memberCode = item.member && item.member.code ? item.member.code : '';

                                        // Determine status based on dates
                                        let startDate = item.start_date || item.joining_date;
                                        let endDate = item.end_date || item.expire_date;
                                        let sessionsRemaining = item.sessions_remaining ?? item.remaining_sessions ?? 0;
                                        let isActive = false;

                                        if (startDate && endDate) {
                                            const start = new Date(startDate);
                                            const end = new Date(endDate);
                                            start.setHours(0, 0, 0, 0);
                                            end.setHours(0, 0, 0, 0);
                                            isActive = (today >= start && today <= end && sessionsRemaining > 0);
                                        } else if (sessionsRemaining > 0) {
                                            isActive = true;
                                        }

                                        const statusBadge = isActive
                                            ? '<span class="badge badge-light-success">{{ trans('sw.active') }}</span>'
                                            : '<span class="badge badge-light-danger">{{ trans('sw.expire') }}</span>';

                                        result += '<tr>';
                                        result += '<td class="ps-4">' + (i+1) + '</td>';
                                        result += '<td>';
                                        result += '<div class="d-flex align-items-center">';
                                        result += '<div class="symbol symbol-35px symbol-circle me-3">';
                                        result += '<span class="symbol-label bg-light-primary text-primary fw-bold">' + (memberName.charAt(0) || '?') + '</span>';
                                        result += '</div>';
                                        result += '<div class="d-flex flex-column">';
                                        result += '<span class="fw-bold">' + memberName + '</span>';
                                        result += '<span class="text-muted fs-7">' + memberCode + '</span>';
                                        result += '</div>';
                                        result += '</div>';
                                        result += '</td>';
                                        result += '<td>' + arg.title + '</td>';
                                        result += '<td>' + statusBadge + '</td>';
                                        result += '</tr>';
                                    }
                                } else {
                                    result = '<tr id="empty_cart"><td colspan="4" class="text-center py-10">';
                                    result += '<i class="ki-outline ki-information-5 fs-2x text-muted mb-3"></i>';
                                    result += '<div class="text-muted fs-6">{{ trans('sw.no_record_found') }}</div>';
                                    result += '</td></tr>';
                                }
                                $('#modalMembersTable').modal('show');
                                $('#cart_result').html(result);
                            },
                            error: function (request, error) {
                                swal("{{ trans('sw.operation_failed') }}", "{{ trans('sw.something_went_wrong') }}", "error");
                                console.error("Request: " + JSON.stringify(request));
                                console.error("Error: " + JSON.stringify(error));
                            }
                        });
                    },
                    events: [
                        @foreach($reservations as $reservation)
                        {
                            title: '{{$reservation['title']}}',
                            pt_class_id: '{{$reservation['pt_class_id']}}',
                            pt_trainer_id: '{{$reservation['pt_trainer_id']}}',
                            session_token: '{{$reservation['session_token'] ?? ''}}',
                            start: '{{$reservation['start']}}',
                            end: '{{$reservation['end']}}',
                            backgroundColor: '{{$reservation['background_color']}}',
                        },
                        @endforeach
                    ]
                });
            }

            renderCalendar();

        });
    </script>


@endsection


