@extends('software::Web.master')
@section('styles')
    <link href="{{asset('resources/assets/new_front/global/plugins/fullcalendar/fullcalendar.min.css')}}" rel="stylesheet"/>
    <link href='{{asset('resources/assets/new_front/global/plugins/fullcalendar/fullcalendar.print.css')}}' rel='stylesheet' media='print' />
    <!-- END PAGE LEVEL STYLES -->
    <style>
        .fc-title{
            color: #ffffff;
        }
    </style>
@endsection
@section('content')
    <div class="main">
        <div class="container">
            <ul class="breadcrumb">
                <li><a href="{{route('sw.showCustomerProfile')}}">{{trans('admin.home')}}</a></li>
                <li class="active"><a href="#">{{$title}}</a></li>
            </ul>
            <!-- BEGIN SIDEBAR & CONTENT -->
            <div class="row margin-bottom-40">
                <!-- BEGIN CONTENT -->
                <div class="col-md-12 col-sm-12">
                    <div class="content-page">
                        <div class="row margin-bottom-30">
                            <!-- BEGIN CAROUSEL -->
                            <div class="col-md-3 ">
                                @include('software::Web.__side_menu')
                            </div>
                            <!-- END CAROUSEL -->

                            <!-- BEGIN LEFT SIDEBAR -->
                            <div class="col-md-9 col-sm-9 blog-posts">



                                <!-- BEGIN PAGE CONTENT-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="portlet box green-meadow calendar">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-gift"></i> {{trans('sw.calender')}}
                                                </div>
                                            </div>
                                            <div><br/><br/></div>
                                            <div class="portlet-body">
                                                <div class="row">
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
                            <!-- END LEFT SIDEBAR -->



                        </div>


                    </div>
                </div>
                <!-- END CONTENT -->
            </div>
            <!-- BEGIN SIDEBAR & CONTENT -->
        </div>
    </div>




@stop

@section('scripts')
    <!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
    <script src="{{asset('resources/assets/new_front/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js')}}" type="text/javascript"></script>

    <!-- END CORE PLUGINS -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <!-- IMPORTANT! fullcalendar depends on jquery-ui-1.10.3.custom.min.js for drag & drop support -->
    <script src="{{asset('resources/assets/new_front/global/plugins/moment.min.js')}}"></script>
    <script src="{{asset('resources/assets/new_front/global/plugins/fullcalendar/fullcalendar.min.js')}}"></script>
    <!-- END PAGE LEVEL PLUGINS -->

    <script src='{{asset('resources/assets/new_front/global/plugins/fullcalendar/lang-all.js')}}'></script>
    <script>

        $(document).ready(function() {
            var currentLangCode = '{{($lang ?? 'ar') == 'ar' ? 'ar-sa' : 'en'}}';
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





                    },
                    events: [
                            @foreach($pts as $reservation)
                        {
                            title: '{{$reservation->pt_class->name ?? $reservation->pt_class->pt_subscription->name}}',
                            pt_class_id: '{{$reservation->pt_class_id}}',
                            pt_trainer_id: '{{$reservation->pt_trainer_id}}',
                            start: '{{\Carbon\Carbon::parse($reservation->joining_date)->toDateString()}}',
                            end: '{{\Carbon\Carbon::parse($reservation->expire_date)->toDateString()}}',
                            backgroundColor: '{{$reservation->pt_class->background_color}}',
                        },
                        @endforeach

                        {
                            title: 'Click for Google',
                            url: 'http://google.com/',
                            start: '2024-03-05',
                            backgroundColor: Metronic.getBrandColor('red'),
                            allDay: false,
                        }
                    ]
                });
            }

            renderCalendar();
        });

    </script>
    <!-- END PAGE LEVEL SCRIPTS -->

@endsection


