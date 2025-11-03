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
            <a href="{{ route('sw.loyalty_point_rules.index') }}" class="text-muted text-hover-primary">{{ trans('sw.loyalty_point_rules_list')}}</a>
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
    <!--begin::Loyalty Point Rule Form-->
    <form method="post" action="{{ isset($rule) ? route('sw.loyalty_point_rules.update', $rule->id) : route('sw.loyalty_point_rules.store') }}" class="form d-flex flex-column flex-lg-row">
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
                        <!--begin::Rule Name-->
                        <div class="col-md-12">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.name')}}</label>
                                <input type="text" name="name" class="form-control" 
                                       placeholder="{{ trans('sw.enter_rule_name')}}" 
                                       value="{{ old('name', $rule->name ?? '') }}" 
                                       required />
                                <div class="form-text">{{ trans('sw.loyalty_rule_name_help') }}</div>
                                @error('name')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--end::Rule Name-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Basic Information Section-->

            <!--begin::Conversion Rates Section-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="bi bi-currency-exchange me-2"></i>{{ trans('sw.conversion_rates')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                   
                    <div class="row g-5">
                        <!--begin::Money to Point Rate-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.money_to_point_rate')}}</label>
                                <input type="number" step="0.01" name="money_to_point_rate" class="form-control" 
                                       placeholder="10.00" 
                                       value="{{ old('money_to_point_rate', $rule->money_to_point_rate ?? '10.00') }}" 
                                       required />
                                <div class="form-text text-primary">
                                    <i class="ki-outline ki-information-2 fs-6"></i>
                                    {{ trans('sw.money_to_point_rate_help') }}
                                </div>
                                @error('money_to_point_rate')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--end::Money to Point Rate-->
                        
                        <!--begin::Point to Money Rate-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.point_to_money_rate')}}</label>
                                <input type="number" step="0.01" name="point_to_money_rate" class="form-control" 
                                       placeholder="10.00" 
                                       value="{{ old('point_to_money_rate', $rule->point_to_money_rate ?? '10.00') }}" 
                                       required />
                                <div class="form-text text-success">
                                    <i class="ki-outline ki-information-2 fs-6"></i>
                                    {{ trans('sw.point_to_money_rate_help') }}
                                </div>
                                @error('point_to_money_rate')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--end::Point to Money Rate-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Conversion Rates Section-->

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
                        <!--begin::Expiry Days-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.expires_after_days')}}</label>
                                <input type="number" name="expires_after_days" class="form-control" 
                                       placeholder="365" 
                                       value="{{ old('expires_after_days', $rule->expires_after_days ?? '') }}" />
                                <div class="form-text text-warning">
                                    <i class="ki-outline ki-information-2 fs-6"></i>
                                    {{ trans('sw.expires_after_days_help') }}
                                </div>
                                @error('expires_after_days')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--end::Expiry Days-->
                        
                        <!--begin::Status-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.status')}}</label>
                                <div class="form-check form-switch form-check-custom form-check-solid mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                           {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }} />
                                    <label class="form-check-label">{{ trans('sw.active')}}</label>
                                </div>
                                <div class="form-text">{{ trans('sw.loyalty_rule_active_help') }}</div>
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
                <a href="{{ route('sw.loyalty_point_rules.index') }}" class="btn btn-light">
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
    <!--end::Loyalty Point Rule Form-->

@endsection
