@extends('software::layouts.form')
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
@section('form_title') {{ @$title }} @endsection
@section('styles')
    <!---Internal Fileupload css-->

    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
    <link href="{{asset('/')}}resources/assets/admin/global/scripts/css/fileupload.css" rel="stylesheet"
          type="text/css"/>

        <style>
        .tag-orange {
            background-color: #fd7e14 !important;
            color: #fff;
        }
        .tag {
            color: #14112d;
            background-color: #ecf0fa;
            border-radius: 3px;
            padding: 0 .5rem;
            line-height: 2em;
            display: -ms-inline-flexbox;
            display: inline-flex;
            cursor: default;
            font-weight: 400;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        .tab-content>.tab-pane {
            padding-top: 20px;
        }
        .ckbox input{
            width: 20px;
            height: 20px;
        }
        .ckbox span{
            vertical-align: text-top;
        }
    </style>
@endsection
@section('page_body')


    <form method="post" action="{{route('sw.uploadExcelStore')}}" class="form-horizontal" role="form" enctype="multipart/form-data">
        {{csrf_field()}}
        <div class="form-group col-md-6">
            <label for="SubscribedClientsInputPhoto">{{ trans('sw.upload_excel_file')}}</label>

            <input
                   data-default-file="{{asset('uploads/settings/excel_icon.png')}}"
                   name="excel_data" type="file" class="dropify" data-height="200"
                   />
            <input type="hidden" name="excel_data" id="photo_camera">
        </div><!-- end photo div -->

        <div class="col-md-6">
            <label for="SubscribedClientsInputPhoto"><br/></label>

            <div class="note note-info">
                <h4 class="block">Info! Some Header Goes Here</h4>
                <p>
                    Duis mollis, est non commodo luctus, nisi erat porttitor ligula, mattis consectetur purus sit amet eget lacinia odio sem nec elit. Cras mattis consectetur purus sit amet fermentum.
                </p>
            </div>
        </div>

        <div class="form-body">


            <div class="form-actions" style="clear:both;">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <button type="submit" class="btn green">{{ trans('global.save')}}</button>
                        <input type="reset" class="btn default" value="{{ trans('admin.reset')}}">
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if(@session()->get('records'))
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-share font-blue-steel"></i>
                        <span class="caption-subject font-blue-steel ">Recent Activities</span>
                    </div>
                </div>

                <div class="portlet-body">
                    <div class="scroller" style="height: 300px;" data-always-visible="1" data-rail-visible="0">
                        <ul class="feeds">
                            @foreach(session()->get('records') as $record)
                            <li>
                                <div class="col1">
                                    <div class="cont">
                                        <div class="cont-col1">
                                            @if($record['success'])
                                            <div class="label label-sm label-success">
                                                <i class="fa fa-check"></i>
                                            </div>
                                            @else
                                                <div class="label label-sm label-danger">
                                                    <i class="fa fa-times"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="cont-col2">
                                            <div class="desc">
                                                {{$record['msg']}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach

                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endif

@endsection


@section('sub_scripts')
    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    <script src="{{asset('/')}}resources/assets/admin/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="{{asset('/')}}resources/assets/admin/pages/scripts/components-pickers.js"></script>

    <script type="text/javascript" src="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <script>

        jQuery(document).ready(function() {
            ComponentsPickers.init();
        });


    </script>


    <!--Internal Fileuploads js-->
    <script src="{{asset('/')}}resources/assets/admin/global/scripts/js/fileupload.js"></script>
    <script src="{{asset('/')}}resources/assets/admin/global/scripts/js/file-upload.js"></script>


    <script>
        $('.dropify-infos-message').html("{{ trans('sw.upload_image')}}");
        $('.dropify-message p:first').html("{{ trans('sw.upload_image')}}");
        $('.dropify-clear').html("{{ trans('sw.remove')}}");
    </script>

    <script>
        // showing modal with effect
        $('.modal-effect').on('click', function (e) {
            e.preventDefault();
            var effect = $(this).attr('data-effect');
            $('#modalCamera').addClass(effect);

        });
    </script>

@endsection
