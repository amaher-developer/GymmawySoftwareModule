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
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css"/>
@endsection
@section('page_body')
    <div class="row">
        <div class="row " style="padding-bottom: 15px;">
            <form id="form_filter"
                  action=""
                  method="get">
                <div class="col-lg-3 col-md-3 col-xs-9 mg-t-20 mg-lg-t-0" style="padding-bottom: 5px">
                    <input class="form-control form-control-inline input-medium date-picker" size="16" type="text"
                           placeholder="{{ trans('sw.date')}}" name="date"
                           data-date="10/11/2012" data-date-format="mm/dd/yyyy"
                           value="@php echo @strip_tags($_GET['date']) @endphp" autocomplete="off"/>
                    {{--                        <span class="help-block">--}}
                    {{--											Select date </span>--}}
                </div><!-- end filter div -->

                <div class="col-lg-2 col-md-2 col-xs-3">
                    <button class="btn btn-primary  rounded-3 btn-block" id="filter" type="submit"><i
                            class="fa fa-filter mx-1"></i> {{ trans('sw.filter')}}</button>
                </div>

            </form>
        </div>
        <div style="clear: none"></div>
        <div class="row">
            <div class="col-lg-4  col-md-4 col-xs-6 mg-t-20 mg-lg-t-0">
                <div class="input-group">
                    <form class="d-flex w-100" action=""
                          method="get">
                        <div class="input-group ">
                            <input type="text" name="search" class="form-control" value="@php echo @strip_tags($_GET['search']) @endphp"
                                   placeholder="{{ trans('sw.search_on')}}">
                            <span class="input-group-btn ">
											<button class="btn blue  rounded-3" type="submit"><i
                                                    class="fa fa-search"></i></button>
											</span>
                        </div>
                        <span
                            class="input-group-btn "><i
                                class="fa fa-search"></i></span>

                    </form>
                </div><!-- end search button-->
            </div><!-- end search div -->
        </div>
        <div style="clear: none;padding-bottom: 15px"></div>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped table-bordered table-hover">
                    <tbody>
                    <tr>
                        <th>{{ trans('admin.total_count')}}</th>
                        <td>{{ $total }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if(count($logs) > 0)
        <div class="table-responsive border-top userlist-table">
            <table class="table card-table table-striped table-vcenter ">
                <thead>
                <tr class="">
                    <th style="width: 60%"><i class="fa fa-info-circle"></i> {{ trans('sw.notification')}}</th>
                    <th class="text-nowrap mb-0"><i class="fa fa-calendar"></i> {{ trans('sw.date')}}</th>
                    <th><i class="fa fa-user"></i> {{ trans('sw.by')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $key=> $log)
                    <tr>
                        <td> {{ $log->body }}</td>
                        <td class="text-nowrap mb-0"><i class="fa fa-calendar text-muted"></i> {{ $log->created_at->format('Y-m-d') }}
                            <br/>
                            <i class="fa fa-clock-o text-muted"></i> {{ $log->created_at->format('h:i a') }}</td>
                        <td class="text-nowrap mb-0"> {{ @$log->user->name }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-lg-5 col-md-5 col-md-offset-5 text-center">
            {!! $logs->appends($search_query)->render()  !!}
        </div>
        @else
            <h4 class="col-lg-12 text-center">{{ trans('sw.no_record_found')}}</h4>
        @endif
    </div>
@endsection

@section('scripts')
    @parent
{{--    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script type="text/javascript"--}}
{{--            src="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-daterangepicker/moment.min.js"></script>--}}
{{--    <script type="text/javascript"--}}
{{--            src="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>--}}
{{--    <script src="{{asset('/')}}resources/assets/admin/global/scripts/metronic.js" type="text/javascript"></script>--}}
{{--    <script src="{{asset('/')}}resources/assets/admin/pages/scripts/components-pickers.js"></script>--}}
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
            $("#filter_form").submit();
        });

        jQuery(document).ready(function () {
            ComponentsPickers.init();
        });
    </script>

@endsection
