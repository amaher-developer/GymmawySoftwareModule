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
            <a href="{{ route('sw.loyalty_campaigns.index') }}" class="text-muted text-hover-primary">{{ trans('sw.loyalty_campaigns_list')}}</a>
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
@section('page_body')
    <!--begin::Loyalty Campaign Form-->
    <form method="post" action="{{ isset($campaign) ? route('sw.loyalty_campaigns.update', $campaign->id) : route('sw.loyalty_campaigns.store') }}" class="form d-flex flex-column flex-lg-row">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            
            <!--begin::Basic Information Section-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="bi bi-info-circle me-2"></i>{{ trans('sw.basic_information')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                   
                    <div class="row g-5">
                        <!--begin::Campaign Name-->
                        <div class="col-md-8">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.campaign_name')}}</label>
                                <input type="text" name="name" class="form-control" 
                                       placeholder="{{ trans('sw.enter_campaign_name')}}" 
                                       value="{{ old('name', $campaign->name ?? '') }}" 
                                       required />
                                <div class="form-text">{{ trans('sw.loyalty_campaign_name_help') }}</div>
                                @error('name')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--end::Campaign Name-->
                        
                        <!--begin::Multiplier-->
                        <div class="col-md-4">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.multiplier')}}</label>
                                <input type="number" step="0.01" name="multiplier" class="form-control" 
                                       placeholder="2.00" 
                                       value="{{ old('multiplier', $campaign->multiplier ?? '2.00') }}" 
                                       required />
                                <div class="form-text text-primary">
                                    <i class="ki-outline ki-information-2 fs-6"></i>
                                    {{ trans('sw.loyalty_multiplier_help') }}
                                </div>
                                @error('multiplier')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--end::Multiplier-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Basic Information Section-->

            <!--begin::Campaign Period Section-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="bi bi-calendar-range me-2"></i>{{ trans('sw.campaign_period')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                   
                    <div class="row g-5">
                        <!--begin::Start Date-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.start_date')}}</label>
                                <input type="datetime-local" name="start_date" class="form-control" 
                                       value="{{ old('start_date', isset($campaign) ? $campaign->start_date->format('Y-m-d\TH:i') : '') }}" 
                                       required />
                                <div class="form-text">{{ trans('sw.loyalty_start_date_help') }}</div>
                                @error('start_date')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--end::Start Date-->
                        
                        <!--begin::End Date-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.end_date')}}</label>
                                <input type="datetime-local" name="end_date" class="form-control" 
                                       value="{{ old('end_date', isset($campaign) ? $campaign->end_date->format('Y-m-d\TH:i') : '') }}" 
                                       required />
                                <div class="form-text">{{ trans('sw.loyalty_end_date_help') }}</div>
                                @error('end_date')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--end::End Date-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Campaign Period Section-->

            <!--begin::Settings Section-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="bi bi-gear me-2"></i>{{ trans('sw.settings')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                   
                    <div class="row g-5">
                        <!--begin::Applies To-->
                        <div class="col-md-8">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.applies_to')}}</label>
                                <input type="text" name="applies_to" class="form-control" 
                                       placeholder="{{ trans('sw.optional_filter')}}" 
                                       value="{{ old('applies_to', $campaign->applies_to ?? '') }}" />
                                <div class="form-text text-info">
                                    <i class="ki-outline ki-information-2 fs-6"></i>
                                    {{ trans('sw.loyalty_applies_to_help') }}
                                </div>
                                @error('applies_to')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--end::Applies To-->
                        
                        <!--begin::Status-->
                        <div class="col-md-4">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.status')}}</label>
                                <div class="form-check form-switch form-check-custom form-check-solid mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                           {{ old('is_active', $campaign->is_active ?? true) ? 'checked' : '' }} />
                                    <label class="form-check-label">{{ trans('sw.active')}}</label>
                                </div>
                                <div class="form-text">{{ trans('sw.loyalty_campaign_active_help') }}</div>
                            </div>
                        </div>
                        <!--end::Status-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Settings Section-->

            <!--begin::Actions-->
            <div class="d-flex justify-content-end gap-3">
                <a href="{{ route('sw.loyalty_campaigns.index') }}" class="btn btn-light">
                    {{ trans('sw.cancel')}}
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-check fs-3"></i>
                    {{ trans('sw.save')}}
                </button>
            </div>
            <!--end::Actions-->
            
        </div>
        <!--end::Main column-->
    </form>
    <!--end::Loyalty Campaign Form-->

@endsection
