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
    @php
        $invoice = $order->zatcaInvoice ?? null;
        $zatcaEnabled = config('sw_billing.zatca_enabled') && config('sw_billing.auto_invoice') && data_get($billingSettings ?? [], 'sections.money_boxes', true);
    @endphp
    <!--begin::Moneybox Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}

        <!--begin::Aside column-->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <!--begin::Member details-->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.member_details') }}</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-3">
                        <label class="form-label">{{ trans('sw.member_id')}}</label>
                        <input type="text" name="member_id" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_member_id')}}" 
                               value="{{ old('barcode', @$member->member->code) }}" 
                               id="member_id" />
                    </div>
                    <div class="bg-light-secondary p-4 rounded">
                        <div><b>{{ trans('sw.name')}}:</b> <span id="store_member_name">{{@$member->member->name ? @$member->member->name : '-'}}</span></div>
                        <div><b>{{ trans('sw.phone')}}:</b> <span id="store_member_phone">{{@$member->member->phone ? @$member->member->phone : '-'}}</span></div>
                    </div>
                </div>
            </div>
            <!--end::Member details-->
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
                    @if($zatcaEnabled)
                    <div class="mb-10 fv-row">
                        <label class="form-check form-check-custom form-check-solid">
                            <input name="send_to_zatca" class="form-check-input" type="checkbox" value="1" checked>
                            <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.send_to_zatca') }}</span>
                        </label>
                        <span class="text-muted fs-8">{{ trans('sw.send_to_zatca_help') ?? '' }}</span>
                    </div>
                    @endif
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
            <!--begin::Moneybox Details-->
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
                        <select name="receipt_type" class="form-select mb-2">
                            @foreach($money_box_types as $money_box_type)
                            @if(@$money_box_type->operation_type == \Modules\Software\Classes\TypeConstants::Add)
                                <option value="{{$money_box_type->payment_type}}" @if(@old('receipt_type',$order->receipt_type) == $money_box_type->payment_type) selected="" @endif >{{$money_box_type->name}}</option>
                            @endif
                        @endforeach
                        </select>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <label class="form-label">{{ trans('sw.received_from')}}</label>
                        <input id="member_name" value="{{ old('member_name', $order->member_name) }}"
                               placeholder="{{ trans('sw.enter_name')}}"
                               name="member_name" type="text" class="form-control mb-2">
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <label class="required form-label">{{ trans('sw.amount')}}</label>
                        <input id="amount" value="{{ old('amount', $order->amount) }}"
                               placeholder="{{ trans('sw.enter_amount')}}"
                               name="amount" type="number" step="0.01" class="form-control mb-2" required>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="fv-row">
                        <label class="required form-label">{{ trans('sw.notes')}}</label>
                        <textarea id="notes"
                                   placeholder="{{ trans('sw.enter_notes')}}"
                                   name="notes" type="text" class="form-control mb-2" required>{{ old('notes', $order->notes) }}</textarea>
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Moneybox Details-->
            <!--begin::Form actions-->
            <div class="d-flex justify-content-end">
                <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-plus fs-2"></i>
                    {{ trans('sw.add')}}
                </button>
            </div>
            <!--end::Form actions-->
            @if($zatcaEnabled && $order->id && $invoice)
                <div class="card bg-light-primary p-5">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-4">
                        <div class="d-flex flex-column">
                            <h4 class="text-primary mb-2">{{ trans('sw.zatca_invoice_details') }}</h4>
                            <p class="fs-6 text-gray-800 mb-1"><strong>{{ trans('sw.invoice_number') }}:</strong> {{ $invoice->invoice_number }}</p>
                            <p class="fs-6 text-gray-800 mb-1"><strong>{{ trans('sw.total_amount') }}:</strong> {{ number_format($invoice->total_amount, 2) }}</p>
                            <p class="fs-6 text-gray-800 mb-1"><strong>{{ trans('sw.vat_amount') }}:</strong> {{ number_format($invoice->vat_amount, 2) }}</p>
                            <p class="fs-6 text-gray-800 mb-1"><strong>{{ trans('sw.status') }}:</strong>
                                <span class="badge {{ $invoice->zatca_status === 'approved' ? 'badge-light-success' : 'badge-light-warning' }} fw-bold text-uppercase">{{ $invoice->zatca_status }}</span>
                            </p>
                            @if($invoice->zatca_sent_at)
                                <p class="fs-6 text-gray-800 mb-1"><strong>{{ trans('sw.sent_at') }}:</strong> {{ $invoice->zatca_sent_at->format('Y-m-d H:i') }}</p>
                            @endif
                        </div>
                        @if(!empty($invoice->zatca_qr_code))
                            @php
                                $qrCode = \Illuminate\Support\Str::startsWith($invoice->zatca_qr_code, 'data:image')
                                    ? $invoice->zatca_qr_code
                                    : 'data:image/png;base64,' . $invoice->zatca_qr_code;
                            @endphp
                            <div class="flex-shrink-0">
                                <img src="{{ $qrCode }}" alt="ZATCA QR Code" width="120" height="120" class="img-thumbnail">
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        <!--end::Main column-->
    </form>
@endsection


@section('sub_scripts')
    <script>
        $('#member_id').keyup(function () {
            let member_id = $('#member_id').val();

            $.get("{{route('sw.getStoreMemberAjax')}}", {  member_id: member_id },
                function(result){
                    if(result){
                        $('#store_member_name').html(result.name);
                        $('#store_member_phone').html(result.phone);
                    }else{
                        $('#store_member_name').html('-');
                        $('#store_member_phone').html('-');
                    }
                }
            );
        });

    </script>
@endsection


