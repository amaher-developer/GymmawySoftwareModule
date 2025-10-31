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
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/admin/global/plugins/pick-hours-availability-calendar/mark-your-calendar.css')}}">
    <style>
        .myc-date-header {
            text-align: center;
        }
        .member-info li{
            list-style-type: none;
            line-height: 34px;
        }
        .member-info{
            background: #9e9e9e73;
            border-radius: 8px !important;
            margin: 0px;
            padding: 10px 0;
        }
        #myc-next-week {
            display: none !important;
        }
        #myc-prev-week {
            display: none !important;
        }
        .invoice-block {
            text-align: center;
        }
    </style>
@endsection
@section('page_body')
    <!--begin::Card-->
    <div class="card card-flush">
        <!--begin::Card header-->
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <div class="d-flex align-items-center my-1">
                    <i class="ki-outline ki-calendar fs-2 me-3"></i>
                    <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
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
                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.reservations')}}</span>
                    <span class="fs-2 fw-bold text-primary">{{ trans('sw.calendar_view')}}</span>
                </div>
            </div>
            <!--end::Total count-->

            <!--begin::Member Search-->
            <div class="row mb-5">
                <div class="col-md-6">
                    <div class="d-flex align-items-center position-relative" style="max-width: 400px;">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                        <input id="member_id" value="{{ old('member_id') }}"
                               placeholder="{{ trans('sw.enter_member_id')}}"
                               name="member_id" type="text" class="form-control form-control-solid ps-12" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light-info">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-outline ki-user fs-2 text-info me-3"></i>
                                        <div>
                                            <div class="fs-6 fw-semibold text-gray-900">{{ trans('sw.name')}}</div>
                                            <div class="fs-7 text-muted" id="store_member_name">-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-outline ki-phone fs-2 text-info me-3"></i>
                                        <div>
                                            <div class="fs-6 fw-semibold text-gray-900">{{ trans('sw.phone')}}</div>
                                            <div class="fs-7 text-muted" id="store_member_phone">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="d-flex align-items-center">
                                    <i class="ki-outline ki-calendar fs-2 text-info me-3"></i>
                                    <div>
                                        <div class="fs-6 fw-semibold text-gray-900">{{ trans('sw.reservations')}}</div>
                                        <div class="fs-7 text-muted" id="member_reservations">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Search-->

            <!--begin::Calendar-->
            <div class="card bg-light-primary">
                <div class="card-body p-6">
                    <div id="picker"></div>
                </div>
            </div>
            <!--end::Calendar-->

            <!--begin::Hidden inputs-->
            <input type="hidden" id="selected_date" value="">
            <input type="hidden" id="selected_time" value="">
            <input type="hidden" id="selected_member_id" value="">
            <!--end::Hidden inputs-->

            <!--begin::Actions-->
            <div class="d-flex justify-content-center mt-5">
                <button type="button" class="btn btn-primary btn-lg" onclick="create_reservation();">
                    <i class="ki-outline ki-check fs-2"></i>
                    {{ trans('sw.reservation_complete')}}
                </button>
            </div>
            <!--end::Actions-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
@endsection

