@extends('software::Web.master')

@section('content')
    <div class="main">
        <div class="container">
            <ul class="breadcrumb">
{{--                <li><a href="">{{trans('sw.dashboard')}}</a></li>--}}
                <li class="active"><a href="#">{{trans('sw.member_login')}}</a></li>
            </ul>
            <!-- BEGIN SIDEBAR & CONTENT -->
            <div class="row margin-bottom-40">
                <!-- BEGIN CONTENT -->
                <div class="col-md-12 col-sm-12">
                    <div class="content-page">
                        <div class="row margin-bottom-30">
                            <!-- BEGIN CAROUSEL -->
<div class="col-md-9 col-sm-9">
    <h1>{{trans('sw.member_login')}}</h1>
    <div class="content-form-page">
        <div class="row">
            <div class="col-md-7 col-sm-7">
                <form method="post" class="form-horizontal form-without-legend" role="form">
                    {{csrf_field()}}
                    <div class="form-group">
                        <label for="phone" class="col-lg-4 control-label">{{trans('sw.phone')}} <span class="require">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="phone" class="form-control" id="phone">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="code" class="col-lg-4 control-label">{{trans('sw.identification_code')}} <span class="require">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="code" class="form-control" id="code">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8 col-md-offset-4 padding-left-0 padding-top-20">
                            <button type="submit" class="btn btn-primary">{{trans('admin.login')}}</button>
                        </div>
                    </div>

                </form>
            </div>
            <div class="col-md-4 col-sm-4 pull-right">
                <div class="form-info">
                    <img src="{{$mainSettings->logo}}" style="width: auto;height: 120px;">
                </div>
            </div>
        </div>
    </div>
</div>

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
