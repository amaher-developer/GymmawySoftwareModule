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
    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
        <!--begin::General Information-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>{{ trans('sw.subscription_details') }}</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-10 fv-row">
                            <label class="form-label">{{ trans('sw.subscription_name') }}</label>
                            <div class="fw-bold text-gray-900">{{ $subscription->name_en }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-10 fv-row">
                            <label class="form-label">{{ trans('sw.subscription_name_ar') }}</label>
                            <div class="fw-bold text-gray-900">{{ $subscription->name_ar }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-10 fv-row">
                            <label class="form-label">{{ trans('sw.price') }}</label>
                            <div class="fw-bold text-gray-900">{{ $subscription->price }} {{ trans('sw.currency') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-10 fv-row">
                            <label class="form-label">{{ trans('sw.period') }}</label>
                            <div class="fw-bold text-gray-900">{{ $subscription->period }} {{ trans('sw.days') }}</div>
                        </div>
                    </div>
                </div>

                @if($subscription->trainer)
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-10 fv-row">
                            <label class="form-label">{{ trans('sw.trainer') }}</label>
                            <div class="fw-bold text-gray-900">{{ $subscription->trainer->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-10 fv-row">
                            <label class="form-label">{{ trans('sw.trainer_phone') }}</label>
                            <div class="fw-bold text-gray-900">{{ $subscription->trainer->phone ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                @endif

                @if($subscription->content_en || $subscription->content_ar)
                <div class="row">
                    <div class="col-12">
                        <div class="mb-10 fv-row">
                            <label class="form-label">{{ trans('sw.description') }}</label>
                            <div class="fw-bold text-gray-900">
                                @if($lang == 'ar' && $subscription->content_ar)
                                    {{ $subscription->content_ar }}
                                @elseif($subscription->content_en)
                                    {{ $subscription->content_en }}
                                @else
                                    {{ $subscription->content_ar ?: $subscription->content_en }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <!--end::Card body-->
        </div>
        <!--end::General Information-->

        <!--begin::Actions-->
        <div class="d-flex flex-stack pt-10">
            <!--begin::Wrapper-->
            <div class="me-2">
                <a href="{{ route('sw.listPTTrainer') }}" class="btn btn-lg btn-light-primary me-3">{{ trans('sw.back') }}</a>
            </div>
            <!--end::Wrapper-->
            <!--begin::Wrapper-->
            <div>
                @if(in_array('editPTTrainerSubscription', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{ route('sw.editPTTrainerSubscription', $subscription->id) }}" class="btn btn-lg btn-primary">
                        <span class="indicator-label">{{ trans('sw.edit') }}</span>
                    </a>
                @endif
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Actions-->
    </div>
@endsection


