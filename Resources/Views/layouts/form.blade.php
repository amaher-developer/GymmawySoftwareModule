@extends('software::layouts.master')
@section('styles')
{{--    <link href="{{asset('resources/assets/admin/global/plugins/fancybox/source/jquery.fancybox.css')}}"--}}
{{--          rel="stylesheet" type="text/css"/>--}}
{{--    <link href="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/blueimp-gallery/blueimp-gallery.min.css')}}"--}}
{{--          rel="stylesheet" type="text/css"/>--}}
{{--    <link href="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/css/jquery.fileupload.css')}}"--}}
{{--          rel="stylesheet" type="text/css"/>--}}
{{--    <link href="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/css/jquery.fileupload-ui.css')}}"--}}
{{--          rel="stylesheet" type="text/css"/>--}}

    <link href="{{asset('resources/assets/admin/global/plugins/bootstrap-select/css/bootstrap-select.min.css')}}"
          rel="stylesheet" type="text/css"/>

    <link rel="stylesheet" type="text/css"
          href="{{asset('resources/assets/admin/global/plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" type="text/css"
          href="{{asset('resources/assets/admin/global/plugins/select2/css/select2-bootstrap.min.css')}}">

    <link rel="stylesheet" type="text/css"
          href="{{asset('resources/assets/admin/custom/bootstrapValidator.css')}}"/>

{{--    <link rel="stylesheet" type="text/css"--}}
{{--          href="{{asset('resources/assets/admin/global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.css')}}"/>--}}

<style>
    .spinner-input {
        padding: inherit !important;
    }
    </style>
    @yield('sub_styles')
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
                    <h1 class="d-flex align-items-center text-gray-900 fw-bold my-1 fs-3">@yield('form_title')</h1>
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

        <div class="post d-flex flex-column-fluid" >
            
            <div  class="container-xxl">
                @include('generic::errors')
            </div>
        </div>
        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">

            {{-- <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title">
                    <div class="d-flex align-items-center my-1">
                        <i class="ki-outline ki-wallet fs-2 me-3"></i>
                        <span class="fs-4 fw-semibold text-gray-900">@yield('form_title')</span>
                    </div>
                </div>
            </div> --}}

            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl">
                @yield('page_body')
            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
    <!--end::Content-->











@endsection

@section('scripts')
{{--    <script src="{{asset('resources/assets/admin/global/plugins/fancybox/source/jquery.fancybox.pack.js')}}"--}}
{{--            type="text/javascript"></script>--}}
    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js')}}"
            type="text/javascript"></script>
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/js/vendor/tmpl.min.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/js/vendor/load-image.min.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/js/vendor/canvas-to-blob.min.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/blueimp-gallery/jquery.blueimp-gallery.min.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/js/jquery.iframe-transport.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/js/jquery.fileupload.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/js/jquery.fileupload-process.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/js/jquery.fileupload-image.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/js/jquery.fileupload-audio.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/js/jquery.fileupload-video.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/js/jquery.fileupload-validate.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('resources/assets/admin/global/plugins/jquery-file-upload/js/jquery.fileupload-ui.js')}}"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('resources/assets/admin/pages/scripts/form-fileupload.min.js')}}"--}}
{{--            type="text/javascript"></script>--}}
    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-select/js/bootstrap-select.min.js')}}"
            type="text/javascript"></script>
    <script src="{{asset('resources/assets/admin/pages/scripts/components-bootstrap-select.min.js')}}"
            type="text/javascript"></script>


{{--    <script type="text/javascript"--}}
{{--            src="{{asset('resources/assets/admin/global/plugins/select2/js/select2.full.min.js')}}"></script>--}}
{{--    <script type="text/javascript"--}}
{{--            src="{{asset('resources/assets/admin/global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.js')}}"></script>--}}

    @yield('sub_scripts')
@endsection