@section('scripts')
    @parent

    <script src="https://momentjs.com/downloads/moment.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
{{--    <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>--}}
    <script type="text/javascript" src="{{asset('resources/assets/admin/global/plugins/pick-hours-availability-calendar/mark-your-calendar.js')}}"></script>
    <script type="text/javascript">
            $('#member_id').keyup(function () {
                let member_id = $('#member_id').val();

                $.get("{{route('sw.getReservationMemberAjax')}}", {  member_id: member_id },
                    function(result){
                        if(result){
                            $('#store_member_name').html(result.name);
                            $('#store_member_phone').html(result.phone);
                            let reservations = '<ul class="list-group" id="ul_member_reservations">';
                            if(result.gym_reservations){
                                for(let i=0; i < result.gym_reservations.length; i++){
                                    reservations+= '<li class="list-group-item" id="li_reservation_'+result.gym_reservations[i].id+'"> <i class="fa fa-calendar text-muted"></i>'
                                                    + moment(result.gym_reservations[i].date).format('L')
                                                    + ' <i class="fa fa-clock-o text-muted"></i>'
                                                    + result.gym_reservations[i].time_slot
                                                    + ' <span class="badge badge-danger" onclick="remove_reservation('+result.gym_reservations[i].id+', '+ "'" +result.gym_reservations[i].time_slot + "'" +')"><i class="fa fa-times"></i></span>'
                                                    +'</li>';
                                }
                            }
                            reservations+='</ul>';
                            $('#member_reservations').html(reservations);
                            $('#selected_member_id').val(result.id);
                        }else{
                            $('#store_member_name').html('-');
                            $('#store_member_phone').html('-');
                            $('#selected_member_id').val('');
                            $('#member_reservations').html('-');
                        }
                    }
                );
            });
            function create_reservation(){

                let member_id = $('#selected_member_id').val();
                let selected_date = $('#selected_date').val();
                let selected_time = $('#selected_time').val();
                if(member_id && selected_date && selected_time) {
                    $.get("{{route('sw.createReservationMemberAjax')}}", {  member_id: member_id, selected_date: selected_date, selected_time: selected_time },
                        function(result){
                            if(result != 'exist') {
                                $('#ul_member_reservations').append('<li class="list-group-item" id="li_reservation_' + result + '"> <i class="fa fa-calendar text-muted"></i>'
                                    + moment(selected_date).format('L')
                                    + ' <i class="fa fa-clock-o text-muted"></i>'
                                    + selected_time
                                    + ' <span class="badge badge-danger" onclick="remove_reservation(' + result + ', ' + "'" + selected_time + "'" + ')"><i class="fa fa-times"></i></span>'
                                    + '</li>');
                                if(result == 'reload'){
                                    location.reload();
                                }else{
                                    alert('{{ trans('admin.successfully_added')}}');
                                }
                            }else{
                                alert('{{ trans('sw.reservation_member_exist')}}');
                            }

                        }
                    );
                }else{
                    alert('{{ trans('sw.reservation_input_error')}}');
                }
            }
            function remove_reservation(id, time){
                var check = confirm("{{ trans('admin.are_you_sure')}}");
                if(check){
                    $.get("{{route('sw.deleteReservationMemberAjax')}}", {  id: id, time: time },
                        function(result){
                            if(result == 'reload'){
                                location.reload();
                            }
                            $('#li_reservation_'+id).remove();
                        }
                    );
                }
            }

            // https://www.jqueryscript.net/time-clock/pick-hours-availability-calendar.html#google_vignette
            $('#myc-next-week').hide();
            $('#myc-prev-week').hide();
            $('#picker').markyourcalendar({
                months: ['{{ trans('sw.jan')}}','{{ trans('sw.feb')}}','{{ trans('sw.mar')}}','{{ trans('sw.apr')}}','{{ trans('sw.may')}}','{{ trans('sw.jun')}}','{{ trans('sw.jul')}}','{{ trans('sw.aug')}}','{{ trans('sw.sep')}}','{{ trans('sw.oct')}}','{{ trans('sw.nov')}}','{{ trans('sw.dec')}}'],
                weekdays: ['{{ trans('sw.sun')}}','{{ trans('sw.mon')}}','{{ trans('sw.tue')}}','{{ trans('sw.wed')}}','{{ trans('sw.thurs')}}','{{ trans('sw.fri')}}','{{ trans('sw.sat')}}'],


                availability: [
                    @foreach($mainSettings->reservation_details['work_days'] as $index => $day)
                        @php
                            $dayIndex = \Carbon\Carbon::now()->addDays((int)@$index)->dayOfWeek;
                            $intervals = \Carbon\CarbonInterval::minutes(@$mainSettings->reservation_details['time_slot'])->toPeriod(@$day['start'], @$day['end']);

                        @endphp
                        [
                            @if(count($intervals) > 1)
                                @foreach ($intervals as $date)
                                    @php

                                        $reservationATDay = $reservations->filter(function ($item) use($index, $date) {
                                                                        if((\Carbon\Carbon::parse($item->date)->toDateString() == \Carbon\Carbon::now()->addDays((int)@$index)->toDateString()) &&($item->time_slot == $date->format('H:i'))){
                                                                            return $item;
                                                                        }
                                                                })->values();
                                        $reservationATDay = @$reservationATDay[0];
                                    @endphp
                                    @if(intval(@$reservationATDay['total']) < $mainSettings->reservation_details['max_member_per_slot'])
                                        '{{$date->format('H:i')}}',
                                    @endif
                                @endforeach
                            @endif
                        ],
                    @endforeach
                ],
                startDate: new Date("{{\Carbon\Carbon::now()->format('Y-m-d')}}"),
                onClick: function(ev, data) {
                    // data is a list of datetimes
                    var d = data[0].split(' ')[0];
                    var t = data[0].split(' ')[1];
                    $('#selected_date').val(d);
                    $('#selected_time').val(t);
                    // $('#selected-date').html(d);
                    // $('#selected-time').html(t);
                },
                // onClickNavigator: function(ev, instance) {
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
                // }
            });
    </script>
    <script>
        $("#filter_form").slideUp();
        $(".filter_trigger_button").click(function () {
            $("#filter_form").slideToggle(300);
        });

        $(document).on('click', '.remove_filter', function (event) {
            event.preventDefault();
            var filter = $(this).attr('id');
            $("#" + filter).val('');
            $("#filter_form").submit();
        });


    </script>

@endsection
