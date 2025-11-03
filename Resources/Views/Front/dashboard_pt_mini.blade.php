@extends('software::layouts.list')
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
@section('list_title') {{ @$title }} @endsection
@section('styles')
<style>
    .normal_search {
        height: 60px;
    }
    .scan_qrcode_manual {
        height: 60px;
        font-size: 28px;
        line-height: 60px;
    }
    .short-btn {
        min-width: 140px;
        height: 120px;
    }
    .short-btn i{
        line-height: 40px !important;
    }
    .number{
        font-size: 20px !important;
    }
    .details{
        width: 60%;
    }






    #my-qr-reader {
        padding: 20px !important;
        border: 1.5px solid #b2b2b2 !important;
        border-radius: 8px;
    }

    #my-qr-reader img[alt="Info icon"] {
        display: none;
    }

    #my-qr-reader img[alt="Camera based scan"] {
        width: 100px !important;
        height: 100px !important;
    }

    #html5-qrcode-anchor-scan-type-change {
        text-decoration: none !important;
        color: #1d9bf0;
    }

    video {
        width: 100% !important;
        border: 1px solid #b2b2b2 !important;
        border-radius: 0.25em;
    }
</style>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="{{asset('resources/assets/admin/global/plugins/fullcalendar/fullcalendar.min.css')}}" rel="stylesheet"/>
<link href='{{asset('resources/assets/admin/global/plugins/fullcalendar/fullcalendar.print.css')}}' rel='stylesheet' media='print' />
<!-- END PAGE LEVEL STYLES -->
@endsection
@section('page_body')

    @if(\Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString() <= \Carbon\Carbon::now()->toDateString())
        <div class="Metronic-alerts alert alert-danger fade in"><i class="fa-lg fa fa-warning"></i>  {!! trans('sw.subscription_expire_date_msg', ['date'=> \Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString(), 'url' => route('sw.listSwPayment')]) !!}</div>
    @endif

    <!--begin::Dashboard-->
    <div class="row g-5">
        <!--begin::Member Check-in-->
        <div class="col-lg-8">
            <div class="card card-flush h-100">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold">
                            <a onclick="qrcam();" style="cursor: pointer;" data-target="#modalQRCam" data-toggle="modal" title="{{ trans('sw.scan_qr_by_camera')}}" class="btn btn-sm btn-light-primary me-3">
                                <i class="ki-outline ki-scan-barcode"></i>
                            </a>
                            {{ trans('sw.qrcode')}}
                        </h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Input group-->
                    <div class="mb-10">
                        <label class="form-label">{{ trans('sw.check_in_by_id')}}</label>
                        <div class="input-group">
                            <input type="text" class="form-control scan_qrcode_manual" placeholder="{{ trans('sw.check_in_by_id')}}" name="scan_qrcode_manual" id="scan_qrcode_manual">
                            <button class="btn btn-primary normal_search" id="Normal_search" onclick="scanQRcodeManual();" type="button">
                                <i class="ki-outline ki-barcode fs-1"></i>
                            </button>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Checkbox-->
                    <div class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="1" id="scan_qrcode_enquiry">
                        <label class="form-check-label" for="scan_qrcode_enquiry">
                            {{ trans('sw.enquiry_only')}}
                        </label>
                    </div>
                    <!--end::Checkbox-->
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Member Check-in-->

        <!--begin::Last Enter Member-->
        <div class="col-lg-4">
            <div class="card card-flush h-100">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold">{{ trans('sw.last_enter_member')}}</h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50px me-5">
                            <div class="symbol-label bg-light-primary">
                                <i class="ki-outline ki-user fs-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.last_enter_member')}}</span>
                            <span class="fs-4 fw-bold text-primary" id="barcode_last_enter_member">
                                {{@$last_enter_member->member->name}}
                            </span>
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Last Enter Member-->
    </div>
    <!--end::Dashboard-->



        <div><br></div>

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



    <!-- Modal PT Attends with modern styling -->
    <div class="modal fade effect-newspaper" id="modalPTAttends" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('sw.pt_member_attendance_status') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Status Banner -->
                    <div id="status_banner_pt" class="text-center p-4 mb-4 text-white bg-success rounded">
                        <h3 id="p_messages" class="mb-0"></h3>
                    </div>

                    <div class="text-center mb-5">
                        <img id="client_img" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;" src="{{asset('uploads/settings/default.jpg')}}">
                        <h3 id="client_name" class="mt-3 mb-0"></h3>
                        <p class="text-muted" id="client_code"></p>
                    </div>

                    <!-- Key Stats -->
                    <div class="row g-2 text-center mb-5">
                        <div class="col-4">
                            <div class="bg-light-primary p-3 rounded">
                                <div class="fs-7 text-muted">{{ trans('sw.membership') }}</div>
                                <div class="fs-6 fw-bold" id="client_membership"></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light-danger p-3 rounded">
                                <div class="fs-7 text-muted">{{ trans('sw.amount_remaining') }}</div>
                                <div class="fs-6 fw-bold" id="client_amount_remaining"></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light-info p-3 rounded">
                                <div class="fs-7 text-muted">{{ trans('sw.expire_date') }}</div>
                                <div class="fs-6 fw-bold" id="client_expire_date"></div>
                            </div>
                        </div>
                    </div>

                    <div id="myData">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{trans('sw.phone')}}:</span>
                                    <span id="client_phone" class="text-muted"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{trans('sw.remaining_classes')}}:</span>
                                    <span id="client_workouts" class="text-muted"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{trans('sw.pt_classes')}}:</span>
                                    <span id="client_classes" class="text-muted"></span>
                                </div>
                            </div>
                            @if(@$mainSettings->active_loyalty)
                            <div class="col-md-6">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">
                                        <i class="ki-outline ki-gift text-primary me-1"></i>
                                        {{trans('sw.loyalty_points')}}:
                                    </span>
                                    <span id="client_loyalty_points_pt" class="text-primary fw-bold fs-4">0</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-center gap-2">
                    @if(in_array('memberSubscriptionRenewStore', (array)$swUser->permissions) || $swUser->is_super_user)
                    <div id="div_renew"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Modal PT Attends with effects-->





    <!-- start model pay -->
    <div class="modal" id="modalQRCam">
        <div class="modal-dialog  " role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.qrcode')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h1>Scan QR Codes</h1>
                    <div class="container col-md-12">

                        <div class="section">
                            <div id="my-qr-reader">
                            </div>
                        </div>
                        <div class="clearfix"><br/></div>
                    </div>

                </div>
                <div class="clearfix"><br/></div>
            </div>
            <div class="clearfix"><br/><br/></div>
        </div>
    </div>

    <!-- End model pay -->






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

    <!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
    <script src="{{asset('resources/assets/admin/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js')}}" type="text/javascript"></script>

    <!-- END CORE PLUGINS -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <!-- IMPORTANT! fullcalendar depends on jquery-ui-1.10.3.custom.min.js for drag & drop support -->
    <script src="{{asset('resources/assets/admin/global/plugins/moment.min.js')}}"></script>
    <script src="{{asset('resources/assets/admin/global/plugins/fullcalendar/fullcalendar.min.js')}}"></script>
    <!-- END PAGE LEVEL PLUGINS -->

    <script src='{{asset('resources/assets/admin/global/plugins/fullcalendar/lang-all.js')}}'></script>
    <script>

        $(document).ready(function() {
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
                        url = url.replace('@@pt_class_id', arg.pt_class_id);
                        url = url.replace('@@pt_trainer_id', arg.pt_trainer_id);
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
                                if(response_data.result.length > 0){
                                    for (let i = 0; i < response_data.result.length; i++){
                                        result+= '<tr>';
                                        result+= '<td>' + (i+1) + '</td>';
                                        result+= '<td><i class="fa fa-user text-muted"></i> ' + (response_data.result[i].member.name || '' )+ '</td>';
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

    </script>
    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>

    function scanQRcodeManual() {
        let value = $('#scan_qrcode_manual').val();
        qrcode_scanner(value);
    }

    function qrcode_scanner(value) {
        // if (value.length < 4)
        //     return;
        if(value < 0)
            return;
        var mycode = value;
        var enquiry = $("#scan_qrcode_enquiry").is( ":checked" ) ? 1 : 0;
        // console.log('code', mycode);
        $.ajax({
            url: pt_member_attendees_url,
            type: "get",
            data: {
                code: mycode,
                enquiry: enquiry
            }, beforeSend: function () {
                // $("#global-loader").show();
            }, success: (data) => {
                // $("#global-loader").hide();
                var data = data;

                $('#modalPTAttends').modal('show');
                load_new_posts();

                if(data.member) {
                    $('#myData').show();

                    $('#client_name').text(data.member.member.name);
                    $('#barcode_last_enter_member').text(data.member.member.name);
                    $('#client_address').text(data.member.member.address);
                    $('#client_phone').text(data.member.member.phone);
                    $('#client_code').text(data.member.member.code);
                    $('#client_img').attr('src',  data.member.member.image);
                    $('#client_amount_remaining').text(data.member.amount_remaining);
                    $('#client_total_amount_remaining').text(data.member.total_amount_remaining);
                    
                    // Display loyalty points if available
                    if (data.member.member && data.member.member.loyalty_points_formatted !== undefined) {
                        $('#client_loyalty_points_pt').text(data.member.member.loyalty_points_formatted);
                    } else {
                        $('#client_loyalty_points_pt').text('0');
                    }
                    
                    // var partsDate = data.member.member_subscriptions.expire_date.split('T');
                    $('#client_expire_date').text(data.member.expire_date);
                    $('#client_workouts').text(data.member.remain_workouts);
                    $('#client_classes').text(data.member.classes);
                    let subscription_name = '';
                    if(data.member.pt_subscription){ subscription_name =  data.member.pt_subscription?.name; }else{  subscription_name = trans_old_membership; }
                    $('#client_membership').text(subscription_name);

                    if(data.status === true){
                        $('#p_messages').html(data.msg || '<i class="ki-outline ki-check-circle fs-2x"></i>');
                        $('#status_banner_pt').removeClass('bg-danger').addClass('bg-success');
                        if(data.renew_status === true)
                            $('#div_renew').html('<a class="btn btn-primary text-white" id="' + data.member.id + '" membership_id="' + data.member.member_subscription_info.id + '">'+ trans_renew_membership +'</a>');
                        else
                            $('#div_renew').html('');
                    }else{
                        $('#p_messages').html('<i class="ki-outline ki-cross-circle fs-2x me-2"></i>' + data.msg);
                        $('#status_banner_pt').removeClass('bg-success').addClass('bg-danger');
                        if(data.renew_status === true)
                            $('#div_renew').html('<a class="btn btn-primary text-white" id="' + data.member.id + '" membership_id="' + data.member.member_subscription_info.id + '">'+ trans_renew_membership +'</a>');
                        else
                            $('#div_renew').html('');
                    }

                    if(data.member.pt_members && (data.member.pt_members.length > 0)){
                        let pt_members = '';
                        for (let i = 0; data.member.pt_members.length > i; i++){
                            pt_members = pt_members + '<a id="pt_membership_'+ data.member.pt_members[i].id +'" class="tag pt_membership_a tx-15" style="margin: 5px;" onclick="pt_membership('+ data.member.pt_members[i].id +')">' + data.member.pt_members[i].pt_subscription.name +' ('+ data.member.pt_members[i].visits + ' / ' + data.member.pt_members[i].classes +') ' +'</a>';
                        }
                        $('#client_pt_membership_h5').show();
                        $('#client_pt_membership').html(pt_members);
                    }else{
                        $('#client_pt_membership_h5').hide();
                    }
                    // console.log('data.member.gym_reservations', data.member.gym_reservations);
                    if(data.member.gym_reservations && (data.member.gym_reservations.length > 0)){
                        let gym_reservations = '';
                        for (let i = 0; data.member.gym_reservations.length > i; i++){
                            gym_reservations += '<span  class="tag pt_membership_a tx-15" style="margin: 5px;background-color: #ffc107 !important;" >' + data.member.gym_reservations[i].time_slot  +'</span>';
                        }
                        $('#client_reservation_h5').show();
                        $('#client_reservation').html(gym_reservations);
                    }else{
                        $('#client_reservation_h5').hide();
                        $('#client_reservation').html('');
                    }



                } else {
                    $('#myData').hide();
                    $('#div_renew').html('');
                    $('#icon_model').html(' <i class="fa fa-times mg-b-20 tx-50 text-danger"></i>');
                    $('#p_messages').text(data.msg);
                    $('#client_img').attr('src',  default_avatar_image);
                    $('.client_img').css("color", "#f44336c9");
                }
                $('#scan_qrcode_manual').val('');


            },
            error: (reject) => {

                var response = $.parseJSON(reject.responseText);
                console.log(response);

            }


        });
    }




    // cam scanner
    function domReady(fn) {
        if (
            document.readyState === "complete" ||
            document.readyState === "interactive"
        ) {
            setTimeout(fn, 1000);
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }
    function qrcam() {
        domReady(function () {
            // If found you qr code
            var i = 1;
            function onScanSuccess(decodeText, decodeResult) {
                 if(decodeText) {
                     if(i == 1){
                         $("[id=html5-qrcode-button-camera-stop]").click();
                         $('.modal-backdrop').hide();
                         qrcode_scanner(decodeText);
                         $('#modalQRCam').hide();
                     }
                     // console.log(i);
                     i = i+1;
                     // setTimeout(async () => {
                     //    qrcode_scanner(decodeText);
                     // }, 1000);

                 }
            }

            let htmlscanner = new Html5QrcodeScanner(
                "my-qr-reader",
                {fps: 10, qrbos: 250},
            );


            htmlscanner.render(onScanSuccess);


        });
    }

    $('#modalQRCam').on('hidden.bs.modal', function () {
        $("[id=html5-qrcode-button-camera-stop]").click();
    })

    // setTimeout(async () => {
    //     document.getElementById("html5-qrcode-button-camera-stop").click();
    // }, 2000);

  </script>

@endsection
