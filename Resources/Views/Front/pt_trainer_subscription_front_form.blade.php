@extends('software::layouts.form')
@section('form_title') {{ @$title }} @endsection
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

@section('form_content')
    <form action="{{ route('sw.storePTTrainerSubscription') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::General Information-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.general_information') }}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.pt_subscriptions') }}</label>
                        <!--end::Label-->
                        <!--begin::Select-->
                        <select name="class_ids[]" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ trans('sw.select_pt_subscriptions') }}" multiple>
                            @foreach($subscriptions as $subscription)
                                <option value="{{ $subscription->id }}" @if(in_array($subscription->id, $selectedPTSubscriptions)) selected @endif>
                                    {{ $lang == 'ar' ? $subscription->name_ar : $subscription->name_en }}
                                </option>
                            @endforeach
                        </select>
                        <!--end::Select-->
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.reservation_details') }}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea name="reservation_details" class="form-control" rows="5" placeholder="{{ trans('sw.enter_reservation_details') }}"></textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::General Information-->

            <!--begin::Actions-->
            <div class="d-flex flex-stack pt-10">
                <!--begin::Wrapper-->
                <div class="me-2">
                    <a href="{{ route('sw.listPTTrainer') }}" class="btn btn-lg btn-light-primary me-3">{{ trans('sw.cancel') }}</a>
                </div>
                <!--end::Wrapper-->
                <!--begin::Wrapper-->
                <div>
                    <button type="submit" class="btn btn-lg btn-primary">
                        <span class="indicator-label">{{ trans('sw.save') }}</span>
                        <span class="indicator-progress">{{ trans('sw.please_wait') }}...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Actions-->
        </div>
    </form>
@endsection


