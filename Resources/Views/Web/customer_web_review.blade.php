@extends('software::Web.master')

@section('content')


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
