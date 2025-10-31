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

                        @if(@count($trainings) > 0)
                            <!-- BEGIN LEFT SIDEBAR -->
                                <div class="col-md-9 col-sm-9 blog-posts">
                                    @foreach($trainings as $training)
                                        <div class="row">


                                            <div class="col-md-8 col-sm-8">
                                                <h2>{{$training->title}}</h2>
                                                <div class="row front-lists-v2 margin-bottom-15">
                                                    <div class="col-md-6">
                                                        <ul class="list-unstyled">
                                                            <li><i class="fa fa-calendar"></i> <b>{{trans('sw.date')}}:</b> {{\Carbon\Carbon::parse($training->created_at)->toDateString()}}</li>
                                                            <li><i class="fa fa-sort-numeric-asc"></i> <b>{{trans('sw.height')}}:</b> {{$training->height}}</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <ul class="list-unstyled">
                                                            <li><i class="fa fa-bars"></i> <b>{{trans('sw.type')}}:</b> {{$training->type == 2 ? trans('sw.plan_diet') : trans('sw.plan_training')}}</li>
                                                            <li><i class="fa fa-balance-scale"></i> <b>{{trans('sw.weight')}}:</b> {{$training->weight}}</li>
                                                            {{--                                        <li><i class="fa fa-star"></i> Awesome UI</li>--}}
                                                        </ul>
                                                    </div>
                                                </div>
                                                <br>
                                                <p>{{strip_tags($training->plan_details, '<br/>')}}</p>
                                            </div>

                                        </div>
                                        <hr class="blog-post-sep">
                                    @endforeach

                                    <ul class="pagination">
                                        {!! $trainings->appends($search_query)->render()  !!}
                                    </ul>
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
