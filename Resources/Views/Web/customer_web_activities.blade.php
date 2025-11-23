@extends('software::Web.master')
@section('styles')

    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/new_front/global/plugins/pick-hours-availability-calendar/mark-your-calendar.css')}}">

    <link rel="stylesheet" type="text/css"
          href="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css"/>

    <style id="jsbin-css">
        .invoice-block {
            text-align: center;
        }
        @media (min-width: 768px) {
            .modal-xl {
                width: 90%;
                max-width:1200px;
            }
        }
        .btn-lg {
            font-size: 16px;
            padding: 10px;
        }
    </style>
@endsection

@section('content')

    <div class="main">
        <div class="container">
            <ul class="breadcrumb">
                <li><a href="{{route('sw.customerSubscriptions')}}">{{trans('admin.home')}}</a></li>
                <li class="active"><a href="#">{{$title}}</a></li>
            </ul>
            <!-- BEGIN SIDEBAR & CONTENT -->
            <div class="row margin-bottom-40">
    <!-- BEGIN CONTENT -->
    <div class="col-md-12 col-sm-12">
{{--        <h1>{{trans('sw.memberships')}}</h1>--}}
        <div class="content-page">
            <div class="row">


                <!-- BEGIN CAROUSEL -->
                <div class="col-md-3 ">
                    @include('software::Web.__side_menu')
                </div>
                <!-- END CAROUSEL -->
                @if(@count($subscriptions) > 0)
                <!-- BEGIN LEFT SIDEBAR -->
                <div class="col-md-9 col-sm-9 blog-posts">
                    @foreach($subscriptions as $subscription)
                    <div class="row">

                        <div class="col-md-8 col-sm-8">
                            <h2>{{trans('sw.activities')}}</h2>
{{--                            <p>Lorem ipsum dolor sit amet, dolore eiusmod quis tempor incididunt ut et dolore Ut veniam unde nostrudlaboris. Sed unde omnis iste natus error sit voluptatem.</p>--}}
                            <br>
                            @if(@count($member->member_subscription_info->activities) > 0)

                            <div class="row front-lists-v2 margin-bottom-15">
                                <div class="col-md-12">
                                    <ul class="list-unstyled">
                                       @php if(@count($member->member_subscription_info->activities) > 0){echo @implode(' ', array_map(function ($name) use ( $member, ($lang ?? 'ar')){ if(@$name['activity']['id']) { static $i = 0;   return '<li><button class="btn btn-lg btn-'.(@$name['training_times'] > @$name['visits'] ? 'primary' : 'gray').' btn-xs rounded-3" id="activity_'.@$member->id.'_'.@$name['activity']['id'].'" onclick="non_membership_reservation('.@$member->id.', '.@$name['activity']['id'].')"  data-target="#modalReservation" data-toggle="modal" >'.$name['activity']['name_'.($lang ?? 'ar')]. ' ( '.@(int)$name['training_times'].' ) ' .'</button></li>';  $i++; } }, @$member->member_subscription_info->activities ?? [])); } @endphp
                                    </ul>
                                </div>
                            </div>
                            @else
                            <!-- BEGIN LEFT SIDEBAR -->
                                <div class="row front-lists-v2 margin-bottom-15">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="content-page page-404">
                                            <div class="details">
                                                <h3>{{trans('sw.no_record_found')}}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END LEFT SIDEBAR -->
                            @endif
                        </div>

                    </div>
                    <hr class="blog-post-sep">
                    @endforeach

