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
            <a href="{{ route('sw.listUserPermission') }}" class="text-muted text-hover-primary">{{ trans('sw.permission_groups')}}</a>
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

<style>
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
    <!--begin::Form-->
    <form method="post" action="" class="form">
        {{csrf_field()}}
        @php($permission_group->permissions = (array)($permission_group->permissions ?? []))
        
        <div class="row g-7">
            <!--begin::Basic Info-->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ trans('sw.basic_information')}}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-10 fv-row">
                                    <label class="required form-label">{{ trans('sw.title_in_arabic')}}</label>
                                    <input type="text" name="title_ar" class="form-control" 
                                           placeholder="{{ trans('sw.enter_title_in_arabic')}}" 
                                           value="{{ old('title_ar', $permission_group->title_ar) }}" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-10 fv-row">
                                    <label class="required form-label">{{ trans('sw.title_in_english')}}</label>
                                    <input type="text" name="title_en" class="form-control" 
                                           placeholder="{{ trans('sw.enter_title_in_english')}}" 
                                           value="{{ old('title_en', $permission_group->title_en) }}" required />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Basic Info-->

            @include('software::layouts.permissions')

            <!--begin::Form Actions-->
            <div class="col-12">
                <div class="d-flex justify-content-end">
                    <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-outline ki-check fs-2"></i>
                        {{ trans('global.save')}}
                    </button>
                </div>
            </div>
            <!--end::Form Actions-->
        </div>
    </form>
    <!--end::Form-->
@endsection



 

