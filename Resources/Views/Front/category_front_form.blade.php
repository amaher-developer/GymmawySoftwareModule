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
            <a href="{{ route('sw.listCategory') }}" class="text-muted text-hover-primary">{{ trans('sw.categories')}}</a>
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
@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/admin/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/admin/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}"/>
    <style>
        /*.input-small {*/
        /*    width: 200px !important;*/
        /*}*/
    </style>
@endsection
@section('form_title') {{ @$title }} @endsection
@section('page_body')
    <!--begin::Category Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Category Details-->
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
                               value="{{ old('name_ar', $category->name_ar) }}" 
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
                               value="{{ old('name_en', $category->name_en) }}" 
                               id="name_en" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.visible_invisible')}}</label>
                        <!--end::Label-->
                        <!--begin::Checkbox group-->
                        <div class="d-flex flex-wrap gap-5">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="inlineCheckbox21" 
                                       name="is_subscription" value="1" @if(old('is_subscription', $category->is_subscription)) checked @endif />
                                <label class="form-check-label" for="inlineCheckbox21">
                                    {{ trans('sw.subscriptions')}}
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="inlineCheckbox22" 
                                       name="is_activity" value="1" @if(old('is_activity', $category->is_activity)) checked @endif />
                                <label class="form-check-label" for="inlineCheckbox22">
                                    {{ trans('sw.activities')}}
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="inlineCheckbox23" 
                                       name="is_pt_subscription" value="1" @if(old('is_pt_subscription', $category->is_pt_subscription)) checked @endif />
                                <label class="form-check-label" for="inlineCheckbox23">
                                    {{ trans('sw.pt')}}
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="inlineCheckbox24" 
                                       name="is_training" value="1" @if(old('is_training', $category->is_training)) checked @endif />
                                <label class="form-check-label" for="inlineCheckbox24">
                                    {{ trans('sw.training_plans')}}
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="inlineCheckbox25" 
                                       name="is_store" value="1" @if(old('is_store', $category->is_store)) checked @endif />
                                <label class="form-check-label" for="inlineCheckbox25">
                                    {{ trans('sw.store')}}
                                </label>
                            </div>
                        </div>
                        <!--end::Checkbox group-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Category Details-->
            
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
    <!--end::Category Form-->
@endsection
@section('scripts')
    <script type="text/javascript" src="{{asset('resources/assets/admin/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('resources/assets/admin/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js')}}"></script>

    <script type="text/javascript" src="{{asset('resources/assets/admin/global/plugins/fuelux/js/spinner.min.js')}}"></script>
  <script>

        $('.timepicker-24').timepicker({
            autoclose: true,
            minuteStep: 5,
            showSeconds: false,
            showMeridian: false
        });
    </script>
    <script>
        $("#gym_image").change(function () {
            let input = this;
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        });
        $('#spinner_price').spinner({value:0, step: 1, min: 1, max: 10000 });
        $('#spinner_reservation_limit').spinner({value:0, step: 1, min: 1, max: 1000 });
        $('#spinner_reservation_period').spinner({value:0, step: 1, min: 1, max: 1000 });

    </script>

@endsection