{{--                    <ul class="pagination">--}}
{{--                        <li><a href="#">Prev</a></li>--}}
{{--                        <li><a href="#">1</a></li>--}}
{{--                        <li><a href="#">2</a></li>--}}
{{--                        <li class="active"><a href="#">3</a></li>--}}
{{--                        <li><a href="#">4</a></li>--}}
{{--                        <li><a href="#">5</a></li>--}}
{{--                        <li><a href="#">Next</a></li>--}}
{{--                    </ul>--}}
                </div>
                <!-- END LEFT SIDEBAR -->
                @else
                    <!-- BEGIN LEFT SIDEBAR -->
                        <div class="col-md-9 col-sm-9 blog-posts">
                            <div class="col-md-12 col-sm-12">
                                <div class="content-page page-404">
                                    <div class="details">
                                        <h3>{{trans('sw.no_record_found')}}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- END LEFT SIDEBAR -->
                @endif


            </div>
        </div>
    </div>
    <!-- END CONTENT -->


            </div>
            <!-- END SIDEBAR & CONTENT -->
        </div>
    </div>

    <!-- start model pay -->
    <div class="modal" id="modalReservation">
        <div class="modal-dialog  modal-xl" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{trans('sw.reservations')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 id="payMemberName" style="font-weight: bolder">&nbsp;</h6>


                    <div class="row">

                        <div class="row col-md-12">

                            {{--                            <div class="form-group col-md-6">--}}
                            {{--                                <label class="col-md-3 control-label">{{trans('sw.member_id')}} </label>--}}
                            {{--                                <div class="col-md-9">--}}
                            {{--                                    <div class="input-group">--}}
                            {{--											<span class="input-group-addon">--}}
                            {{--											<i class="fa fa-search"></i>--}}
                            {{--											</span>--}}

                            {{--                                        <input id="member_id" value="{{ old('member_id') }}"--}}
                            {{--                                               placeholder="{{trans('sw.enter_member_id')}}"--}}
                            {{--                                               name="member_id" type="text" class="form-control"  autocomplete="off" >--}}
                            {{--                                    </div>--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}

                            <div class="form-group col-md-6">
                                {{--                <label class="col-md-3  control-label"> </label>--}}

                                <div class="well">
                                    <div class="row">
                                        <address  class="col-md-6">
                                            <strong>{{trans('sw.name')}}:</strong>
                                            <span id="store_member_name">-</span>
                                        </address>
                                        <address  class="col-md-6">
                                            <strong>{{trans('sw.phone')}}:</strong>
                                            <span id="store_member_phone">-</span>
                                        </address>
                                    </div>

                                    <address>
                                        <strong>{{trans('sw.reservations')}}:</strong><br><br>
                                        <span id="member_reservations">-</span>
                                    </address>

                                </div>

                            </div>

                            <div class="col-md-6"><div id="activity_icons"></div></div>
                            <div class="form-group col-md-12 clearfix"><hr/></div>
                        </div>

                        <div style="clear: none;padding-bottom: 15px"></div>


                        <div class="col-md-12 text-center"><div id="picker"></div></div>
                        {{--        <div>--}}
                        {{--            <p>Selected date: <span id="selected-date"></span></p>--}}
                        {{--            <p>Selected time: <span id="selected-time"></span></p>--}}
                        {{--        </div>--}}
                        <input type="hidden" id="selected_date" value="">
                        <input type="hidden" id="selected_time" value="">
                        <input type="hidden" id="selected_reservation_non_member_id" value="">
                        <input type="hidden" id="selected_reservation_activity_id" value="">
                        <input type="hidden" id="selected_reservation_start_date" value="">
                        <input type="hidden" id="selected_reservation_step" value="">

                        <div class="row" style="clear: none;padding-bottom: 15px"><br/></div>

                        <div class="row">
                            <div class="col-xs-8 col-md-12 invoice-block">
                                <a class="btn btn-lg green hidden-print margin-bottom-5 " onclick="create_reservation();">
                                    {{trans('sw.reservation_complete')}} <i class="fa fa-check"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- End model pay -->
@stop

@section('scripts')
    <script src="https://momentjs.com/downloads/moment.js"></script>
    {{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>--}}
    <script type="text/javascript" src="{{asset('resources/assets/new_front/global/plugins/pick-hours-availability-calendar/mark-your-calendar.js')}}"></script>
    <script type="text/javascript">

        function create_reservation(){

            let selected_date = $('#selected_date').val();
            let selected_time = $('#selected_time').val();
            let selected_non_member_id = $('#selected_reservation_non_member_id').val();
            let selected_activity_id = $('#selected_reservation_activity_id').val();
            let selected_start_date = $('#selected_reservation_start_date').val();
            let selected_step = $('#selected_reservation_step').val();
            if(selected_date && selected_time && selected_non_member_id && selected_activity_id) {
                $.get("{{route('sw.createReservationNonMemberAjax')}}", {  selected_date: selected_date, selected_time: selected_time, selected_activity_id: selected_activity_id,selected_non_member_id :selected_non_member_id, type : 2  },
                    function(result){
                        if(result === 'exist') {
                            alert('{{trans('sw.reservation_member_exist')}}');
                        }else if(result === 'exceed_limit'){
                            alert('{{trans('sw.reservation_member_exceed_limit')}}');
                        }else{
                            $('#ul_member_reservations').append('<li class="list-group-item" id="li_reservation_' + result + '"> <i class="fa fa-calendar text-muted"></i>'
                                + moment(selected_date).format('MM-DD-YYYY')
                                + ' <i class="fa fa-clock-o text-muted"></i>'
                                + moment(selected_date).format('YYYY-MM-DD') + ' ' + selected_time
                                + ' <i class="fa fa-user text-muted"></i> '+ $('#store_member_name').text()
                                + ' <span class="badge badge-danger" onclick="remove_reservation(' + result + ', ' + "'" + selected_time + "'" + ')"><i class="fa fa-times"></i></span>'
                                + '</li>');
                            if(result == 'reload'){
                                location.reload();
                            }else{
                                alert('{{trans('admin.successfully_added')}}');
                            }
                        }

                    }
                );
            }else{
                alert('{{trans('sw.reservation_input_error')}}');
            }
        }
        function remove_reservation(id, time) {
            var result = confirm("{{trans('sw.are_you_sure')}}");
            if (result) {

                $.get("{{route('sw.deleteReservationNonMemberAjax')}}", {id: id, time: time},
                    function (result) {
                        if (result) {
                            alert('{{trans('admin.successfully_deleted')}}');
                            $('#li_reservation_' + id).remove();
                            location.reload();
                        }
                    }
                );

                return false;
            }

        }

        function non_membership_reservation(id, activity_id, step, start_date){
            $('#selected_reservation_non_member_id').val(id);
            $('#selected_reservation_activity_id').val(activity_id);
            $('#selected_reservation_start_date').val(start_date);
            $('#selected_reservation_step').val(step);

            let availability = [];
            $.ajax({
                url: '{{route('sw.getNonMemberReservation')}}',
                cache: false,
                type: 'GET',
                data: {'activity_id': activity_id, 'non_member_id': id, 'start_date': start_date, 'step': step, member_type: 2},
                dataType: 'json',
                success: function (response) {
                    $('#store_member_name').html(response.non_member?.name);
                    $('#store_member_phone').html(response.non_member?.phone);

                    let reservations = '<ul class="list-group" id="ul_member_reservations">';
                    if(response.member_reservations){

                        for(let i=0; i < response.member_reservations.length; i++) {
                            reservations += '<li class="list-group-item" id="li_reservation_' + response.member_reservations[i].id + '"> <i class="fa fa-calendar text-muted"></i>'
                                + moment(response.member_reservations[i].date).format('L')
                                + ' <i class="fa fa-clock-o text-muted"></i>'
                                + response.member_reservations[i].date
                                + ' <i class="fa fa-user text-muted"></i> '+ (response.member_reservations[i]?.non_member?.name || response.member_reservations[i]?.member?.name)
                                + ' <span class="badge badge-danger" onclick="remove_reservation(' + response.member_reservations[i].id + ', ' + "'" + response.member_reservations[i].date + "'" + ')"><i class="fa fa-times"></i></span>'
                                + '</li>';
                        }
                    }
                    reservations+='</ul>';
                    $('#member_reservations').html(reservations);
                    let activity_name = $('#activity_'+id+'_'+activity_id).html(); //document.querySelector('#activity_'+id+'_'+activity_id);
                    let start_date = response.start_date || '{{\Carbon\Carbon::now()->subDay(@\Carbon\Carbon::now()->dayOfWeek)->format('Y-m-d')}}';
                    $('#activity_icons').html('<button class="btn btn-primary btn-md rounded-3">' + activity_name + '</botton>');

                    if(response?.reservation_check === 0) {
                        availability = response.reservations;

                        // https://www.jqueryscript.net/time-clock/pick-hours-availability-calendar.html#google_vignette
                        // $('#myc-next-week').hide();
                        // $('#myc-prev-week').hide();
                        $('#picker').markyourcalendar({
                            months: ['{{trans('sw.jan')}}','{{trans('sw.feb')}}','{{trans('sw.mar')}}','{{trans('sw.apr')}}','{{trans('sw.may')}}','{{trans('sw.jun')}}','{{trans('sw.jul')}}','{{trans('sw.aug')}}','{{trans('sw.sep')}}','{{trans('sw.oct')}}','{{trans('sw.nov')}}','{{trans('sw.dec')}}'],
                            weekdays: ['{{trans('sw.sun')}}','{{trans('sw.mon')}}','{{trans('sw.tue')}}','{{trans('sw.wed')}}','{{trans('sw.thurs')}}','{{trans('sw.fri')}}','{{trans('sw.sat')}}'],

                            availability: availability,
                            startDate: new Date(start_date),
                            onClick: function(ev, data) {
                                // data is a list of datetimes
                                var d = data[0].split(' ')[0];
                                var t = data[0].split(' ')[1];
                                $('#selected_date').val(d);
                                $('#selected_time').val(t);

                                ev.addClass('selected');
                                // $('#selected-date').html(d);
                                // $('#selected-time').html(t);
                            },prevHtml : '<a onclick="non_membership_reservation('+ id +', '+ activity_id +', '+1+', ' + '\'' + start_date + '\'' + ')" id="myc-prev-week"><</a>',nextHtml : '<a onclick="non_membership_reservation('+ id +', '+ activity_id +', '+2+', ' + '\'' + start_date + '\'' + ')" id="myc-next-week">></a>'
                            , onClickNavigator: function(ev, instance) {
                                console.log(instance);
                                console.log(ev);
                                console.log(instance[0].split(' ')[0]);
                                //     var arr = [
                                //         [
                                //             ['4:01', '5:00', '6:00', '7:00', '8:01'],
                                //             ['1:00', '5:00'],
                                //             ['2:00', '5:00'],
                                //             ['3:30'],
                                //             ['2:00', '5:00'],
                                //             ['2:00', '5:00'],
                                //             ['2:00', '5:00']
                                //         ],
                                //         [
                                //             ['2:00', '5:00'],
                                //             ['4:00', '5:00', '6:00', '7:00', '8:00'],
                                //             ['4:00', '5:00'],
                                //             ['2:00', '5:00'],
                                //             ['2:00', '5:00'],
                                //             ['2:00', '5:00'],
                                //             ['2:00', '5:00']
                                //         ],
                                //         [
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00', '6:00', '7:00', '8:00'],
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00']
                                //         ],
                                //         [
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00', '6:00', '7:00', '8:00'],
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00'],
                                //             ['4:00', '5:00']
                                //         ],
                                //         [
                                //             ['4:00', '6:00'],
                                //             ['4:00', '6:00'],
                                //             ['4:00', '6:00'],
                                //             ['4:00', '6:00'],
                                //             ['4:00', '5:00', '6:00', '7:00', '8:00'],
                                //             ['4:00', '6:00'],
                                //             ['4:00', '6:00']
                                //         ],
                                //         [
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00'],
                                //             ['3:00', '6:00'],
                                //             ['4:00', '5:00', '6:00', '7:00', '8:00'],
                                //             ['3:00', '6:00']
                                //         ],
                                //         [
                                //             ['3:00', '4:00'],
                                //             ['3:00', '4:00'],
                                //             ['3:00', '4:00'],
                                //             ['3:00', '4:00'],
                                //             ['3:00', '4:00'],
                                //             ['3:00', '4:00'],
                                //             ['4:00', '5:00', '6:00', '7:00', '8:00']
                                //         ]
                                //     ]
                                //     var rn = Math.floor(Math.random() * 10) % 7;
                                //     instance.setAvailability(arr[rn]);
                            }
                        });

                    }else if(response?.reservation_check === 1) {
                        $('#picker').html('<div class="alert alert-danger">{{trans('sw.member_time_reservation_activity_exceed_limit_error')}}</div>');
                    }else{
                        $('#picker').html('<div class="alert alert-danger">{{trans('sw.no_dates_available_for_activity_error')}}</div>');

                    }

                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });


        }

    </script>
    <script>
        $('[data-toggle="tooltip"]').tooltip();
        $(document).on('click', '.confirm_delete', function (event) {
            var tr = $(this).parent().parent();
            event.preventDefault();
            url = $(this).attr('href');
            swal({
                title: "{{trans('admin.are_you_sure')}}",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "{{trans('admin.yes')}}",
                cancelButtonText: "{{trans('admin.no_cancel')}}",
                showLoaderOnConfirm: true,
//                ,closeOnConfirm: false,
//                closeOnCancel: false
                preConfirm: function (isConfirm) {
                    return new Promise(function (resolve, reject) {
                        setTimeout(function () {
                            if (isConfirm) {
                                $.ajax({
                                    url: url,
                                    type: 'GET',
                                    success: function () {
                                        swal("{{trans('completed')}}", "{{trans('admin.completed_successfully')}}", "success");

                                        tr.remove();
                                    },
                                    error: function (request, error) {
                                        swal("{{trans('operation_failed')}}", "{{trans('admin.something_wrong')}}", "error");
                                        console.error("Request: " + JSON.stringify(request));
                                        console.error("Error: " + JSON.stringify(error));
                                    }
                                });
                            } else {
                                swal("{{trans('admin.cancelled')}}", "{{trans('admin.everything_still')}}", "info");
                            }
//            });
                        }, 2000)
                    })
                },
                allowOutsideClick: false
            }).then(function (isConfirm) {

            });

//                    .then(function () {
//
        });



    </script>
@endsection


