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
            <a href="{{ route('sw.listMoneyBoxType') }}" class="text-muted text-hover-primary">{{ trans('sw.money_box_types')}}</a>
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
    <!--begin::Warning Alert-->
    <div class="alert alert-warning mb-10">
        <b>{{ trans('sw.warning')}}:</b> {{ trans('sw.warning_setting_msg')}}
    </div>
    <!--end::Warning Alert-->
    
    <!--begin::Money Box Type Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Money Box Type Details-->
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
                               value="{{ old('name_ar', $money_box_type->name_ar) }}" 
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
                               value="{{ old('name_en', $money_box_type->name_en) }}" 
                               id="name_en" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.operation_type')}}</label>
                        <!--end::Label-->
                        <!--begin::Radio group-->
                        <div class="d-flex gap-5">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" name="operation_type" id="optionsRadios25" 
                                       value="0" @if(old('operation_type', $money_box_type->operation_type) == 0) checked @endif required />
                                <label class="form-check-label" for="optionsRadios25">
                                    {{ trans('sw.add_to_money_box')}}
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" name="operation_type" id="optionsRadios26" 
                                       value="1" @if(old('operation_type', $money_box_type->operation_type) == 1) checked @endif required />
                                <label class="form-check-label" for="optionsRadios26">
                                    {{ trans('sw.withdraw_from_money_box')}}
                                </label>
                            </div>
                        </div>
                        <!--end::Radio group-->
                        
                        <!--begin::Radio option-->
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" name="operation_type" id="optionsRadios27" 
                                   value="2" @if(old('operation_type', $money_box_type->operation_type) == 2) checked="" @endif required />
                            <label class="form-check-label" for="optionsRadios27">
                                {{ trans('sw.withdraw_earning')}}
                            </label>
                        </div>
                        <!--end::Radio option-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Money Box Type Details-->


{{--            <div class="form-group col-md-12">--}}
{{--                <label class="col-md-3  control-label">{{ trans('sw.calculate_process')}} <span class="required"></span></label>--}}
{{--                <div class="col-md-9">--}}

{{--                    <div class="radio-list col-md-12">--}}

{{--                        <label class="radio-inline col-md-3">--}}
{{--                            <div class="radio" id="uniform-optionsRadios25">--}}
{{--                                <span class="">--}}
{{--                                    <input type="radio" name="payment_type"  @if(old('payment_type', $money_box_type->payment_type) == 0) checked="" @endif id="optionsRadios25" value="0" required=""> {{ trans('sw.sum').' (+)'}}--}}
{{--                                </span>--}}
{{--                            </div>--}}
{{--                        </label>--}}
{{--                        <label class="radio-inline col-md-3">--}}
{{--                            <div class="radio" id="uniform-optionsRadios26">--}}
{{--                                <span class="checked">--}}
{{--                                    <input type="radio" name="payment_type" @if(old('payment_type', $money_box_type->payment_type) == 1) checked="" @endif id="optionsRadios26" value="1" required=""> {{ trans('sw.difference').' (-)'}}--}}
{{--                                </span>--}}
{{--                            </div>--}}
{{--                        </label>--}}

{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}



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


@section('sub_scripts')

@endsection


