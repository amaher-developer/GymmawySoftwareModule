@extends('software::Web.master')

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
                            <h2>{{$subscription->subscription->name}}</h2>
{{--                            <p>Lorem ipsum dolor sit amet, dolore eiusmod quis tempor incididunt ut et dolore Ut veniam unde nostrudlaboris. Sed unde omnis iste natus error sit voluptatem.</p>--}}
                            <br>
                            <div class="row front-lists-v2 margin-bottom-15">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><i class="fa fa-calendar"></i> <b>{{trans('sw.joining_date')}}:</b> {{\Carbon\Carbon::parse($subscription->joining_date)->toDateString()}}</li>
                                        <li><i class="fa fa-sort-numeric-asc"></i> <b>{{trans('sw.workouts')}}:</b> {{ @$subscription->workouts }}</li>
                                        <li><i class="fa fa-dollar"></i> <b>{{trans('sw.amount_paid')}}:</b> {{@number_format($subscription->amount_paid, 2)}}</li>
                                        <li><i class="fa fa-check"></i> <b>{{trans('sw.status')}}:</b> {{ @$subscription->status_name }}</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><i class="fa fa-calendar"></i> <b>{{trans('sw.expire_date')}}:</b> {{\Carbon\Carbon::parse($subscription->expire_date)->toDateString()}}</li>
                                        <li><i class="fa fa-sort-numeric-asc"></i> <b>{{trans('sw.number_of_visits')}}:</b> {{ @$subscription->visits }}</li>
                                        <li><i class="fa fa-dollar"></i> <b>{{trans('sw.amount_remaining')}}:</b> {{@number_format($subscription->amount_remaining, 2)}}</li>
{{--                                        <li><i class="fa fa-star"></i> Awesome UI</li>--}}
                                    </ul>
                                </div>
                            </div>
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
                                    <div class="number">
                                        404
                                    </div>
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
@stop

@section('scripts')
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


