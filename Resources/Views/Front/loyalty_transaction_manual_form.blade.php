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
            <a href="{{ route('sw.loyalty_transactions.index') }}" class="text-muted text-hover-primary">{{ trans('sw.loyalty_transactions_list')}}</a>
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
    <!--begin::Manual Points Adjustment Form-->
    <form method="post" action="{{ route('sw.loyalty_transactions.store_manual') }}" class="form d-flex flex-column flex-lg-row">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            
            <!--begin::Notice Section-->
            <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                <i class="ki-outline ki-information fs-2tx text-warning me-4"></i>
                <div class="d-flex flex-stack flex-grow-1">
                    <div class="fw-semibold">
                        <h4 class="text-gray-900 fw-bold">{{ trans('sw.note') }}</h4>
                        <div class="fs-6 text-gray-700">{{ trans('sw.manual_adjustment_help') }}</div>
                    </div>
                </div>
            </div>
            <!--end::Notice Section-->
            
            <!--begin::Member Selection Section-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="bi bi-person me-2"></i>{{ trans('sw.member_information')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                   
                    <div class="row g-5">
                        <!--begin::Select Member-->
                        <div class="col-md-12">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.select_member')}}</label>
                                <select name="member_id" class="form-select" data-control="select2" data-placeholder="{{ trans('sw.select_member')}}" required>
                                    <option value="">{{ trans('sw.select_member')}}</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }} - {{ trans('sw.current_balance') }}: {{ number_format($member->loyalty_points_balance) }} {{ trans('sw.points') }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">{{ trans('sw.loyalty_select_member_help') }}</div>
                                @error('member_id')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--end::Select Member-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Member Selection Section-->

            <!--begin::Points Adjustment Section-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="bi bi-plus-slash-minus me-2"></i>{{ trans('sw.points_adjustment')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                   
                    <div class="row g-5">
                        <!--begin::Points-->
                        <div class="col-md-12">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.points')}}</label>
                                <input type="number" name="points" class="form-control" 
                                       placeholder="{{ trans('sw.positive_to_add_negative_to_deduct')}}" 
                                       value="{{ old('points') }}" 
                                       required />
                                <div class="form-text text-primary">
                                    <i class="ki-outline ki-information-2 fs-6"></i>
                                    {{ trans('sw.loyalty_points_input_help') }}
                                </div>
                                @error('points')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--end::Points-->
                        
                        <!--begin::Reason-->
                        <div class="col-md-12">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.reason')}}</label>
                                <textarea name="reason" class="form-control" rows="4"
                                          placeholder="{{ trans('sw.enter_reason_for_adjustment')}}" 
                                          required>{{ old('reason') }}</textarea>
                                <div class="form-text text-warning">
                                    <i class="ki-outline ki-information-2 fs-6"></i>
                                    {{ trans('sw.loyalty_reason_help') }}
                                </div>
                                @error('reason')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!--end::Reason-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Points Adjustment Section-->

            <!--begin::Actions-->
            <div class="d-flex justify-content-end gap-3">
                <a href="{{ route('sw.loyalty_transactions.index') }}" class="btn btn-light">
                    {{ trans('sw.cancel')}}
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="ki-outline ki-check fs-3"></i>
                    {{ trans('sw.save')}}
                </button>
            </div>
            <!--end::Actions-->
            
        </div>
        <!--end::Main column-->
    </form>
    <!--end::Manual Points Adjustment Form-->

@endsection


