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
            <a href="{{ route('sw.listGroupDiscount') }}" class="text-muted text-hover-primary">{{ trans('sw.group_discounts')}}</a>
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
    <!--begin::Group Discount Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Discount Details-->
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
                               value="{{ old('name_ar', $group_discount->name_ar) }}" 
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
                               value="{{ old('name_en', $group_discount->name_en) }}" 
                               id="name_en" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.discount_type')}}</label>
                        <!--end::Label-->
                        <!--begin::Radio group-->
                        <div class="d-flex gap-5">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" name="type" id="optionsRadios25" 
                                       value="0" @if(old('type', $group_discount->type) == 0) checked @endif required />
                                <label class="form-check-label" for="optionsRadios25">
                                    {{ trans('sw.amount2')}}
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" name="type" id="optionsRadios26" 
                                       value="1" @if(old('type', $group_discount->type) == 1) checked @endif required />
                                <label class="form-check-label" for="optionsRadios26">
                                    {{ trans('sw.percentage2')}}
                                </label>
                            </div>
                        </div>
                        <!--end::Radio group-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.value')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input id="amount" value="{{ old('amount', $group_discount->amount) }}" step="0.01" @if($group_discount->type == 1) max="100" @endif
                               placeholder="{{ trans('sw.enter_value')}}"
                               name="amount" type="number" class="form-control mb-2" required>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.visible_invisible')}}</label>
                        <!--end::Label-->
                        <!--begin::Checkboxes-->
                        <div class="d-flex flex-wrap gap-5">
                            <!--begin::Checkbox-->
                            <label class="form-check form-check-custom form-check-solid">
                                <input type="checkbox" id="inlineCheckbox21" name="is_member" value="1" 
                                       class="form-check-input" @if(old('is_member', $group_discount->is_member)) checked="" @endif />
                                <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.subscribed_clients')}}</span>
                            </label>
                            <!--end::Checkbox-->
                            
                            <!--begin::Checkbox-->
                            <label class="form-check form-check-custom form-check-solid">
                                <input type="checkbox" id="inlineCheckbox22" name="is_non_member" value="1" 
                                       class="form-check-input" @if(old('is_non_member', $group_discount->is_non_member)) checked="" @endif />
                                <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.daily_clients')}}</span>
                            </label>
                            <!--end::Checkbox-->
                            
                            <!--begin::Checkbox-->
                            <label class="form-check form-check-custom form-check-solid">
                                <input type="checkbox" id="inlineCheckbox23" name="is_pt_member" value="1" 
                                       class="form-check-input" @if(old('is_pt_member', $group_discount->is_pt_member)) checked="" @endif />
                                <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.pt')}}</span>
                            </label>
                            <!--end::Checkbox-->
                            
                            <!--begin::Checkbox-->
                            <label class="form-check form-check-custom form-check-solid">
                                <input type="checkbox" id="inlineCheckbox24" name="is_training_member" value="1" 
                                       class="form-check-input" @if(old('is_training_member', $group_discount->is_training_member)) checked="" @endif />
                                <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.training_plans')}}</span>
                            </label>
                            <!--end::Checkbox-->
                            
                            <!--begin::Checkbox-->
                            <label class="form-check form-check-custom form-check-solid">
                                <input type="checkbox" id="inlineCheckbox25" name="is_store" value="1" 
                                       class="form-check-input" @if(old('is_store', $group_discount->is_store)) checked="" @endif />
                                <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.store')}}</span>
                            </label>
                            <!--end::Checkbox-->
                        </div>
                        <!--end::Checkboxes-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Discount Details-->

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
<script>

    $("#optionsRadios25, #optionsRadios26").change(function () {
        if ($("#optionsRadios26").is(":checked")) {
            $('#amount').attr('max', 100);
        }else{
            $('#amount').removeAttr('max');
        }
        $('#amount').val(0);
    });

</script>
@endsection
