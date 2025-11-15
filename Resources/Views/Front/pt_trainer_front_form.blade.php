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
            <a href="{{ route('sw.listPTTrainer') }}" class="text-muted text-hover-primary">{{ trans('sw.pt_trainers')}}</a>
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

            margin: 0 15px;
        }
        .ckbox input{
             width: 20px;
             height: 20px;
         }
        .ckbox span{
            vertical-align: text-top;
        }
        .sch-day-name {
            color: black;
            font-weight: bold;
        }
    </style>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
@endsection
@section('page_body')
    <!--begin::PT Trainer Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Aside column-->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <!--begin::Thumbnail settings-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{ trans('sw.the_image')}}</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body text-center pt-0">
                    <!--begin::Image input-->
                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url({{asset('uploads/settings/default.jpg')}})">
                        <!--begin::Preview existing avatar-->
                        <div class="image-input-wrapper w-125px h-125px" style="background-image: url({{$trainer->image ?? asset('uploads/settings/default.jpg')}})"></div>
                        <!--end::Preview existing avatar-->
                        <!--begin::Label-->
                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="{{ trans('sw.change_image')}}">
                            <i class="ki-outline ki-pencil fs-7"></i>
                            <!--begin::Inputs-->
                            <input type="file" name="image" accept=".png, .jpg, .jpeg" />
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
                    <!--begin::Hint-->
                    <div class="text-muted fs-7">{{ trans('sw.set_the_trainer_image')}}</div>
                    <!--end::Hint-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Thumbnail settings-->
        </div>
        <!--end::Aside column-->
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Trainer Details-->
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
                    <div class="row">
                        <div class="col-lg-6">
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="name" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_name')}}" 
                               value="{{ old('name', $trainer->name) }}" 
                                       required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.phone')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                    <input id="phone" value="{{ old('phone', $trainer->phone) }}"
                           placeholder="{{ trans('sw.enter_phone')}}"
                           name="phone" type="text" class="form-control" required>
                                <!--end::Input-->
                </div>
                            <!--end::Input group-->
            </div>
                        <div class="col-lg-6">
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">{{ trans('sw.bonus_amount')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                    <input id="price" value="{{ old('price', $trainer->price) }}"
                           placeholder="{{ trans('sw.enter_price')}}"
                           name="price" type="number" step="0.01" min="0"  class="form-control" >
                                <!--end::Input-->
                </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.trainer_percentage_for_member')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                    <input id="percentage" value="{{ old('percentage', $trainer->percentage) ?? 0 }}"
                           placeholder="{{ trans('sw.enter_percentage')}}"
                           name="percentage" type="number" max="100" min="0" class="form-control" required>
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                        </div>
                </div>
            </div>
                <!--end::Card body-->
            </div>
            <!--end::Trainer Details-->

            <!--begin::Form actions-->
            <div class="d-flex justify-content-end">
                <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-check fs-2"></i>
                    {{ trans('global.save')}}
                </button>
            </div>
            <!--end::Form actions-->
        </div>
    </form>


@endsection

@section('scripts')
    @parent
    <script type="text/javascript" src="{{asset('resources/assets/admin/global/scripts/js/fileupload.js')}}"></script>
    <script type="text/javascript" src="{{asset('resources/assets/admin/global/scripts/js/file-upload.js')}}"></script>
    <script>
        $(document).ready(function () {
            if (typeof KTImageInput !== 'undefined') {
                KTImageInput.createInstances();
            }
        });
    </script>
@endsection
