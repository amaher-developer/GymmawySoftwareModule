@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">
                <i class="ki-outline ki-home fs-6 text-muted"></i>
            </a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-muted">{{ trans('sw.training') }}</li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listTrainingMedicine') }}" class="text-muted text-hover-primary">{{ trans('sw.training_medicines') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
    </ul>
@endsection

@section('actions')
    <a href="{{ route('sw.listTrainingMedicine') }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-arrow-left fs-3"></i> {{ trans('sw.back_to_list') }}
    </a>
@endsection

@section('content')
<div class="row g-7">
    <!--begin::Left Column - Form Instructions-->
    <div class="col-lg-4 col-xl-3">
        <div class="card card-flush mb-5">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold m-0">{{ trans('sw.instructions') }}</h3>
                </div>
            </div>
            <div class="card-body pt-0">
                 <div class="d-flex align-items-start mb-7">
                     <div class="symbol symbol-40px me-5">
                         <span class="symbol-label bg-light-primary">
                             <i class="ki-outline ki-information-5 fs-1 text-primary"></i>
                         </span>
                     </div>
                     <div class="flex-grow-1">
                         <div class="fw-bold text-gray-800 mb-1">{{ trans('sw.about_medicine_form') }}</div>
                         <div class="text-muted fs-7">{{ trans('sw.medicine_form_description_new') }}</div>
                     </div>
                 </div>

                <div class="separator separator-dashed my-5"></div>

                <div class="d-flex align-items-start mb-7">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-light-success">
                            <i class="ki-outline ki-check fs-1 text-success"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-gray-800 mb-1">{{ trans('sw.required_fields') }}</div>
                        <div class="text-muted fs-7">{{ trans('sw.medicine_required_fields_desc') }}</div>
                    </div>
                </div>

                <div class="separator separator-dashed my-5"></div>

                 <div class="d-flex align-items-start">
                     <div class="symbol symbol-40px me-5">
                         <span class="symbol-label bg-light-warning">
                             <i class="ki-outline ki-flask fs-1 text-warning"></i>
                         </span>
                     </div>
                     <div class="flex-grow-1">
                         <div class="fw-bold text-gray-800 mb-1">{{ trans('sw.dosage_per_member') }}</div>
                         <div class="text-muted fs-7">{{ trans('sw.dosage_per_member_desc') }}</div>
                     </div>
                 </div>
            </div>
        </div>

        <!--begin::Quick Tips-->
        <div class="card card-flush">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold m-0">{{ trans('sw.quick_tips') }}</h3>
                </div>
            </div>
            <div class="card-body pt-0">
                 <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                     <i class="ki-outline ki-information-5 fs-2tx text-primary me-4"></i>
                     <div class="d-flex flex-stack flex-grow-1">
                         <div class="fw-semibold">
                             <div class="fs-6 text-gray-700">{{ trans('sw.medicine_tips_new') }}</div>
                         </div>
                     </div>
                 </div>
            </div>
        </div>
        <!--end::Quick Tips-->
    </div>
    <!--end::Left Column-->

    <!--begin::Right Column - Form-->
    <div class="col-lg-8 col-xl-9">
        <div class="card card-flush">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="fw-bold">{{ $medicine->id ? trans('sw.edit_medicine') : trans('sw.add_medicine') }}</h2>
                </div>
            </div>
            <div class="card-body pt-0">
                <form action="{{ $medicine->id ? route('sw.editTrainingMedicine', $medicine->id) : route('sw.createTrainingMedicine') }}" method="POST">
                    @csrf

                    <!--begin::Section 1 - Basic Information-->
                    <div class="mb-10">
                        <div class="d-flex align-items-center mb-5">
                            <div class="symbol symbol-40px me-4">
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-outline ki-pill fs-2 text-primary"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h3 class="fw-bold mb-1">{{ trans('sw.basic_information') }}</h3>
                                <div class="text-muted fs-7">{{ trans('sw.medicine_basic_info_desc') }}</div>
                            </div>
                        </div>

                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label required">{{ trans('sw.medicine_name_en') }}</label>
                                <input type="text" name="name_en" value="{{ old('name_en', $medicine->name_en) }}" 
                                       class="form-control form-control-lg @error('name_en') is-invalid @enderror" 
                                       placeholder="{{ trans('sw.enter_medicine_name_en') }}" required />
                                @error('name_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="ki-outline ki-information-5 fs-6"></i>
                                    {{ trans('sw.medicine_name_en_hint') }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">{{ trans('sw.medicine_name_ar') }}</label>
                                <input type="text" name="name_ar" value="{{ old('name_ar', $medicine->name_ar) }}" 
                                       class="form-control form-control-lg @error('name_ar') is-invalid @enderror" 
                                       placeholder="{{ trans('sw.enter_medicine_name_ar') }}" required />
                                @error('name_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="ki-outline ki-information-5 fs-6"></i>
                                    {{ trans('sw.medicine_name_ar_hint') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Section 1-->

                    <div class="separator separator-dashed my-10"></div>

                    <!--begin::Section 2 - Status & Settings-->
                    <div class="mb-10">
                        <div class="d-flex align-items-center mb-5">
                            <div class="symbol symbol-40px me-4">
                                <span class="symbol-label bg-light-success">
                                    <i class="ki-outline ki-toggle-on fs-2 text-success"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h3 class="fw-bold mb-1">{{ trans('sw.status_settings') }}</h3>
                                <div class="text-muted fs-7">{{ trans('sw.medicine_status_settings_desc') }}</div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info d-flex align-items-center mb-5">
                            <i class="ki-outline ki-information-5 fs-2x me-3"></i>
                            <div class="flex-grow-1 fs-7">
                                <strong>{{ trans('sw.note') }}:</strong> {{ trans('sw.medicine_dose_note') }}
                            </div>
                        </div>

                        <div class="card border border-gray-300">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="form-check form-switch form-check-custom form-check-solid me-5">
                                        <input class="form-check-input h-30px w-50px" type="checkbox" name="status" value="1" 
                                               id="medicine_status" {{ old('status', $medicine->status) ? 'checked' : '' }} />
                                        <label class="form-check-label fs-5 fw-bold" for="medicine_status">
                                            {{ trans('sw.active') }}
                                        </label>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="text-gray-700 fs-6">
                                            <i class="ki-outline ki-information-5 fs-5 text-primary me-2"></i>
                                            {{ trans('sw.medicine_status_hint') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Section 3-->

                    <!--begin::Actions-->
                    <div class="separator separator-dashed my-10"></div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('sw.listTrainingMedicine') }}" class="btn btn-light btn-lg">
                            <i class="ki-outline ki-cross fs-2"></i>
                            {{ trans('sw.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="ki-outline ki-check fs-2"></i>
                            {{ $medicine->id ? trans('sw.update_medicine') : trans('sw.save_medicine') }}
                        </button>
                    </div>
                    <!--end::Actions-->
                </form>
            </div>
        </div>
    </div>
    <!--end::Right Column-->
</div>
@endsection


