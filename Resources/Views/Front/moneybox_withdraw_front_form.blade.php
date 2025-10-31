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
            <a href="{{ route('sw.listMoneyBox') }}" class="text-muted text-hover-primary">{{ trans('sw.moneybox')}}</a>
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
    <!--begin::Moneybox Withdraw Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Aside column-->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <!--begin::Payment details-->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.payment_details') }}</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-10 fv-row">
                        <label class="required form-label">{{ trans('sw.payment_type')}}</label>
                        <select class="form-select form-select-solid mb-2" name="payment_type">
                            @foreach($payment_types as $payment_type)
                                <option value="{{$payment_type->payment_id}}" @if(@old('payment_type',$order->payment_type) == $payment_type->payment_id) selected="" @endif>{{$payment_type->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(@$mainSettings->vat_details['vat_percentage'])
                    <div class="fv-row">
                        <label class="form-check form-check-custom form-check-solid">
                            <input name="is_vat" class="form-check-input" type="checkbox" value="1" id="is_vat" />
                            <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.including_vat')}}</span>
                        </label>
                    </div>
                    @endif
                </div>
            </div>
            <!--end::Payment details-->
        </div>
        <!--end::Aside column-->

        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Withdraw Details-->
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
                        <label class="required form-label">{{ trans('sw.type')}}</label>
                        <select name="receipt_type" class="form-select mb-2" required>
                            @foreach($money_box_types as $money_box_type)
                                @if(@$money_box_type->operation_type == \Modules\Software\Classes\TypeConstants::Sub)
                                    <option value="{{$money_box_type->payment_type}}" @if(@old('receipt_type',$order->receipt_type) == $money_box_type->payment_type) selected @endif>
                                        {{$money_box_type->name}}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <label class="form-label">{{ trans('sw.received_name')}}</label>
                        <input type="text" name="member_name" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_name')}}" 
                               value="{{ old('member_name', $order->member_name) }}" 
                               id="member_name" />
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <label class="required form-label">{{ trans('sw.amount')}}</label>
                        <input type="number" name="amount" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_amount')}}" 
                               value="{{ old('amount', $order->amount) }}" 
                               id="amount" step="0.01" required />
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="fv-row">
                        <label class="required form-label">{{ trans('sw.notes')}}</label>
                        <textarea name="notes" class="form-control mb-2" 
                                  placeholder="{{ trans('sw.enter_notes')}}" 
                                  id="notes" required>{{ old('notes', $order->notes) }}</textarea>
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Withdraw Details-->

            <!--begin::Form actions-->
            <div class="d-flex justify-content-end">
                <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-minus fs-2"></i>
                    {{ trans('sw.withdraw')}}
                </button>
            </div>
            <!--end::Form actions-->
        </div>
        <!--end::Main column-->
    </form>
@endsection


@section('sub_scripts')

@endsection
