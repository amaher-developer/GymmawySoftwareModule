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
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listBanner') }}" class="text-muted text-hover-primary">{{ trans('sw.banners')}}</a>
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
@endsection
@section('page_body')
    <!--begin::Banner Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}

        <!--begin::Aside column-->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <!--begin::Thumbnail settings-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('admin.image')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body text-center pt-0">
                    <!--begin::Image input-->
                    <div class="image-input image-input-outline w-100" data-kt-image-input="true" style="background-image: url({{asset('uploads/settings/default.jpg')}})">
                        <!--begin::Preview existing avatar-->
                        <div class="image-input-wrapper w-100" style="height: 100px; background-size: contain; background-repeat: no-repeat; background-position: center; background-image: url({{@$banner->image ?? asset('uploads/settings/default.jpg')}})"></div>
                        <!--end::Preview existing avatar-->
                        <!--begin::Label-->
                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="{{ trans('sw.change_image')}}">
                            <i class="ki-outline ki-pencil fs-7"></i>
                            <!--begin::Inputs-->
                            <input type="file" name="image" accept=".png, .jpg, .jpeg" @if(!@$banner->image) required @endif />
                            <input type="hidden" name="avatar_remove" />
                            <!--end::Inputs-->
                        </label>
                        <!--end::Label-->
                        <!--begin::Cancel-->
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="{{ trans('sw.cancel')}}">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </span>
                        <!--end::Cancel-->
                        <!--begin::Remove-->
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="{{ trans('sw.remove_image')}}">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </span>
                        <!--end::Remove-->
                    </div>
                    <!--end::Image input-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Thumbnail settings-->
        </div>
        <!--end::Aside column-->
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Banner Details-->
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
                        <label class="required form-label">{{ trans('sw.title')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="title" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_title')}}" 
                               value="{{ old('title', $banner->title) }}" 
                               id="title" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.content')}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea name="content" id="kt_docs_ckeditor_classic">{{ old('content', @$banner->content) }}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->

                    <!--begin::Row-->
                    <div class="row mb-10">
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{ trans('sw.phone')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="phone" class="form-control mb-2"
                                   placeholder="{{ trans('sw.enter_phone')}}"
                                   value="{{ old('phone', $banner->phone) }}"
                                   id="phone" />
                            <!--end::Input-->
                        </div>
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{ trans('sw.url')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="url" class="form-control mb-2"
                                   placeholder="{{ trans('sw.enter_url')}}"
                                   value="{{ old('url', $banner->url) }}"
                                   id="url" />
                            <!--end::Input-->
                        </div>
                    </div>
                    <!--end::Row-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.visible_invisible')}}</label>
                        <!--end::Label-->
                        <!--begin::Checkbox group-->
                        <div class="d-flex gap-5">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="inlineCheckbox21" 
                                       name="is_mobile" value="1" @if(@$banner->is_mobile) checked @endif />
                                <label class="form-check-label" for="inlineCheckbox21">
                                    {{ trans('sw.mobile')}}
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="inlineCheckbox22" 
                                       name="is_web" value="1" @if(@$banner->is_web) checked @endif />
                                <label class="form-check-label" for="inlineCheckbox22">
                                    {{ trans('sw.web')}}
                                </label>
                            </div>
                        </div>
                        <!--end::Checkbox group-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Banner Details-->
            
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
    <!--end::Banner Form-->
@endsection


@section('sub_scripts')

<!--CKEditor Build Bundles:: Only include the relevant bundles accordingly-->
<script src="{{asset('resources/assets/new_front/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>
<script src="{{asset('resources/assets/new_front/plugins/custom/ckeditor/ckeditor-inline.bundle.js')}}"></script>
<script src="{{asset('resources/assets/new_front/plugins/custom/ckeditor/ckeditor-balloon.bundle.js')}}"></script>
<script src="{{asset('resources/assets/new_front/plugins/custom/ckeditor/ckeditor-balloon-block.bundle.js')}}"></script>
<script src="{{asset('resources/assets/new_front/plugins/custom/ckeditor/ckeditor-document.bundle.js')}}"></script>
<script>
    $(document).ready(function() {
        // Init KTImageInput
        KTImageInput.createInstances();

        @php
            $lang = app()->getLocale();
        @endphp

        ClassicEditor
            .create(document.querySelector('#kt_docs_ckeditor_classic') @if($lang == 'ar') , { language: 'ar' } @endif )
            .then(editor => {
                console.log(editor);
            })
            .catch(error => {
                console.error(error);
            });
    });
</script>
@endsection


