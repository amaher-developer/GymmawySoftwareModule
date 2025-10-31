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
          href="{{asset('resources/assets/admin/global/plugins/bootstrap-summernote/summernote.css')}}">
@endsection
@section('page_body')
    <!--begin::Training Plan Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Plan Details-->
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
                        <label class="required form-label">{{ trans('sw.plan_type')}}</label>
                        <!--end::Label-->
                        <!--begin::Radio group-->
                        <div class="d-flex gap-5">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" name="type" id="optionsRadios4"
                                       value="{{\Modules\Software\Classes\TypeConstants::TRAINING_PLAN_TYPE}}" 
                                       @if(old('type', $plan->type) == \Modules\Software\Classes\TypeConstants::TRAINING_PLAN_TYPE) checked @endif />
                                <label class="form-check-label" for="optionsRadios4">
                                    {{ trans('sw.plan_training')}}
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" name="type" id="optionsRadios5"
                                       value="{{\Modules\Software\Classes\TypeConstants::DIET_PLAN_TYPE}}" 
                                       @if(old('type', $plan->type) == \Modules\Software\Classes\TypeConstants::DIET_PLAN_TYPE) checked @endif />
                                <label class="form-check-label" for="optionsRadios5">
                                    {{ trans('sw.plan_diet')}}
                                </label>
                            </div>
                        </div>
                        <!--end::Radio group-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.title')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="title" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_title')}}" 
                               value="{{ old('title', $plan->title) }}" 
                               id="title" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.plan')}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea name="content" class="form-control mb-2 summernote-textarea-ar" 
                                  placeholder="{{ trans('sw.enter_plan')}}" 
                                  id="content" rows="10" required>{!! old('content', $plan->content) !!}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Plan Details-->
            
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
    <!--end::Training Plan Form-->
@endsection


@section('sub_scripts')


    <script type="text/javascript"
            src="{{asset('resources/assets/admin/global/plugins/bootstrap-summernote/summernote.min.js')}}"></script>
    <script>
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
