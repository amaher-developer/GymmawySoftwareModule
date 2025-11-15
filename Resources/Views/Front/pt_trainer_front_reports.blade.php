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
    <link href="{{asset('resources/assets/admin/global/plugins/fullcalendar/fullcalendar.min.css')}}" rel="stylesheet"/>
    <link href='{{asset('resources/assets/admin/global/plugins/fullcalendar/fullcalendar.print.css')}}' rel='stylesheet' media='print' />
    <!-- END PAGE LEVEL STYLES -->
@endsection
@section('page_body')
    <div class="row">

        <div class="row " style="padding-bottom: 15px;">
            <form id="form_filter"
                  action=""
                  method="get">

                <div class="col-lg-3 col-md-3 col-xs-9 mg-t-20 mg-lg-t-0">

                    <div class="input-group  date-picker input-daterange" data-date="10/11/2012"
                         data-date-format="mm/dd/yyyy">
                        <input type="text" class="form-control" name="from"
                               value="@php echo @strip_tags($_GET['from']) @endphp" autocomplete="off">
                        <span class="input-group-addon">
												 {{ trans('sw.to')}} </span>
                        <input type="text" class="form-control" name="to"
                               value="@php echo @strip_tags($_GET['to']) @endphp" autocomplete="off">
                    </div>
                    <!-- /input-group -->

                </div><!-- end filter div -->
                    <!-- /input-group -->

                <div class="col-lg-2 col-md-2 col-xs-2 mg-t-20 mg-lg-t-0" style="padding-bottom: 5px">
                    <select name="pt_class_id" id="pt_class_id" class="form-control select2">
                        <option value="">{{ trans('sw.all_classes')}}...</option>
                        @foreach($subscriptions as $subscription)
                            <optgroup label="{{$subscription->name}}">{{$subscription->name}}</optgroup>
                            @foreach($subscription->pt_classes as $class)
                                <option value="{{$class->id}}" @if(request('pt_class_id') == $class->id) selected="" @endif >{{$class->name}}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>


                <div class="col-lg-2 col-md-2 col-xs-2 mg-t-20 mg-lg-t-0">
                    <select name="pt_trainer" class="form-control select2">
                        <option value="">{{ trans('admin.choose')}}...</option>
                        @foreach($pt_trainers as $trainer)
                            <option value="{{$trainer->id}}" @if(request('pt_trainer') == $trainer->id) selected="" @endif>{{$trainer->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-2 col-xs-3">
                    <button class="btn btn-primary  rounded-3 btn-block" id="filter" type="submit"><i
                            class="fa fa-filter mx-1"></i> {{ trans('sw.filter')}}</button>
                </div>

            </form>
        </div>
        <div style="clear: none"></div>
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

        <div style="clear: none;padding-bottom: 15px"></div>

        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped table-bordered table-hover">
                    <tbody>
                    <tr>
                        <th>{{ trans('admin.total_count')}}</th>
                        <td>{{ count($reservations) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
                <div class="portlet box green-meadow calendar">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i> {{ trans('sw.calender')}}
                        </div>
                    </div>
                    <div><br/><br/></div>
                    <div class="portlet-body">
                        <div class="row">
                            {{--                            <div class="col-md-3 col-sm-12">--}}
                            {{--                                <!-- BEGIN DRAGGABLE EVENTS PORTLET-->--}}
                            {{--                                <h3 class="event-form-title">Draggable Events</h3>--}}
                            {{--                                <div id="external-events">--}}
                            {{--                                    <form class="inline-form">--}}
                            {{--                                        <input type="text" value="" class="form-control" placeholder="Event Title..." id="event_title"/><br/>--}}
                            {{--                                        <a href="javascript:;" id="event_add" class="btn default">--}}
                            {{--                                            Add Event </a>--}}
                            {{--                                    </form>--}}
                            {{--                                    <hr/>--}}
                            {{--                                    <div id="event_box">--}}
                            {{--                                    </div>--}}
                            {{--                                    <label for="drop-remove">--}}
                            {{--                                        <input type="checkbox" id="drop-remove"/>remove after drop </label>--}}
                            {{--                                    <hr class="visible-xs"/>--}}
                            {{--                                </div>--}}
                            {{--                                <!-- END DRAGGABLE EVENTS PORTLET-->--}}
                            {{--                            </div>--}}
                            <div class="col-md-12 col-sm-12">
                                <div id="calendar" class="has-toolbar">
                                </div>
                            </div>
                        </div>
                        <!-- END CALENDAR PORTLET-->
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->

    </div>





    <!-- start model pay -->
    <div class="modal" id="modalMembersTable">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.pt_members')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="portlet grey-cascade box">
                                <div class="portlet-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered table-striped" id="cart_table">
                                            <thead>
                                            <tr>
                                                <th>
                                                    #
                                                </th>
                                                <th>
                                                    {{ trans('sw.name')}}
                                                </th>
                                                <th>
                                                    {{ trans('sw.pt_class')}}
                                                </th>

                                                {{--                                                <th>--}}
                                                {{--                                                    {{ trans('sw.status')}}--}}
                                                {{--                                                </th>--}}
                                            </tr>
                                            </thead>
                                            <tbody id="cart_result" @if($lang == 'ar') class="text-right" @endif>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- End model pay -->
@endsection

@section('scripts')
    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    @parent


    <!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js')}}" type="text/javascript"></script>--}}

    <!-- END CORE PLUGINS -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <!-- IMPORTANT! fullcalendar depends on jquery-ui-1.10.3.custom.min.js for drag & drop support -->
    <script src="{{asset('resources/assets/admin/global/plugins/moment.min.js')}}"></script>
    <script src="{{asset('resources/assets/admin/global/plugins/fullcalendar/fullcalendar.min.js')}}"></script>
    <!-- END PAGE LEVEL PLUGINS -->

    <script src='{{asset('resources/assets/admin/global/plugins/fullcalendar/lang-all.js')}}'></script>
    <script>

        $(document).ready(function() {

            ComponentsPickers.init();

            var currentLangCode = '{{$lang == 'ar' ? 'ar-sa' : 'en'}}';
            var currentTimezone = 'UTC';



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
                    buttonIcons: false, // show the prev/next text
                    weekNumbers: true,
                    editable: false,
                    eventLimit: true, // allow "more" link when too many events
                    // Delete event
                    eventClick: function (arg) {
                        let url  = "{{route('sw.listPTMemberInClassCalendar', ['pt_class_id' => '@@pt_class_id', 'pt_trainer_id' => '@@pt_trainer_id'])}}";
                        url = url.replace('@@pt_class_id', arg.pt_class_id ?? '');
                        url = url.replace('@@pt_trainer_id', arg.pt_trainer_id ?? '');
                        $.ajax({
                            url: url,
                            cache: false,
                            type: 'GET',
                            dataType: 'text',
                            data: {},
                            success: function (response) {
                                // $('#trainer_confirm').modal('toggle');
                                // $('#tr_trainer_member_'+id).remove();
                                let result = '';
                                let date_status = '';
                                let response_data = $.parseJSON(response);
                                if(response_data.result && response_data.result.length > 0){
                                    for (let i = 0; i < response_data.result.length; i++){
                                        var item = response_data.result[i];
                                        var memberName = item.member && item.member.name ? item.member.name : '';
                                        result+= '<tr>';
                                        result+= '<td>' + (i+1) + '</td>';
                                        result+= '<td><i class="fa fa-user text-muted"></i> ' + memberName + '</td>';
                                        result+= '<td>' + arg.title + '</td>';
                                        result+= '</tr>';
                                    }
                                }else{
                                    result = '<tr id="empty_cart"><td colspan="3" class="text-center">{{ trans('sw.no_record_found')}}</td></tr>';

                                }
                                $('#modalMembersTable').modal('show');
                                $('#cart_result').html(result);
                            },
                            error: function (request, error) {
                                swal("Operation failed", "Something went wrong.", "error");
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

                        // {
                        //     title: 'Click for Google',
                        //     url: 'http://google.com/',
                        //     start: '2024-03-05',
                        //     backgroundColor: Metronic.getBrandColor('red'),
                        //     allDay: false,
                        // }
                    ]
                });
            }

            renderCalendar();

        });

        $('#subscription').change(function (){
            $("#class_id option").hide();
            $("#class_id  option[value=all" + "]").show();
            var get_classes = $(this).find(":selected").attr('classes');
            var classes = get_classes.split(",");

            for (let i = 0; i < classes.length; i++ ){
                $("#class_id option[value=" + classes[i] + "]").show();
            }

        });
        function pt_subscription(){
            $("#class_id option").hide();
            $("#class_id  option[value=all" + "]").show();
            var get_classes = $('#subscription').find(":selected").attr('classes');
            var classes = get_classes.split(",");

            for (let i = 0; i < classes.length; i++ ){
                $("#class_id option[value=" + classes[i] + "]").show();
            }

        }
        pt_subscription();
    </script>


@endsection
