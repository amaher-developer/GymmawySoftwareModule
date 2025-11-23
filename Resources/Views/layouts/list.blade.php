@extends('software::layouts.master')
@section('styles')
    <style>
        .table-striped thead tr th {
            padding-right: 2%;
        }
        .table-striped tbody tr td {
            vertical-align: middle;
        }
        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

    </style>
@endsection
@section('gym_shortcuts')

@endsection


@section('content')


    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <!--begin::Container-->
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
                <!--begin::Page title-->
                <div data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}" class="page-title d-flex align-items-center me-3 flex-wrap lh-1">
                    <!--begin::Title-->
                    <h1 class="d-flex align-items-center text-gray-900 fw-bold my-1 fs-3">@yield('list_title')</h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-200 border-start mx-4"></span>
                    <!--end::Separator-->
                    @yield('breadcrumb')
                </div>
                <!--end::Page title-->


                <!--begin::Actions-->
                <div class="d-flex align-items-center py-1">
                    @yield('list_add_button')
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Toolbar-->


        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl">
                @yield('page_body')
            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
    <!--end::Content-->




    <!--begin::Actions-->
    {{-- <div class="d-flex align-items-center py-1">

        @yield('list_add_button')
        <!--begin::Wrapper-->

    </div> --}}
    <!--end::Actions-->

@stop


@section('scripts')
    <script>
        $('[data-toggle="tooltip"]').tooltip();
        $(document).on('click', '.confirm_delete', function (event) {
            var $this = $(this);
            var tr = $this.closest('tr');
            event.preventDefault();
            url = $this.attr('href');
            let data_amount = $this.attr('data-swal-amount');
            
            let swal_config = {
                title: "{{trans('admin.are_you_sure')}}",
                text: $this.attr('data-swal-text'),
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "{{trans('admin.yes')}}",
                cancelButtonText: "{{trans('admin.no_cancel')}}",
                allowOutsideClick: false
            };
            
            // Add input field if refund amount is specified
            if(data_amount){
                swal_config.input = 'number';
                swal_config.inputValue = data_amount;
                swal_config.inputAttributes = {
                    min: 0,
                    step: 0.01
                };
                swal_config.inputValidator = function(value) {
                    if (!value || value < 0) {
                        return 'Please enter a valid amount!';
                    }
                };
            }
            
            Swal.fire(swal_config).then((result) => {
                if (result.isConfirmed) {
                    let amount = data_amount ? result.value : null;
                    
                    $.ajax({
                        url: url,
                        type: 'GET',
                        data: {'amount': amount},
                        success: function (response) {
                            Swal.fire({
                                title: "{{trans('admin.completed')}}",
                                text: "{{trans('admin.completed_successfully')}}",
                                icon: "success"
                            }).then(() => {
                                // Remove the table row with fade effect
                                tr.fadeOut(300, function() {
                                    $(this).remove();
                                });
                            });
                        },
                        error: function (request, error) {
                            Swal.fire({
                                title: "{{trans('operation_failed')}}",
                                text: "{{trans('admin.something_wrong')}}",
                                icon: "error"
                            });
                            console.error("Request: " + JSON.stringify(request));
                            console.error("Error: " + JSON.stringify(error));
                        }
                    });
                } else if (result.isDismissed) {
                    Swal.fire({
                        title: "{{trans('admin.cancelled')}}",
                        text: "{{trans('admin.everything_still')}}",
                        icon: "info"
                    });
                }
            });
        });



    </script>
@endsection




