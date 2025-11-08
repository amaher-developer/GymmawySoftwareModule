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
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
        <!--end::Item-->
    </ul>
    <!--end::Breadcrumb-->
@endsection
@section('styles')
    <style>
        .invoice-container {
            direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
            font-family: {{ app()->getLocale() == 'ar' ? 'Cairo, Tahoma, Arial, sans-serif' : 'inherit' }};
        }
        
        .total_for_price {
            color: gray;
            font-size: 12px;
        }
        .setting-images {
            width: 100%;
        }
        #member_signature_form {
            display: none;
        }
        
        .kbw-signature { width: 300px; height: 80px; }
        canvas {
            display: block;
        }
        
        /* Print Styles */
        @media print {
            /* Hide buttons and navigation */
            .btn, button, .hidden-print, .breadcrumb {
                display: none !important;
            }
            
            /* Hide header, menu, sidebar, and navigation */
            header, .header, .menu, .sidebar, nav, .navbar, .toolbar, .app-header, .app-sidebar, .app-wrapper {
                display: none !important;
            }
            
            @page {
                margin: 0.3in;
                size: A4;
            }
            
            body {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Remove all wrapper margins and padding */
            .wrapper, .content, .container, .container-fluid, .app-container, .app-main {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .invoice-container, .card {
                box-shadow: none !important;
                border: none !important;
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .card-body {
                padding: 5px !important;
                margin: 0 !important;
            }
            
            .mw-lg-950px {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Reduce all spacing in print */
            .mb-19, .mb-10, .mb-9, .mb-8, .mb-7, .mb-5, .mb-4, .mb-3, .mb-2 {
                margin-bottom: 5px !important;
            }
            
            .mt-10, .mt-7, .mt-5, .mt-3 {
                margin-top: 5px !important;
            }
            
            .pb-7, .pb-12, .pt-8, .pt-10 {
                padding-bottom: 5px !important;
                padding-top: 5px !important;
            }
            
            .py-20 {
                padding-top: 5px !important;
                padding-bottom: 5px !important;
            }
            
            .gap-7, .gap-md-10 {
                gap: 5px !important;
            }
            
            .pe-5 {
                padding-right: 5px !important;
            }
            
            .separator {
                border-top: 1px solid #ddd !important;
                margin: 10px 0 !important;
            }
            
            img {
                max-width: 100% !important;
                height: auto !important;
            }
            
            /* Make logo smaller in print */
            .invoice-logo {
                height: 50px !important;
                max-height: 50px !important;
            }
        }
    </style>
    <link href="{{asset('resources/assets/front/')}}/css/jquery.signature.css" rel="stylesheet">
@endsection
@section('form_title') {{ @$title }} @endsection
@section('page_body')
    @php
        $invoice = $invoice ?? null;
        $invoiceRecord = $invoice ?? ($order->zatcaInvoice ?? null);
        $hasInvoice = $invoiceRecord && !empty(data_get($invoiceRecord, 'invoice_number'));

        $sentAt = $invoiceRecord ? data_get($invoiceRecord, 'zatca_sent_at') : null;
        $sentAtFormatted = $sentAt ? \Carbon\Carbon::parse($sentAt)->format('Y-m-d H:i') : null;

        $rawQr = $invoiceRecord ? data_get($invoiceRecord, 'zatca_qr_code') : null;
        if (!empty($rawQr)) {
            $baseQr = \Illuminate\Support\Str::startsWith($rawQr, 'data:image') ? $rawQr : 'data:image/png;base64,' . $rawQr;
        } elseif (!empty($qr_img_invoice)) {
            $baseQr = \Illuminate\Support\Str::startsWith($qr_img_invoice, 'data:image') ? $qr_img_invoice : asset($qr_img_invoice);
        } else {
            $baseQr = null;
        }
    @endphp
    {{--    {!! \DNS2D::getBarcodeHTML('AQVTYWxsYQIKMTIzNDU2Nzg5MQMUMjAyMS0wNy0xMlQxNDoyNTowOVoEBjEwMC4wMAUFMTUuMDA=', 'QRCODE') !!}--}}

    <!-- begin::Invoice 3-->
    <div class="card invoice-container">
        <!-- begin::Body-->
        <div class="card-body py-20">
            <!-- begin::Wrapper-->
            <div class="mw-lg-950px mx-auto w-100">
                <!-- begin::Header-->
                <div class="d-flex justify-content-between flex-column flex-sm-row mb-19">
                    <h4 class="fw-bolder text-gray-800 fs-2qx pe-5 pb-7">{{ trans('sw.subscription_contract') }}</h4>
                    <!--end::Logo-->
                    <div class="text-sm-end">
                        <!--begin::Logo-->
                        @if($mainSettings->logo)
                            <img alt="Logo" src="{{$mainSettings->logo}}" class="invoice-logo" style="height: 80px;object-fit: contain;" />
                        @endif
                        <!--end::Logo-->
                        <!--begin::Address-->
                        @if(@$mainSettings->address)
                            <div class="text-sm-end fw-semibold fs-6 text-muted mt-3" style="max-width: 200px; margin-left: auto; line-height: 1.4;">
                                {{@$mainSettings->address}}
                            </div>
                        @endif
                        <!--end::Address-->
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="pb-12">
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-column gap-7 gap-md-10">
                        <!--begin::Message-->
                        <div class="fw-bold fs-2">{{ trans('sw.subscription_contract_details') }}</div>
                        <!--begin::Message-->
                        <!--begin::Separator-->
                        <div class="separator"></div>
                        <!--begin::Separator-->
                        <!--begin::Order details-->
                        <div class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold">
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted"><i class="ki-outline ki-hash fs-6 me-1"></i>{{ app()->getLocale() == 'ar' ? 'رقم الاشتراك' : trans('sw.subscription_number') }}</span>
                                <span class="fs-5">#{{$order['id']}}</span>
                            </div>
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted"><i class="ki-outline ki-calendar fs-6 me-1"></i>{{ trans('sw.date') }}</span>
                                <span class="fs-5 d-flex align-items-center flex-nowrap">
                                    <i class="ki-outline ki-calendar fs-6 me-1 text-primary"></i>
                                    <span class="fw-bolder text-gray-800 text-nowrap">{{ \Carbon\Carbon::parse($order['created_at'] ?? $order['updated_at'])->format('d/m/Y') }}</span>
                                    <i class="ki-outline ki-time fs-6 ms-3 me-1 text-primary"></i>
                                    <span class="fw-bold text-gray-700 text-nowrap">{{ \Carbon\Carbon::parse($order['created_at'] ?? $order['updated_at'])->format('h:i A') }}</span>
                                </span>
                            </div>
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted"><i class="ki-outline ki-time-schedule fs-6 me-1"></i>{{ trans('sw.subscription_period') }}</span>
                                <span class="fs-5">
                                    @if(@$order->joining_date && @$order->expire_date)
                                        {{\Carbon\Carbon::parse($order->joining_date)->format('d/m/Y')}} ~ {{\Carbon\Carbon::parse($order->expire_date)->format('d/m/Y')}}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted"><i class="ki-outline ki-bill fs-6 me-1"></i>{{ trans('sw.invoice_total_required') }}</span>
                                <span class="fs-5">{{number_format($order['amount_paid'], 2)}} {{@trans('sw.app_currency')}}</span>
                            </div>
                            
                        </div>
                        <!--end::Order details-->
                        @if(config('sw_billing.zatca_enabled') && $invoiceRecord)
                            <div class="card bg-light-primary p-5 mb-7">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-4">
                                    <div class="d-flex flex-column">
                                        <h4 class="text-primary mb-2">{{ trans('sw.zatca_invoice_details') }}</h4>
                                        <p class="fs-6 text-gray-800 mb-1"><strong>{{ trans('sw.invoice_number') }}:</strong> {{ data_get($invoiceRecord, 'invoice_number') }}</p>
                                        <p class="fs-6 text-gray-800 mb-1"><strong>{{ trans('sw.total_amount') }}:</strong> {{ number_format((float) data_get($invoiceRecord, 'total_amount', 0), 2) }}</p>
                                        <p class="fs-6 text-gray-800 mb-1"><strong>{{ trans('sw.vat_amount') }}:</strong> {{ number_format((float) data_get($invoiceRecord, 'vat_amount', 0), 2) }}</p>
                                        <p class="fs-6 text-gray-800 mb-1">
                                            <strong>{{ trans('sw.status') }}:</strong>
                                            <span class="badge {{ data_get($invoiceRecord, 'zatca_status') === 'approved' ? 'badge-light-success' : 'badge-light-warning' }} fw-bold text-uppercase">
                                                {{ data_get($invoiceRecord, 'zatca_status') }}
                                            </span>
                                        </p>
                                        @if($sentAtFormatted)
                                            <p class="fs-6 text-gray-800 mb-1"><strong>{{ trans('sw.sent_at') }}:</strong> {{ $sentAtFormatted }}</p>
                                        @endif
                                    </div>
                                    @if($baseQr)
                                        <div class="flex-shrink-0 text-center">
                                            <img src="{{ $baseQr }}" alt="ZATCA QR Code" width="120" height="120" class="img-thumbnail">
                                            <div class="fs-8 text-muted mt-2">{{ data_get($invoiceRecord, 'invoice_number') }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <!--begin::Buyer & VAT Info-->
                        @if(@$mainSettings->vat_details['seller_name'] || @$mainSettings->vat_details['vat_number'] || @$order->member)
                        <div class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold">
                            @if(@$mainSettings->vat_details['seller_name'])
                            <div class="flex-root d-flex flex-column" style="max-width: calc(25% - 20px);">
                                <span class="text-muted"><i class="ki-outline ki-shop fs-6 me-1"></i>{{ trans('sw.seller_name') }}</span>
                                <span class="fs-5">{{@$mainSettings->vat_details['seller_name']}}</span>
                            </div>
                            @endif
                            @if(@$mainSettings->vat_details['vat_number'])
                            <div class="flex-root d-flex flex-column" style="max-width: calc(25% - 20px);">
                                <span class="text-muted"><i class="ki-outline ki-barcode fs-6 me-1"></i>{{ trans('sw.vat_number') }}</span>
                                <span class="fs-5">{{@$mainSettings->vat_details['vat_number']}}</span>
                            </div>
                            @endif
                            @if(@$order->member)
                            <div class="flex-root d-flex flex-column" style="max-width: calc(25% - 20px);">
                                <span class="text-muted"><i class="ki-outline ki-profile-user fs-6 me-1"></i>{{ trans('sw.buyer_name') }}</span>
                                <span class="fs-5">{{@$order->member->name}} ({{@$order->member->code}})</span>
                            </div>
                            @endif
                            @if(@$order->member->national_id)
                            <div class="flex-root d-flex flex-column" style="max-width: calc(25% - 20px);">
                                <span class="text-muted"><i class="ki-outline ki-security-user fs-6 me-1"></i>{{ trans('sw.national_id') }}</span>
                                <span class="fs-5">{{@$order->member->national_id}}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                        <!--end::Buyer & VAT Info-->
                        <!--begin:Order summary-->
                        <div class="d-flex justify-content-between flex-column">
                            <!--begin::Table-->
                            <div class="table-responsive border-bottom mb-9">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="border-bottom fs-6 fw-bold text-muted">
                                            <th class="min-w-50px pb-2">#</th>
                                            <th class="min-w-175px pb-2">{{ trans('sw.subscription_contract_details') }}</th>
                                            <th class="min-w-100px text-end pb-2">{{ trans('sw.total_price') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                        <tr>
                                            <td>{{$order->id}}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <!--begin::Thumbnail-->
                                                    <div class="symbol symbol-50px">
                                                        <span class="symbol-label bg-light-success">
                                                            <i class="ki-outline ki-calendar-tick fs-2x text-success"></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Thumbnail-->
                                                    <!--begin::Title-->
                                                    <div class="ms-5">
                                                        <div class="fw-bold">{{$order->subscription->name}}</div>
                                                        <div class="fs-7 text-muted">{{ trans('sw.member_moneybox_add_msg', ['member'=> $order->member->name, 'subscription' => $order->subscription->name .((@$order->joining_date && @$order->expire_date) ? ' ( '.\Carbon\Carbon::parse($order->joining_date)->toDateString().' ~ '.\Carbon\Carbon::parse($order->expire_date)->toDateString().' ) ' : ' '), 'amount_paid' => @round($order->amount_paid, 2), 'amount_remaining' => @round(@$order->amount_remaining, 2)])}}</div>
                                                    </div>
                                                    <!--end::Title-->
                                                </div>
                                            </td>
                                            <td class="text-end">{{number_format($order->amount_paid,2)}} {{@trans('sw.app_currency')}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-end">{{ trans('sw.total_for_price') }} @if(@$order->vat)<span class="total_for_price">({{ trans('sw.excluding_vat') }})</span>@endif</td>
                                            <td class="text-end">{{number_format(($order->amount_paid + @$order->amount_remaining - @$order->vat),2)}} {{@trans('sw.app_currency')}}</td>
                                        </tr>
                                        @if(@$order->vat)
                                        <tr>
                                            <td colspan="2" class="text-end">{{ trans('sw.total_for_price') }} <span class="total_for_price">({{ trans('sw.including_vat') }})</span></td>
                                            <td class="text-end">{{number_format(($order->amount_paid + @$order->amount_remaining),2)}} {{@trans('sw.app_currency')}}</td>
                                        </tr>
                                        @endif
                                        @if($order->discount_value)
                                        <tr>
                                            <td colspan="2" class="text-end">{{ trans('sw.discount') }}</td>
                                            <td class="text-end">{{number_format($order->discount_value, 2)}} {{@trans('sw.app_currency')}}</td>
                                        </tr>
                                        @endif
                                        @if(@$order->vat)
                                        <tr>
                                            <td colspan="2" class="text-end">{{ trans('sw.vat') }} <span class="total_for_price">({{@$mainSettings->vat_details['vat_percentage'].'%'}})</span></td>
                                            <td class="text-end">{{@number_format($order->vat, 2)}} {{@trans('sw.app_currency')}}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td colspan="2" class="text-end">{{ trans('sw.reminder') }}</td>
                                            <td class="text-end">{{number_format(($order->amount_remaining),2)}} {{@trans('sw.app_currency')}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-end">{{ trans('sw.paid') }}</td>
                                            <td class="text-end">{{number_format(($order->amount_paid),2)}} {{@trans('sw.app_currency')}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-end fs-7 text-muted">
                                                @foreach($payments as $payment)
                                                <strong>{{$payment['name']}}:</strong> {{number_format(($payment['payment']),2)}} |
                                                @endforeach
                                            </td>
                                            <td class="text-end"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="fs-3 text-gray-900 fw-bold text-end">{{ trans('sw.invoice_total_required') }}</td>
                                            <td class="text-gray-900 fs-3 fw-bolder text-end">{{number_format($order->amount_paid, 2)}} {{@trans('sw.app_currency')}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!--end::Table-->
                        </div>
                        <!--end:Order summary-->
                        
                        @if(@$mainSettings->terms)
                        <!--begin::Terms-->
                        <div class="d-flex flex-column gap-3">
                            <div class="separator"></div>
                            <div class="fw-bold fs-4"><i class="ki-outline ki-document fs-3 me-2"></i>{{ trans('sw.terms') }}</div>
                            <div class="fs-6 text-gray-700">
                                {!! $mainSettings->terms !!}
                            </div>
                        </div>
                        <!--end::Terms-->
                        @endif

                        <!--begin::Signature Section-->
                        <div class="d-flex flex-column gap-3">
                            <div class="separator"></div>
                            <div class="d-flex flex-wrap gap-5">
                                <div class="flex-grow-1" style="min-width: 250px;">
                                    <div class="fw-bold mb-2">{{ trans('sw.name') }}:</div>
                                    <div id="member_name">@if($order->signature_file){{@$order->member->name}}@else ---------------- @endif</div>
                                    <div class="fw-bold mt-4 mb-2">{{ trans('sw.signature') }}:</div>
                                    <div id="member_signature">@if($order->signature_file) <img src="{{$order->signature_file}}" style="max-width: 200px; border: 1px solid #e0e0e0; padding: 5px;"> @else ---------------- @endif</div>
                                    
                                    <div id="member_signature_form" class="mt-3">
                                        <input name="page_type" id="page_type" value="{{Route::current()->getName()}}" type="hidden">
                                        <div>
                                            <canvas id="signature-pad" style="border: 1px solid #a0a0a0;" width="300" height="150"></canvas>
                                        </div>
                                        <div class="mt-3">
                                            <button class="btn btn-success btn-sm" onclick="member_signature()">{{ trans('global.save') }}</button>
                                            <button class="btn btn-secondary btn-sm" id="clear-signature">{{ trans('admin.reset') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Signature Section-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Body-->
                <!-- begin::Footer-->
                <div class="d-flex flex-stack flex-wrap mt-10 pt-8 {{ app()->getLocale() == 'ar' ? 'flex-row-reverse' : '' }}">
                    <!-- begin::Actions-->
                    <div class="my-1 {{ app()->getLocale() == 'ar' ? 'ms-5' : 'me-5' }}">
                        @if((Route::current()->getName() == 'sw.showOrderSubscription') &&(@round($order->amount_remaining) > 0) && (in_array('createMemberPayAmountRemainingForm', (array)$swUser->permissions) || $swUser->is_super_user))
                        <!-- begin::Pay-->
                        <button type="button" class="btn btn-primary my-1 {{ app()->getLocale() == 'ar' ? 'ms-3' : 'me-3' }}" data-target="#modalPay" data-toggle="modal" title="{{ trans('sw.pay_remaining') }}">
                            <i class="ki-outline ki-dollar {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i> {{ trans('sw.pay_remaining') }}
                        </button>
                        <!-- end::Pay-->
                        @endif
                        
                        @if(in_array('signOrderSubscription', (array)$swUser->permissions) || $swUser->is_super_user)
                        <!-- begin::Sign-->
                        <button type="button" class="btn btn-light-primary my-1 {{ app()->getLocale() == 'ar' ? 'ms-3' : 'me-3' }}" onclick="member_signature()" title="{{ trans('sw.signature') }}">
                            <i class="ki-outline ki-pencil {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                        </button>
                        <!-- end::Sign-->
                        @endif

                        @if(in_array('uploadContractGymOrder', (array)$swUser->permissions) || $swUser->is_super_user)
                        <!-- begin::Upload-->
                        <button type="button" class="btn btn-light-primary my-1 {{ app()->getLocale() == 'ar' ? 'ms-3' : 'me-3' }}" data-target="#modalUploadContract" data-toggle="modal" title="{{ trans('sw.upload_subscription_contract') }}">
                            <i class="ki-outline ki-file-up {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                        </button>
                        <!-- end::Upload-->
                        @endif
                        
                        <!-- begin::Print-->
                        <button type="button" class="btn btn-success my-1 {{ app()->getLocale() == 'ar' ? 'ms-3' : 'me-3' }}" onclick="window.print();">
                            <i class="fa fa-print {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i> {{ trans('sw.print') }}
                        </button>
                        <!-- end::Print-->
                        
                        <!-- begin::POS View-->
                        @if(Route::current()->getName() == 'sw.showOrderSubscriptionNonMember')
                            <button type="button" class="btn btn-light-success btn-icon my-1" onclick="javascript:window.open('{{route('sw.showOrderSubscriptionPOSNonMember', @$order->id)}}', 'POS','height=600,width=700');" title="POS {{ trans('sw.view') }}">
                                <i class="ki-outline ki-file fs-2"></i>
                            </button>
                        @elseif(Route::current()->getName() == 'sw.showOrderPTSubscription')
                            <button type="button" class="btn btn-light-success btn-icon my-1" onclick="javascript:window.open('{{route('sw.showOrderPTSubscriptionPOS', @$order->id)}}', 'POS','height=600,width=700');" title="POS {{ trans('sw.view') }}">
                                <i class="ki-outline ki-file fs-2"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-light-success btn-icon my-1" onclick="javascript:window.open('{{route('sw.showOrderSubscriptionPOS', @$order->id)}}', 'POS','height=600,width=700');" title="POS {{ trans('sw.view') }}">
                                <i class="ki-outline ki-file fs-2"></i>
                            </button>
                        @endif
                        <!-- end::POS View-->
                        
                        @if(@$mainSettings->active_zk)
                        <!-- begin::Fingerprint-->
                        <button type="button" class="btn btn-light-info my-1 {{ app()->getLocale() == 'ar' ? 'ms-3' : 'me-3' }}" id="fingerprint_refresh" onclick="fingerprint_open_popup()">
                            <i class="fa fa-refresh {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i> {{ trans('sw.fingerprint_refresh') }}
                        </button>
                        <!-- end::Fingerprint-->
                        @endif
                    </div>
                    <!-- end::Actions-->
                    @php
                        $qr_src = $qr_img_invoice ?? null;
                        if($qr_src && !\Illuminate\Support\Str::startsWith($qr_src, 'data:image')) {
                            $qr_src = asset($qr_src);
                        }
                    @endphp
                    @if(@$mainSettings->vat_details['saudi'] && $qr_src)
                    <!-- begin::QR Code-->
                    <div class="my-1 d-flex flex-column align-items-center">
                        <img class="well" src="{{$qr_src}}" style="height: 60px; width: 60px;" alt="QR Code"/>
                        @if($hasInvoice)
                            <div class="fs-8 text-muted mt-2">{{ $invoice->invoice_number }}</div>
                        @endif
                    </div>
                    <!-- end::QR Code-->
                    @endif
                </div>
                <!-- end::Footer-->
            </div>
            <!-- end::Wrapper-->
        </div>
        <!-- end::Body-->
    </div>
    <!-- end::Invoice 3-->


    <!-- start model pay -->
    <div class="modal" id="modalUploadContract">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.upload_subscription_contract')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 style="font-weight: bolder;" id=""></h6>

                    <div class="row col-lg-12" style="margin: 10px 0;">
                        <label> <i class="fa fa-info-circle"></i> {{ trans('sw.upload_subscription_contract_msg')}}</label><br/><br/>
                    </div>
                    <div class="row col-lg-6">
                        <input type="file" name="contract_file" id="upload_contract_file" class="form-control"/>
                    </div>
                    <div class="row col-lg-6" style="padding: 6px 40px;text-align: left;">
                        @if(@$order->contract_files)
                            @foreach($order->contract_files as $contract_file)
                            <a href="{{asset('./uploads/gymorders/' . $contract_file)}}" download><i class="fa fa-download"></i> {{ trans('sw.download')}}</a>
                            @endforeach
                        @endif
                    </div>
                    <div class="clearfix"><br/><br/></div>
                    <div class="row col-lg-12" id="uploaded_contract_file">
                        @if(@$order->contract_files)
                            @foreach($order->contract_files as $contract_file)
                                <img src="{{asset('./uploads/gymorders/' . $contract_file)}}"  class="img-thumbnail  setting-images" />
                            @endforeach
                        @endif
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- End model pay -->


    <!-- start model pay -->
    <div class="modal" id="modalPay">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.pay_remaining')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 id="payMemberName" style="font-weight: bolder">{{ trans('sw.amount_paid')}}</h6>
                    <div id="modalPayResult"></div>
                    <form id="form_pay" action="" method="GET">
                        <div class="row">
                            <div class="form-group col-lg-6">
                                <input name="amount_paid" class="form-control" type="number" id="amount_paid"  step="0.01"
                                       placeholder="{{ trans('sw.enter_amount_paid')}}">
                            </div><!-- end pay qty  -->
                            <div class="form-group col-lg-6">
                                <select class="form-control" name="payment_type" id="payment_type">
                                    @foreach($payment_types as $payment_type)
                                        <option value="{{$payment_type->payment_id}}" >{{$payment_type->name}}</option>
                                    @endforeach
                                    {{--                                    <option value="{{\Modules\Software\Classes\TypeConstants::CASH_PAYMENT}}" >{{ trans('sw.payment_cash')}}</option>--}}
                                    {{--                                    <option value="{{\Modules\Software\Classes\TypeConstants::ONLINE_PAYMENT}}" >{{ trans('sw.payment_online')}}</option>--}}
                                    {{--                                    <option value="{{\Modules\Software\Classes\TypeConstants::BANK_TRANSFER_PAYMENT}}" >{{ trans('sw.payment_bank_transfer')}}</option>--}}
                                </select>
                            </div><!-- end pay qty  -->
                        </div>
                        <br/>
                        <button class="btn ripple btn-primary rounded-3" id="form_pay_btn"
                                type="submit">{{ trans('sw.pay')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- End model pay -->
@endsection
@section('sub_scripts')
<script>
    function fingerprint_open_popup() {
        const myWindow = window.open("{{env('APP_ZK_LOCAL_HOST')}}", "", "width=20, height=20");
        setTimeout(function() {myWindow.close()}, 5000);
    }
    @if($mainSettings->active_zk && (request('reload') == 1))
        fingerprint_open_popup();
    @endif


    $("#upload_contract_file").change(function () {
        // $(document).on('change', '#upload_file', function () {
        var name = document.getElementById("upload_contract_file").files[0].name;
        var form_data = new FormData();
        var ext = name.split('.').pop().toLowerCase();
        if (jQuery.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
            alert("Invalid File");
        }
        var oFReader = new FileReader();
        oFReader.readAsDataURL(document.getElementById("upload_contract_file").files[0]);
        var f = document.getElementById("upload_contract_file").files[0];
        var fsize = f.size || f.fileSize;
        if (fsize > 2000000) {
            alert("{{ trans('sw.image_size_msg')}}");
        } else {
            form_data.append("contract_file", document.getElementById('upload_contract_file').files[0]);
            form_data.append("type", '{{Route::current()->getName()}}');
            form_data.append("order_id", '{{@$order->id}}');
            form_data.append("_token", "{{ csrf_token() }}");
            $.ajax({
                url: "{{ route('sw.uploadContractGymOrder') }}",
                method: "POST",
                data: form_data,
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {

                    $('#uploaded_contract_file').hide();
                    $('#uploaded_contract_file').after("<label class='text-success' id='loading'>Image Uploading...</label>");
                },
                success: function (data) {
                    if (data == '0') {
                        alert("{{ trans('sw.image_upload_error_msg')}}");
                    } else if (data == '1') {
                        alert("{{ trans('sw.image_max_error_msg')}}");
                    } else {

                        var index = data.lastIndexOf("/") + 1;
                        var filename = data.substr(index);
                        var dataResult = '<img src="' + data + '" height="150" width="225" class="img-thumbnail  setting-images" />'
                            // +'<br/><span><a href="javascript:void(0)" onclick="deleteUploadCoverImage("'+ filename +'");"><i class="fa fa-trash"></i></a></span>'
                        ;

                        $('#loading').remove();
                        $('#uploaded_contract_file').show();
                        // $('#uploaded_contract_file').append(dataResult);
                        $('#uploaded_contract_file').html(dataResult);
                    }
                }
            });
        }
    });
</script>

@if(in_array('signOrderSubscription', (array)$swUser->permissions) || $swUser->is_super_user)

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
    var canvas = document.getElementById('signature-pad');
    var signaturePad = new SignaturePad(canvas);
    var syncStatus = document.getElementById('sync-status');

    // Clear the signature pad
    document.getElementById('clear-signature').addEventListener('click', function () {
        signaturePad.clear();
    });

    // Auto-Save signature every 5 seconds
    function member_signature() {
        $('#member_signature_form').show();
        $('#member_name').html('{{@$order->member->name}}');
        $('#member_signature').html('');
        if (!signaturePad.isEmpty()) {
            var data = signaturePad.toDataURL('image/png');
            syncSignature(data);
        }
    }

    // Function to sync signature via AJAX
    function syncSignature(signatureData) {
        swal({
            title: trans_are_you_sure,
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: trans_yes,
            cancelButtonText: trans_no_please,
            showLoaderOnConfirm: true,
//                ,closeOnConfirm: false,
//                closeOnCancel: false
            preConfirm: function (isConfirm) {
                return new Promise(function (resolve, reject) {
                    if (isConfirm) {
                        $.ajax({
                            url: "{{route('sw.signOrderSubscription', $order->id)}}",
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                type: $('#page_type').val(),
                                signature: signatureData
                            },
                            success: function (response) {
                                if(response.status === true) {
                                    swal({
                                        title: trans_done,
                                        text: trans_successfully_processed,
                                        type: "success",
                                        button: "OK",
                                    }).then(() => {
                                        // This will reload the page when the user clicks "OK"
                                        window.location.reload();
                                    });
                                }else{
                                    swal({
                                        title: trans_operation_failed,
                                        text: trans_operation_failed,
                                        type: "error",
                                        timer: 4000,
                                        confirmButtonText: 'Ok',
                                    });
                                }
                                syncStatus.textContent = 'Synced at ' + new Date().toLocaleTimeString();
                            },
                            error: function () {
                                var response = $.parseJSON(reject.responseText);
                                console.log(response);
                                syncStatus.textContent = 'Sync Failed';
                            }
                        });

                    } else {
                        swal("Cancelled", "Alright, everything still as it is", "info");
                    }
            })
    },
    allowOutsideClick: false
    }).then(function (isConfirm) {

    });
    return false;
    }


    $(document).on('click', '#form_pay_btn', function (event) {
        event.preventDefault();
        let id = {{$order->id}};
        let amount_paid = $('#amount_paid').val();
        let payment_type = $('#payment_type').val();
        $('#modalPayResult').show();
        $.ajax({
            @if(Route::current()->getName() == 'sw.showOrderSubscriptionNonMember')
            url: '{{route('sw.createMemberPayAmountRemainingForm')}}',
            @elseif(Route::current()->getName() == 'sw.showOrderPTSubscription')
            url: '{{route('sw.createPTMemberPayAmountRemainingForm')}}',
            @else
            url: '{{route('sw.createMemberPayAmountRemainingForm')}}',
            @endif
            cache: false,
            type: 'GET',
            dataType: 'text',
            data: {id: id, amount_paid: amount_paid, payment_type: payment_type},
            success: function (response) {
                if (response == '1') {
                    $('#modalPayResult').html('<div class="alert alert-success">{{ trans('admin.successfully_paid')}}</div>');
                    let amount_remaining = $('#td_pay_amount_remaining_'+id).text();
                    if(amount_paid === amount_remaining){
                        $('#tr_pay_'+id).remove();
                    }else{
                        let result_amount_remaining = Math.round(amount_remaining) - Math.round(amount_paid);
                        $('#td_pay_amount_remaining_'+id).text(result_amount_remaining);
                        $('#span_amount_remaining_'+id).text(result_amount_remaining);
                        let span_total_amount_remaining = $('#span_total_amount_remaining_'+id).text();
                        let result_total_amount_remaining = Math.round(span_total_amount_remaining) - Math.round(amount_paid);
                        $('#span_total_amount_remaining_'+id).text(result_total_amount_remaining);
                    }
                    //location.reload();

                    swal({
                        title: trans_done,
                        text: trans_successfully_processed,
                        type: "success",
                        preConfirm: function (isConfirm) {
                            setTimeout(function () {
                                location.reload();

                            }, 1000)
                        },
                        allowOutsideClick: false
                    }).then(function (isConfirm) {
                        location.reload();
                    });
                } else {
                    $('#modalPayResult').html('<div class="alert alert-danger">' + response + '</div>');
                }

            },
            error: function (request, error) {
                swal("Operation failed", "Something went wrong.", "error");
                console.error("Request: " + JSON.stringify(request));
                console.error("Error: " + JSON.stringify(error));
            }
        });

    });



</script>

@endif
@endsection
