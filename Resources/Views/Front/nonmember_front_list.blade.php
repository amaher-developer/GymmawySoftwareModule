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

    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/admin/global/plugins/pick-hours-availability-calendar/mark-your-calendar.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css"/>
    <style>
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

        .invoice-block {
            text-align: center;
        }

        @media (min-width: 768px) {
            .modal-xl {
                width: 90%;
                max-width: 1200px;
            }
        }
    </style>
@endsection
@section('page_body')

<!--begin::Non Members-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-user fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <div class="card-toolbar">
        <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
        <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_nonmembers_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->
                
                <!--begin::Add Member-->
                @if(in_array('createNonMember', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createNonMember')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add Member-->
                
                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportNonMemberPDF', 'exportNonMemberExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download')}}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportNonMemberExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportNonMemberExcel', $search_query)}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export')}}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportNonMemberPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportNonMemberPDF', $search_query)}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.pdf_export')}}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <!--end::Export-->
                
                <!--begin::Calendar Button-->
                <a href="{{route('sw.listNonMemberReport')}}" class="btn btn-sm btn-flex btn-light-info">
                    <i class="ki-outline ki-calendar fs-6"></i>
                    {{ trans('sw.activities_calender')}}
                </a>
                <!--end::Calendar Button-->
            </div>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Filter-->
        <div class="collapse" id="kt_nonmembers_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" id="from_date" value="@php echo @strip_tags($_GET['from']) ? \Carbon\Carbon::parse($_GET['from'])->format('Y-m-d') : '' @endphp" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" name="to" id="to_date" value="@php echo @strip_tags($_GET['to']) ? \Carbon\Carbon::parse($_GET['to'])->format('Y-m-d') : '' @endphp" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.activity')}}</label>
                            <select name="activity" class="form-select form-select-solid">
                                <option value="">{{ trans('admin.choose')}}...</option>
                                @foreach($activities as $activity)
                                    <option value="{{$activity->name}}" @if(request('activity') == $activity->name) selected="" @endif>{{$activity->name}}</option>
                                @endforeach
                            </select>
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

        @if(count($members) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_non_members_table">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px text-nowrap">
                            <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.name')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-phone fs-6 me-2"></i>{{ trans('sw.phone')}}
                        </th>
                        <th class="min-w-200px text-nowrap">
                            <i class="ki-outline ki-list fs-6 me-2"></i>{{ trans('sw.activities')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.price')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.amount_remaining')}}
                        </th>
                        <th class="min-w-100px">
                            <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date')}}
                        </th>
                        <th class="text-end min-w-70px actions-column">
                            <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                        </th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($members as $key=> $member)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-50px me-3">
                                        <div class="symbol-label fs-3 bg-light-primary text-primary">
                                            <i class="ki-outline ki-user fs-2"></i>
                                        </div>
                                    </div>
                                    <!--end::Avatar-->
                                    <div>
                                        <!--begin::Title-->
                                        <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            {{ $member->name }}
                                        </div>
                                        @if($member->national_id)
                                            <div class="text-muted fs-7">
                                                <i class="ki-outline ki-credit-cart fs-6 me-1"></i> {{$member->national_id}}
                                            </div>
                                        @endif
                                        @if(@$member->notes)
                                            <div class="text-muted fs-7">
                                                <span class="badge badge-light-info" style="cursor: pointer;" data-target="#pt_subscription_notes_{{$member->id}}" data-toggle="modal">
                                                    <i class="ki-outline ki-information-5 fs-6 me-1"></i> {{ trans('sw.notes')}}
                                                </span>
                                            </div>
                                        @endif
                                        <!--end::Title-->
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ $member->phone }}</span>
                            </td>
                            <td class="pe-0">
                                <div class="d-flex flex-wrap gap-1">
                                    @php  echo implode('', array_map(function ($name) use ($member){ static $i = 0; return '<button class="btn btn-'. (((count(@$member->non_member_times)  > 0 ) && ($name['id'] == $member->activities[$i]['id'])) ? 'success' : 'primary')  .' btn-sm rounded-2" id="activity_'.@$member->id.'_'.@$name['id'].'" onclick="non_membership_reservation('.@$member->id.', '.@$name['id'].')"  data-target="#modalReservation" data-toggle="modal" style="font-size: 10px; padding: 2px 6px;">'.$name['name'].'</button>'; $i++;},@$member->activities ?? [])) @endphp
                                </div>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ number_format($member->price, 2) }}</span>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ @number_format($member->amount_remaining, 2) }}</span>
                            </td>
                            <td class="pe-0">
                                <div class="d-flex flex-column">
                                    <div class="text-muted fw-bold d-flex align-items-center">
                                        <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                        <span>{{ $member->created_at->format('Y-m-d') }}</span>
                                    </div>
                                    <div class="text-muted fs-7 d-flex align-items-center">
                                        <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                        <span>{{ $member->created_at->format('h:i a') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end actions-column">
                                <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                    <!--begin::WhatsApp-->
                                    <a href="https://web.whatsapp.com/send?phone={{ ((substr( $member->phone, 0, 1 ) === "+") || (substr( $member->phone, 0, 2 ) === "00")) ? $member->phone : '+'.env('APP_COUNTRY_CODE').$member->phone}}"
                                       target="_blank" class="btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="{{ trans('sw.whatsapp')}}">
                                        <i class="ki-outline ki-message-text-2 fs-2"></i>
                                    </a>
                                    <!--end::WhatsApp-->
                                    
                                    <!--begin::Invoice-->
                                    <a href="{{route('sw.showOrderSubscriptionNonMember',$member->id)}}"
                                       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('sw.invoice')}}">
                                        <i class="ki-outline ki-document fs-2"></i>
                                    </a>
                                    <!--end::Invoice-->
                                    
                                    @if(in_array('editNonMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Edit-->
                                        <a href="{{route('sw.editNonMember',$member->id)}}"
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                        <!--end::Edit-->
                                    @endif
                                
                                @if(in_array('deleteNonMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                    @if(request('trashed'))
                                        <!--begin::Enable-->
                                        <a title="{{ trans('admin.enable')}}"
                                           href="{{route('sw.deleteNonMember',$member->id)}}"
                                           class="confirm_delete btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="{{ trans('admin.enable')}}">
                                            <i class="ki-outline ki-check-circle fs-2"></i>
                                        </a>
                                        <!--end::Enable-->
                                    @else
                                        <!--begin::Delete-->
                                        <a title="{{ trans('admin.disable')}}"
                                           data-swal-text="{{ trans('sw.disable_with_refund', ['amount' => $member->price])}}"
                                           href="{{route('sw.deleteNonMember',$member->id).'?refund=1&total_amount='.@$member->price}}"
                                           data-swal-amount="{{@$member->price}}"
                                           class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="{{ trans('admin.disable')}}">
                                            <i class="ki-outline ki-trash fs-2"></i>
                                        </a>
                                        <!--end::Delete-->
                                    @endif
                                @endif
                                </div>
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
                        'from' => $members->firstItem() ?? 0,
                        'to' => $members->lastItem() ?? 0,
                        'total' => $members->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $members->appends($search_query)->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                        <i class="ki-outline ki-user fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Non Members-->

    <!-- start model pay -->
    <div class="modal" id="modalReservation">
        <div class="modal-dialog  modal-xl" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.reservations')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 id="payMemberName" style="font-weight: bolder">&nbsp;</h6>


                    <div class="row">

                        <div class="row col-md-12">

{{--                            <div class="form-group col-md-6">--}}
{{--                                <label class="col-md-3 control-label">{{ trans('sw.member_id')}} </label>--}}
{{--                                <div class="col-md-9">--}}
{{--                                    <div class="input-group">--}}
{{--											<span class="input-group-addon">--}}
{{--											<i class="fa fa-search"></i>--}}
{{--											</span>--}}

{{--                                        <input id="member_id" value="{{ old('member_id') }}"--}}
{{--                                               placeholder="{{ trans('sw.enter_member_id')}}"--}}
{{--                                               name="member_id" type="text" class="form-control"  autocomplete="off" >--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

                            <div class="form-group col-md-6">
                                {{--                <label class="col-md-3  control-label"> </label>--}}

                                <div class="well">
                                    <div class="row">
                                        <address  class="col-md-6">
                                            <strong>{{ trans('sw.name')}}:</strong>
                                            <span id="store_member_name">-</span>
                                        </address>
                                        <address  class="col-md-6">
                                            <strong>{{ trans('sw.phone')}}:</strong>
                                            <span id="store_member_phone">-</span>
                                        </address>
                                    </div>

                                    <address>
                                        <strong>{{ trans('sw.reservations')}}:</strong><br><br>
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
                                    {{ trans('sw.reservation_complete')}} <i class="fa fa-check"></i>
                                </a>
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
    @parent
    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    
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

        $("#filter_form").slideUp();
        $(".filter_trigger_button").click(function () {
            $("#filter_form").slideToggle(300);
        });

        $(document).on('click', '.remove_filter', function (event) {
            event.preventDefault();
            var filter = $(this).attr('id');
            $("#" + filter).val('');
            $("#form_filter").submit();
        });
        jQuery(document).ready(function () {
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
        });

    </script>

    <script src="https://momentjs.com/downloads/moment.js"></script>
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>--}}

    {{--    <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>--}}
    <script type="text/javascript" src="{{asset('resources/assets/admin/global/plugins/pick-hours-availability-calendar/mark-your-calendar.js')}}"></script>
    <script type="text/javascript">

        function create_reservation(){

            let selected_date = $('#selected_date').val();
            let selected_time = $('#selected_time').val();
            let selected_non_member_id = $('#selected_reservation_non_member_id').val();
            let selected_activity_id = $('#selected_reservation_activity_id').val();
            let selected_start_date = $('#selected_reservation_start_date').val();
            let selected_step = $('#selected_reservation_step').val();
            if(selected_date && selected_time && selected_non_member_id && selected_activity_id) {
                $.get("{{route('sw.createReservationNonMemberAjax')}}", {  selected_date: selected_date, selected_time: selected_time, selected_activity_id: selected_activity_id,selected_non_member_id :selected_non_member_id  },
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
        function remove_reservation(id, time) {
            swal({
                title: trans_are_you_sure,
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: trans_yes,
                cancelButtonText: trans_no_please,
                showLoaderOnConfirm: true,
//                ,closeOnConfirm: false,
//                closeOnCancel: false
                preConfirm: function (isConfirm) {
                    return new Promise(function (resolve, reject) {
                        setTimeout(function () {
                            if (isConfirm) {
                                $.get("{{route('sw.deleteReservationNonMemberAjax')}}", {id: id, time: time},
                                    function (result) {
                                    if(result) {
                                        swal({
                                            title: trans_done,
                                            text: trans_successfully_processed,
                                            type: "success",
                                            timer: 4000,
                                            confirmButtonText: 'Ok',
                                        });
                                        if (result == 'reload') {
                                            location.reload();
                                        }
                                        $('#li_reservation_' + id).remove();
                                    }else{
                                        swal({
                                            title: trans_operation_failed,
                                            text: trans_operation_failed,
                                            type: "error",
                                            timer: 4000,
                                            confirmButtonText: 'Ok',
                                        });
                                    }
                                    }
                                );

                                return false;
                            } else {
                                swal("Cancelled", "Alright, everything still as it is", "info");
                            }
//            });
                        }, 2000)
                    })
                },
                allowOutsideClick: false
            }).then(function (isConfirm) {

            });
            return false;
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
                data: {'activity_id': activity_id, 'non_member_id': id, 'start_date': start_date, 'step': step},
                dataType: 'json',
                success: function (response) {
                    $('#store_member_name').html(response.non_member.name);
                    $('#store_member_phone').html(response.non_member.phone);

                    let reservations = '<ul class="list-group" id="ul_member_reservations">';
                    if(response.member_reservations){

                        for(let i=0; i < response.member_reservations.length; i++) {
                            reservations += '<li class="list-group-item" id="li_reservation_' + response.member_reservations[i].id + '"> <i class="fa fa-calendar text-muted"></i>'
                                + moment(response.member_reservations[i].date).format('L')
                                + ' <i class="fa fa-clock-o text-muted"></i>'
                                + response.member_reservations[i].date
                                + ' <span class="badge badge-danger" onclick="remove_reservation(' + response.member_reservations[i].id + ', ' + "'" + response.member_reservations[i].date + "'" + ')"><i class="fa fa-times"></i></span>'
                                + '</li>';
                        }
                    }
                    reservations+='</ul>';
                    $('#member_reservations').html(reservations);
                    let activity_name = $('#activity_'+id+'_'+activity_id).html(); //document.querySelector('#activity_'+id+'_'+activity_id);
                    let start_date = response.start_date || '{{\Carbon\Carbon::now()->subDay(@\Carbon\Carbon::now()->dayOfWeek)->format('Y-m-d')}}';
                    $('#activity_icons').html('<button class="btn btn-primary btn-md rounded-3">' + activity_name + '</botton>');

                    availability = response.reservations;


                    // https://www.jqueryscript.net/time-clock/pick-hours-availability-calendar.html#google_vignette
                    // $('#myc-next-week').hide();
                    // $('#myc-prev-week').hide();
                    $('#picker').markyourcalendar({
                        months: ['{{ trans('sw.jan')}}','{{ trans('sw.feb')}}','{{ trans('sw.mar')}}','{{ trans('sw.apr')}}','{{ trans('sw.may')}}','{{ trans('sw.jun')}}','{{ trans('sw.jul')}}','{{ trans('sw.aug')}}','{{ trans('sw.sep')}}','{{ trans('sw.oct')}}','{{ trans('sw.nov')}}','{{ trans('sw.dec')}}'],
                        weekdays: ['{{ trans('sw.sun')}}','{{ trans('sw.mon')}}','{{ trans('sw.tue')}}','{{ trans('sw.wed')}}','{{ trans('sw.thurs')}}','{{ trans('sw.fri')}}','{{ trans('sw.sat')}}'],

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

        $("#filter_form").slideUp();
        $(".filter_trigger_button").click(function () {
            $("#filter_form").slideToggle(300);
        });

        $(document).on('click', '.remove_filter', function (event) {
            event.preventDefault();
            var filter = $(this).attr('id');
            $("#" + filter).val('');
            $("#form_filter").submit();
        });




// Date range picker functionality removed - using individual date pickers instead


    </script>
@endsection
