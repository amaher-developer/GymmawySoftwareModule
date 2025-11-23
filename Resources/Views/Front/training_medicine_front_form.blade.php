@extends('software::layouts.form')
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
@section('form_title') {{ @$title }} @endsection
@section('styles')

    <link rel="stylesheet" type="text/css"
          href="{{asset('resources/assets/new_front/global/plugins/bootstrap-summernote/summernote.css')}}">

<style>
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
</style>
@endsection
@section('page_body')
    <!--begin::Training Medicine Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Medicine Details-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{$title}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name_in_arabic')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="name_ar" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_name_in_arabic')}}" 
                               value="{{ old('name_ar', $medicine->name_ar) }}" 
                               id="name_ar" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name_in_english')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="name_en" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_name_in_english')}}" 
                               value="{{ old('name_en', $medicine->name_en) }}" 
                               id="name_en" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.default_dose')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="dose" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_name_in_english')}}" 
                               value="{{ old('dose', $medicine->dose) }}" 
                               id="dose" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Medicine Details-->
            
            <!--begin::Form Actions-->
            <div class="d-flex justify-content-end">
                <!--begin::Button-->
                <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                <!--end::Button-->
                <!--begin::Button-->
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">{{ trans('global.save')}}</span>
                    <span class="indicator-progress">Please wait... 
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
                <!--end::Button-->
            </div>
            <!--end::Form Actions-->
        </div>
        <!--end::Main column-->
    </form>
    <!--end::Training Medicine Form-->
@endsection


@section('sub_scripts')

    <script type="text/javascript"
            src="{{asset('resources/assets/new_front/global/plugins/bootstrap-summernote/summernote.min.js')}}"></script>
    <script>

        $('#member_id').keyup(function () {
            let member_id = $('#member_id').val();

            $.get("{{route('sw.getStoreMemberAjax')}}", {  member_id: member_id },
                function(result){
                    if(result){
                        $('#store_member_name').html(result.name);
                        $('#store_member_phone').html(result.phone);
                    }else{
                        $('#store_member_name').html('-');
                        $('#store_member_phone').html('-');
                    }
                }
            );
        });
    </script>
    <script>
        jQuery(document).ready(function() {
            ComponentsPickers.init();
        });

        $('.summernote-textarea').summernote({
            toolbar: [
                // [groupName, [list of button]]
                ['insert', ['link', 'table', 'hr', 'picture']],
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
            ],
            height: 200,
            focus: true,
            callbacks: {
                // onImageUpload: function (files, editor, welEditable) {
                //     // upload image to server and create imgNode...
                //     sendFile(files[0], editor, welEditable);
                // }
            }
        });
        $('.summernote-textarea-ar').summernote({
            popover: {
                image: [
                    ['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
                    ['float', ['floatLeft', 'floatRight', 'floatNone']],
                    ['remove', ['removeMedia']]
                ]
            },
            toolbar: [
                // [groupName, [list of button]]
                ['insert', ['link', 'table', 'hr']],
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
            ],
            height: 200,
            focus: true,
            direction: 'rtl',
            callbacks: {
                // onImageUpload: function (files, editor, welEditable) {
                //     // upload image to server and create imgNode...
                //     sendFile(files[0], editor, welEditable);
                // }
            }
        });
    </script>
@endsection


