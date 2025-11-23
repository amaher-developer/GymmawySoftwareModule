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
    <link href="{{asset('resources/assets/new_front/')}}/css/jquery.signature.css" rel="stylesheet">
@endsection
@section('form_title') {{ @$title }} @endsection
@section('page_body')
    @php
        $invoice = $invoice ?? null;
        $invoiceRecord = $invoice ?? ($order->zatcaInvoice ?? null);
        $hasInvoice = $invoiceRecord && !empty(data_get($invoiceRecord, 'invoice_number'));

        $sentAt = $invoiceRecord ? data_get($invoiceRecord, 'zatca_sent_at') : null;
        $sentAtFormatted = $sentAt ? \Carbon\Carbon::parse($sentAt)->format('Y-m-d H:i') : null;

        // Check if activity reservation feature is active
        $features = is_array($mainSettings->features ?? null)
            ? $mainSettings->features 
            : (is_string($mainSettings->features ?? null) 
                ? json_decode($mainSettings->features, true) 
                : []);
        $active_activity_reservation = isset($features['active_activity_reservation']) && $features['active_activity_reservation'];
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
                        
                        @if($active_activity_reservation && (Route::current()->getName() == 'sw.showOrderSubscription') && (in_array('createReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user))
                            <!-- begin::Upcoming Reservations Button (Member)-->
                            @php
                                $memberReservations = $upcomingReservations ?? collect();
                            @endphp
                            @if($memberReservations->count() > 0)
                                <button type="button" 
                                        class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm my-1 position-relative {{ app()->getLocale() == 'ar' ? 'ms-3' : 'me-3' }}" 
                                        title="{{ trans('sw.upcoming_reservations') }} ({{ $memberReservations->count() }})"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#upcomingReservationsModal{{ $order->member_id }}">
                                    <i class="ki-outline ki-calendar-tick fs-2"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                        {{ $memberReservations->count() }}
                                        <span class="visually-hidden">{{ trans('sw.upcoming_reservations') }}</span>
                                    </span>
                                </button>
                            @endif
                            <!-- end::Upcoming Reservations Button (Member)-->
                            
                            <!-- begin::Quick Book Button (Member)-->
                            @php
                                $memberActivities = @$order->activities ?? [];
                                $hasValidActivities = false;
                                if (!empty($memberActivities) && is_array($memberActivities)) {
                                    foreach ($memberActivities as $act) {
                                        // For member subscriptions, activities structure is: $act['activity']['id']
                                        if (isset($act['activity']['id']) || isset($act['id'])) {
                                            $hasValidActivities = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            @if($hasValidActivities)
                                <button type="button" 
                                        class="btn btn-icon btn-bg-light btn-active-color-success btn-sm my-1 {{ app()->getLocale() == 'ar' ? 'ms-3' : 'me-3' }}" 
                                        title="{{ trans('sw.quick_booking') }}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#quickBookModal{{ $order->member_id }}"
                                        onclick="openQuickBookModal({{ $order->member_id }}, {{ json_encode($memberActivities) }})">
                                    <i class="ki-outline ki-calendar-add fs-2"></i>
                                </button>
                            @endif
                            <!-- end::Quick Book Button (Member)-->
                        @endif
                        
                        @if($active_activity_reservation && (Route::current()->getName() == 'sw.showOrderSubscriptionNonMember') && (in_array('createReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user))
                            <!-- begin::Upcoming Reservations Button (Non-Member)-->
                            @php
                                $nonMemberReservations = $upcomingReservations ?? collect();
                            @endphp
                            @if($nonMemberReservations->count() > 0)
                                <button type="button" 
                                        class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm my-1 position-relative {{ app()->getLocale() == 'ar' ? 'ms-3' : 'me-3' }}" 
                                        title="{{ trans('sw.upcoming_reservations') }} ({{ $nonMemberReservations->count() }})"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#upcomingReservationsModal{{ $order->id }}">
                                    <i class="ki-outline ki-calendar-tick fs-2"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                        {{ $nonMemberReservations->count() }}
                                        <span class="visually-hidden">{{ trans('sw.upcoming_reservations') }}</span>
                                    </span>
                                </button>
                            @endif
                            <!-- end::Upcoming Reservations Button (Non-Member)-->
                            
                            <!-- begin::Quick Book Button (Non-Member)-->
                            @php
                                $nonMemberActivities = @$order->activities ?? [];
                                $hasValidActivities = false;
                                if (!empty($nonMemberActivities) && is_array($nonMemberActivities)) {
                                    foreach ($nonMemberActivities as $act) {
                                        if (isset($act['id'])) {
                                            $hasValidActivities = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            @if($hasValidActivities)
                                <button type="button" 
                                        class="btn btn-icon btn-bg-light btn-active-color-success btn-sm my-1 {{ app()->getLocale() == 'ar' ? 'ms-3' : 'me-3' }}" 
                                        title="{{ trans('sw.quick_booking') }}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#quickBookModal{{ $order->id }}"
                                        onclick="openQuickBookModalNonMember({{ $order->id }}, {{ json_encode($nonMemberActivities) }})">
                                    <i class="ki-outline ki-calendar-add fs-2"></i>
                                </button>
                            @endif
                            <!-- end::Quick Book Button (Non-Member)-->
                        @endif
                        
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
    
    @if($active_activity_reservation && (Route::current()->getName() == 'sw.showOrderSubscription') && (in_array('createReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user))
        @php
            $memberReservations = $upcomingReservations ?? collect();
        @endphp
        @if($memberReservations->count() > 0)
            <!--begin::Upcoming Reservations Modal (Member)-->
            <div class="modal fade" id="upcomingReservationsModal{{ $order->member_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">{{ trans('sw.upcoming_reservations') }}</h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-outline ki-cross fs-1"></i>
                            </div>
                        </div>
                        <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                            <!--begin::Member Info-->
                            <div class="d-flex align-items-center mb-5">
                                <div class="symbol symbol-50px me-3">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-user fs-2x text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-800 fw-bold fs-5">{{ $order->member->name }}</div>
                                    @if($order->member->phone)
                                        <div class="text-muted fs-7">
                                            <i class="ki-outline ki-phone fs-6 me-1"></i> {{ $order->member->phone }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <!--end::Member Info-->
                            
                            <!--begin::Reservations List-->
                            <div class="separator separator-dashed my-5"></div>
                            <div class="mb-5">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="fw-bold text-gray-800 fs-6">
                                        {{ trans('sw.reservations') }} ({{ $memberReservations->count() }})
                                    </h3>
                                    <a href="{{ route('sw.listReservation') }}?member_id={{ $order->member_id }}" class="btn btn-sm btn-light-primary">
                                        <i class="ki-outline ki-eye fs-6"></i> {{ trans('sw.view_all') }}
                                    </a>
                                </div>
                                
                                <div class="d-flex flex-column gap-3">
                                    @foreach($memberReservations as $reservation)
                                        <div class="card card-flush border border-gray-300 border-dashed">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-4">
                                                        <!--begin::Date-->
                                                        <div class="text-center">
                                                            <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ trans('sw.date') }}</div>
                                                            <span class="badge badge-{{ $reservation->status == 'confirmed' ? 'success' : ($reservation->status == 'pending' ? 'warning' : 'primary') }} badge-lg">
                                                                {{ $reservation->reservation_date->format('Y-m-d') }}
                                                            </span>
                                                        </div>
                                                        <!--end::Date-->
                                                        
                                                        <!--begin::Time-->
                                                        <div class="text-center">
                                                            <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ trans('sw.time') }}</div>
                                                            <div class="text-gray-800 fw-bold fs-6">
                                                                <i class="ki-outline ki-time fs-5 text-primary me-1"></i>
                                                                {{ $reservation->start_time }} - {{ $reservation->end_time }}
                                                            </div>
                                                        </div>
                                                        <!--end::Time-->
                                                        
                                                        <!--begin::Activity-->
                                                        @if($reservation->activity)
                                                            <div class="text-center">
                                                                <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ trans('sw.activity') }}</div>
                                                                <span class="badge badge-light-info badge-lg">
                                                                    <i class="ki-outline ki-list fs-5 me-1"></i>
                                                                    {{ $reservation->activity->{'name_'.($lang ?? 'ar')} ?? $reservation->activity->name }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                        <!--end::Activity-->
                                                    </div>
                                                    
                                                    <!--begin::Status & Actions-->
                                                    <div class="text-end">
                                                        <div class="mb-2">
                                                            <select class="form-select form-select-sm reservation-status-select" 
                                                                    data-reservation-id="{{ $reservation->id }}"
                                                                    data-old-value="{{ $reservation->status }}"
                                                                    style="min-width: 120px;">
                                                                <option value="pending" @selected($reservation->status == 'pending')>{{ trans('sw.pending') }}</option>
                                                                <option value="confirmed" @selected($reservation->status == 'confirmed')>{{ trans('sw.confirmed') }}</option>
                                                                <option value="attended" @selected($reservation->status == 'attended')>{{ trans('sw.attended') }}</option>
                                                                <option value="cancelled" @selected($reservation->status == 'cancelled')>{{ trans('sw.cancelled') }}</option>
                                                                <option value="missed" @selected($reservation->status == 'missed')>{{ trans('sw.missed') }}</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-icon btn-light-primary reservation-edit-btn" 
                                                                    title="{{ trans('admin.edit') }}"
                                                                    data-reservation-id="{{ $reservation->id }}"
                                                                    data-member-id="{{ $order->member_id }}">
                                                                <i class="ki-outline ki-pencil fs-4"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <!--end::Status & Actions-->
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <!--end::Reservations List-->
                        </div>
                        <div class="modal-footer flex-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Upcoming Reservations Modal (Member)-->
        @endif
        
        @php
            $memberActivities = @$order->activities ?? [];
            $hasValidActivities = false;
            if (!empty($memberActivities) && is_array($memberActivities)) {
                foreach ($memberActivities as $act) {
                    if (isset($act['activity']['id']) || isset($act['id'])) {
                        $hasValidActivities = true;
                        break;
                    }
                }
            }
        @endphp
        @if($hasValidActivities)
            <!--begin::Quick Book Modal (Member)-->
            <div class="modal fade" id="quickBookModal{{ $order->member_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">
                                <i class="ki-outline ki-calendar-tick fs-2 me-2 text-success"></i>
                                {{ trans('sw.quick_booking') }} - {{ $order->member->name }}
                            </h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-outline ki-cross fs-1"></i>
                            </div>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="qb_member_id_{{ $order->member_id }}" value="{{ $order->member_id }}">
                            <input type="hidden" id="qb_reservation_id_{{ $order->member_id }}" value="">
                            
                            <!--begin::Help Text-->
                            <div class="alert alert-light-info d-flex align-items-center p-4 mb-5">
                                <i class="ki-outline ki-information-5 fs-2x text-info me-3"></i>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-gray-800">{{ trans('sw.quick_booking_title') }}</span>
                                    <span class="text-muted fs-7 mt-1">{{ trans('sw.select_activity_and_time') }}</span>
                                </div>
                            </div>
                            <!--end::Help Text-->
                            
                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="required form-label">
                                    <i class="ki-outline ki-gym fs-6 me-1"></i>
                                    {{ trans('sw.activity') }}
                                </label>
                                <select id="qb_activity_{{ $order->member_id }}" class="form-select form-select-solid qb-activity-select" data-member-id="{{ $order->member_id }}" data-placeholder="{{ trans('sw.select_activity') }}">
                                    <option value="">{{ trans('sw.select_activity') }}</option>
                                    @foreach($memberActivities as $activity)
                                        @php
                                            $activityData = $activity['activity'] ?? $activity;
                                            $activityId = $activityData['id'] ?? null;
                                            $activityName = $activityData['name_'.($lang ?? 'ar')] ?? $activityData['name_ar'] ?? $activityData['name'] ?? '';
                                            $duration = $activityData['duration_minutes'] ?? $activity['duration_minutes'] ?? 60;
                                        @endphp
                                        @if($activityId && $activityName)
                                            <option value="{{ $activityId }}" data-duration="{{ $duration }}">{{ $activityName }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <!--end::Input group-->
                            
                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="required form-label">
                                    <i class="ki-outline ki-calendar fs-6 me-1"></i>
                                    {{ trans('sw.date') }}
                                </label>
                                <input type="date" id="qb_date_{{ $order->member_id }}" class="form-control form-control-solid qb-date-input" data-member-id="{{ $order->member_id }}" min="{{ date('Y-m-d') }}" />
                            </div>
                            <!--end::Input group-->
                            
                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="form-label">
                                    <i class="ki-outline ki-time fs-6 me-1"></i>
                                    {{ trans('sw.duration') }}
                                </label>
                                <select id="qb_duration_{{ $order->member_id }}" class="form-select form-select-solid qb-duration-select" data-member-id="{{ $order->member_id }}">
                                    <option value="30">30 {{ trans('sw.minutes') }}</option>
                                    <option value="45">45 {{ trans('sw.minutes') }}</option>
                                    <option value="60" selected>60 {{ trans('sw.minutes') }}</option>
                                    <option value="90">90 {{ trans('sw.minutes') }}</option>
                                    <option value="120">120 {{ trans('sw.minutes') }}</option>
                                </select>
                            </div>
                            <!--end::Input group-->
                            
                            <!--begin::Button-->
                            <div class="mb-5">
                                <button type="button" class="btn btn-light-primary w-100 qb-load-slots-btn" data-member-id="{{ $order->member_id }}">
                                    <i class="ki-outline ki-magnifier fs-2"></i>
                                    {{ trans('sw.show_available_slots') }}
                                </button>
                            </div>
                            <!--end::Button-->
                            
                            <!--begin::Slots-->
                            <div id="qb_slots_{{ $order->member_id }}" class="mb-5">
                                <div class="slots-empty-state">
                                    <i class="ki-outline ki-calendar-tick"></i>
                                    <div class="empty-title">{{ trans('sw.select_activity_date_to_show_slots') }}</div>
                                    <div class="empty-subtitle">{{ trans('sw.choose_activity_and_date_first') }}</div>
                                </div>
                            </div>
                            <!--end::Slots-->
                            
                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="form-label">
                                    <i class="ki-outline ki-note-text fs-6 me-1"></i>
                                    {{ trans('sw.notes') }}
                                </label>
                                <textarea id="qb_notes_{{ $order->member_id }}" class="form-control form-control-solid" rows="3" placeholder="{{ trans('sw.enter_notes_placeholder') }}"></textarea>
                            </div>
                            <!--end::Input group-->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.cancel') }}</button>
                            <button type="button" class="btn btn-success qb-book-btn" data-member-id="{{ $order->member_id }}">
                                <i class="ki-outline ki-check-circle fs-2"></i>
                                <span class="qb-book-btn-text">{{ trans('sw.book_now') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Quick Book Modal (Member)-->
        @endif
    @endif
    
    @if($active_activity_reservation && (Route::current()->getName() == 'sw.showOrderSubscriptionNonMember') && (in_array('createReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user))
        @php
            $nonMemberReservations = $upcomingReservations ?? collect();
        @endphp
        @if($nonMemberReservations->count() > 0)
            <!--begin::Upcoming Reservations Modal (Non-Member)-->
            <div class="modal fade" id="upcomingReservationsModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">{{ trans('sw.upcoming_reservations') }}</h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-outline ki-cross fs-1"></i>
                            </div>
                        </div>
                        <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                            <!--begin::Non-Member Info-->
                            <div class="d-flex align-items-center mb-5">
                                <div class="symbol symbol-50px me-3">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-user fs-2x text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-800 fw-bold fs-5">{{ $order->name }}</div>
                                    @if($order->phone)
                                        <div class="text-muted fs-7">
                                            <i class="ki-outline ki-phone fs-6 me-1"></i> {{ $order->phone }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <!--end::Non-Member Info-->
                            
                            <!--begin::Reservations List-->
                            <div class="separator separator-dashed my-5"></div>
                            <div class="mb-5">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="fw-bold text-gray-800 fs-6">
                                        {{ trans('sw.reservations') }} ({{ $nonMemberReservations->count() }})
                                    </h3>
                                    <a href="{{ route('sw.listReservation') }}?non_member_id={{ $order->id }}" class="btn btn-sm btn-light-primary">
                                        <i class="ki-outline ki-eye fs-6"></i> {{ trans('sw.view_all') }}
                                    </a>
                                </div>
                                
                                <div class="d-flex flex-column gap-3">
                                    @foreach($nonMemberReservations as $reservation)
                                        <div class="card card-flush border border-gray-300 border-dashed">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-4">
                                                        <!--begin::Date-->
                                                        <div class="text-center">
                                                            <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ trans('sw.date') }}</div>
                                                            <span class="badge badge-{{ $reservation->status == 'confirmed' ? 'success' : ($reservation->status == 'pending' ? 'warning' : 'primary') }} badge-lg">
                                                                {{ $reservation->reservation_date->format('Y-m-d') }}
                                                            </span>
                                                        </div>
                                                        <!--end::Date-->
                                                        
                                                        <!--begin::Time-->
                                                        <div class="text-center">
                                                            <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ trans('sw.time') }}</div>
                                                            <div class="text-gray-800 fw-bold fs-6">
                                                                <i class="ki-outline ki-time fs-5 text-primary me-1"></i>
                                                                {{ $reservation->start_time }} - {{ $reservation->end_time }}
                                                            </div>
                                                        </div>
                                                        <!--end::Time-->
                                                        
                                                        <!--begin::Activity-->
                                                        @if($reservation->activity)
                                                            <div class="text-center">
                                                                <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ trans('sw.activity') }}</div>
                                                                <span class="badge badge-light-info badge-lg">
                                                                    <i class="ki-outline ki-list fs-5 me-1"></i>
                                                                    {{ $reservation->activity->{'name_'.($lang ?? 'ar')} ?? $reservation->activity->name }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                        <!--end::Activity-->
                                                    </div>
                                                    
                                                    <!--begin::Status & Actions-->
                                                    <div class="text-end">
                                                        <div class="mb-2">
                                                            <select class="form-select form-select-sm reservation-status-select" 
                                                                    data-reservation-id="{{ $reservation->id }}"
                                                                    data-old-value="{{ $reservation->status }}"
                                                                    style="min-width: 120px;">
                                                                <option value="pending" @selected($reservation->status == 'pending')>{{ trans('sw.pending') }}</option>
                                                                <option value="confirmed" @selected($reservation->status == 'confirmed')>{{ trans('sw.confirmed') }}</option>
                                                                <option value="attended" @selected($reservation->status == 'attended')>{{ trans('sw.attended') }}</option>
                                                                <option value="cancelled" @selected($reservation->status == 'cancelled')>{{ trans('sw.cancelled') }}</option>
                                                                <option value="missed" @selected($reservation->status == 'missed')>{{ trans('sw.missed') }}</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-icon btn-light-primary reservation-edit-btn" 
                                                                    title="{{ trans('admin.edit') }}"
                                                                    data-reservation-id="{{ $reservation->id }}"
                                                                    data-non-member-id="{{ $order->id }}">
                                                                <i class="ki-outline ki-pencil fs-4"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <!--end::Status & Actions-->
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <!--end::Reservations List-->
                        </div>
                        <div class="modal-footer flex-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Upcoming Reservations Modal (Non-Member)-->
        @endif
        
        @php
            $nonMemberActivities = @$order->activities ?? [];
            $hasValidActivities = false;
            if (!empty($nonMemberActivities) && is_array($nonMemberActivities)) {
                foreach ($nonMemberActivities as $act) {
                    if (isset($act['id'])) {
                        $hasValidActivities = true;
                        break;
                    }
                }
            }
        @endphp
        @if($hasValidActivities)
            <!--begin::Quick Book Modal (Non-Member)-->
            <div class="modal fade" id="quickBookModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">
                                <i class="ki-outline ki-calendar-tick fs-2 me-2 text-success"></i>
                                {{ trans('sw.quick_booking') }} - {{ $order->name }}
                            </h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-outline ki-cross fs-1"></i>
                            </div>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="qb_nonmember_id_{{ $order->id }}" value="{{ $order->id }}">
                            <input type="hidden" id="qb_reservation_id_{{ $order->id }}" value="">
                            
                            <!--begin::Help Text-->
                            <div class="alert alert-light-info d-flex align-items-center p-4 mb-5">
                                <i class="ki-outline ki-information-5 fs-2x text-info me-3"></i>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-gray-800">{{ trans('sw.quick_booking_title') }}</span>
                                    <span class="text-muted fs-7 mt-1">{{ trans('sw.select_activity_and_time') }}</span>
                                </div>
                            </div>
                            <!--end::Help Text-->
                            
                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="required form-label">
                                    <i class="ki-outline ki-gym fs-6 me-1"></i>
                                    {{ trans('sw.activity') }}
                                </label>
                                <select id="qb_activity_{{ $order->id }}" class="form-select form-select-solid qb-activity-select" data-non-member-id="{{ $order->id }}" data-placeholder="{{ trans('sw.select_activity') }}">
                                    <option value="">{{ trans('sw.select_activity') }}</option>
                                    @foreach($nonMemberActivities as $activity)
                                        @php
                                            $activityId = $activity['id'] ?? null;
                                            $activityName = $activity['name_'.($lang ?? 'ar')] ?? $activity['name_ar'] ?? $activity['name'] ?? '';
                                            $duration = $activity['duration_minutes'] ?? 60;
                                        @endphp
                                        @if($activityId && $activityName)
                                            <option value="{{ $activityId }}" data-duration="{{ $duration }}">{{ $activityName }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <!--end::Input group-->
                            
                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="required form-label">
                                    <i class="ki-outline ki-calendar fs-6 me-1"></i>
                                    {{ trans('sw.date') }}
                                </label>
                                <input type="date" id="qb_date_{{ $order->id }}" class="form-control form-control-solid qb-date-input" data-non-member-id="{{ $order->id }}" min="{{ date('Y-m-d') }}" />
                            </div>
                            <!--end::Input group-->
                            
                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="form-label">
                                    <i class="ki-outline ki-time fs-6 me-1"></i>
                                    {{ trans('sw.duration') }}
                                </label>
                                <select id="qb_duration_{{ $order->id }}" class="form-select form-select-solid qb-duration-select" data-non-member-id="{{ $order->id }}">
                                    <option value="30">30 {{ trans('sw.minutes') }}</option>
                                    <option value="45">45 {{ trans('sw.minutes') }}</option>
                                    <option value="60" selected>60 {{ trans('sw.minutes') }}</option>
                                    <option value="90">90 {{ trans('sw.minutes') }}</option>
                                    <option value="120">120 {{ trans('sw.minutes') }}</option>
                                </select>
                            </div>
                            <!--end::Input group-->
                            
                            <!--begin::Button-->
                            <div class="mb-5">
                                <button type="button" class="btn btn-light-primary w-100 qb-load-slots-btn" data-non-member-id="{{ $order->id }}">
                                    <i class="ki-outline ki-magnifier fs-2"></i>
                                    {{ trans('sw.show_available_slots') }}
                                </button>
                            </div>
                            <!--end::Button-->
                            
                            <!--begin::Slots-->
                            <div id="qb_slots_{{ $order->id }}" class="mb-5">
                                <div class="slots-empty-state">
                                    <i class="ki-outline ki-calendar-tick"></i>
                                    <div class="empty-title">{{ trans('sw.select_activity_date_to_show_slots') }}</div>
                                    <div class="empty-subtitle">{{ trans('sw.choose_activity_and_date_first') }}</div>
                                </div>
                            </div>
                            <!--end::Slots-->
                            
                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="form-label">
                                    <i class="ki-outline ki-note-text fs-6 me-1"></i>
                                    {{ trans('sw.notes') }}
                                </label>
                                <textarea id="qb_notes_{{ $order->id }}" class="form-control form-control-solid" rows="3" placeholder="{{ trans('sw.enter_notes_placeholder') }}"></textarea>
                            </div>
                            <!--end::Input group-->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.cancel') }}</button>
                            <button type="button" class="btn btn-success qb-book-btn" data-non-member-id="{{ $order->id }}">
                                <i class="ki-outline ki-check-circle fs-2"></i>
                                <span class="qb-book-btn-text">{{ trans('sw.book_now') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Quick Book Modal (Non-Member)-->
        @endif
    @endif
<!-- Quick Booking Styles -->
<style>
/* Time Slots Styling */
.time-slots-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 0.75rem;
    padding: 1rem 0;
}

.slot-btn {
    min-width: 140px;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 0.65rem;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-align: center;
    border: 2px solid;
    background: transparent;
}

.slot-btn i {
    font-size: 1.1rem;
}

/* Available Slot */
.slot-free {
    border-color: #50cd89;
    color: #50cd89;
    background-color: rgba(80, 205, 137, 0.08);
}

.slot-free:hover {
    background-color: rgba(80, 205, 137, 0.15);
    border-color: #47b875;
    color: #47b875;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(80, 205, 137, 0.2);
}

.slot-free.active {
    background: linear-gradient(135deg, #50cd89 0%, #47b875 100%);
    color: #ffffff;
    border-color: #47b875;
    box-shadow: 0 4px 16px rgba(80, 205, 137, 0.4);
    transform: translateY(-2px);
}

.slot-free.active::before {
    content: "\2713";
    margin-left: -0.5rem;
    font-weight: bold;
}

/* Busy/Occupied Slot */
.slot-busy {
    border-color: #e4e6ef;
    color: #a1a5b7;
    background-color: #f5f8fa;
    cursor: not-allowed;
    opacity: 0.65;
    position: relative;
}

.slot-busy::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #a1a5b7;
    transform: rotate(-5deg);
}

/* Empty State */
.slots-empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: linear-gradient(135deg, #f5f8fa 0%, #ffffff 100%);
    border-radius: 0.65rem;
    border: 2px dashed #e4e6ef;
}

.slots-empty-state i {
    font-size: 4rem;
    color: #e4e6ef;
    margin-bottom: 1rem;
    display: block;
}

.slots-empty-state .empty-title {
    font-size: 1rem;
    font-weight: 600;
    color: #5e6278;
    margin-bottom: 0.5rem;
}

.slots-empty-state .empty-subtitle {
    font-size: 0.875rem;
    color: #a1a5b7;
}

/* Error State */
.slots-error-state {
    text-align: center;
    padding: 3rem 1rem;
    background: linear-gradient(135deg, #fff5f8 0%, #ffffff 100%);
    border-radius: 0.65rem;
    border: 2px solid #f1416c;
}

.slots-error-state i {
    font-size: 4rem;
    color: #f1416c;
    margin-bottom: 1rem;
    display: block;
}

.slots-error-state .error-title {
    font-size: 1rem;
    font-weight: 600;
    color: #f1416c;
    margin-bottom: 0.5rem;
}

.slots-error-state .error-subtitle {
    font-size: 0.875rem;
    color: #a1a5b7;
}

/* Loading State */
.slots-loading-state {
    text-align: center;
    padding: 3rem 1rem;
}

.slots-loading-state .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.3rem;
    color: #50cd89;
}

/* Responsive */
@media (max-width: 768px) {
    .time-slots-container {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 0.5rem;
    }
    
    .slot-btn {
        min-width: 120px;
        padding: 0.625rem 0.875rem;
        font-size: 0.85rem;
    }
}
</style>
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

@if($active_activity_reservation && ((Route::current()->getName() == 'sw.showOrderSubscription') || (Route::current()->getName() == 'sw.showOrderSubscriptionNonMember')) && (in_array('createReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user))
<!-- Quick Booking JavaScript -->
<script>
// Function to open quick book modal for member
function openQuickBookModal(memberId, activities) {
    console.log('Opening modal for member:', memberId);
    setTimeout(function() {
        const select = $(`#qb_activity_${memberId}`);
        if (select.length === 0) {
            console.error('Select element not found for member:', memberId);
            return;
        }
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.select2({
            placeholder: '{{ trans('sw.select_activity') }}',
            allowClear: true,
            minimumResultsForSearch: 0,
            dropdownParent: $(`#quickBookModal${memberId}`),
            language: {
                searching: function() {
                    return '{{ trans('sw.searching') }}...';
                },
                noResults: function() {
                    return '{{ trans('sw.no_results_found') }}';
                }
            }
        });
        console.log('Select2 initialized for member:', memberId);
    }, 300);
}

// Function to open quick book modal for non-member
function openQuickBookModalNonMember(nonMemberId, activities) {
    console.log('Opening modal for non-member:', nonMemberId);
    setTimeout(function() {
        const select = $(`#qb_activity_${nonMemberId}`);
        if (select.length === 0) {
            console.error('Select element not found for non-member:', nonMemberId);
            return;
        }
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.select2({
            placeholder: '{{ trans('sw.select_activity') }}',
            allowClear: true,
            minimumResultsForSearch: 0,
            dropdownParent: $(`#quickBookModal${nonMemberId}`),
            language: {
                searching: function() {
                    return '{{ trans('sw.searching') }}...';
                },
                noResults: function() {
                    return '{{ trans('sw.no_results_found') }}';
                }
            }
        });
        console.log('Select2 initialized for non-member:', nonMemberId);
    }, 300);
}

// Load slots for member modal
$(document).on('click', '.qb-load-slots-btn[data-member-id]', function(e){
    e.preventDefault();
    e.stopPropagation();
    
    const memberId = $(this).data('member-id');
    console.log('Button clicked! Loading slots for member:', memberId);
    
    let activity_id;
    const activitySelect = $(`#qb_activity_${memberId}`);
    
    if (activitySelect.length === 0) {
        console.error('Activity select not found for member:', memberId);
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: 'Activity select not found',
            confirmButtonText: 'Ok'
        });
        return false;
    }
    
    if (activitySelect.hasClass('select2-hidden-accessible')) {
        activity_id = activitySelect.select2('val');
    } else {
        activity_id = activitySelect.val();
    }
    
    const date = $(`#qb_date_${memberId}`).val();
    const duration = $(`#qb_duration_${memberId}`).val();
    
    console.log('Form values:', {activity_id, date, duration});

    if(!activity_id || !date) {
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.select_activity_date_first') }}',
            confirmButtonText: 'Ok'
        });
        return false;
    }

    const btn = $(this);
    btn.prop('disabled', true).html('<i class="ki-outline ki-loading fs-2"></i> {{ trans('sw.loading') }}...');
    $(`#qb_slots_${memberId}`).html(`
        <div class="slots-loading-state">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">{{ trans('sw.loading') }}...</span>
            </div>
            <div class="text-muted mt-3 fw-semibold">{{ trans('sw.loading_slots') }}...</div>
        </div>
    `);
    
    console.log('Sending request to:', "{{ route('sw.reservation.slots') }}");

    $.ajax({
        url: "{{ route('sw.reservation.slots') }}",
        type: 'POST',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: {
            activity_id: activity_id, 
            reservation_date: date, 
            duration: duration
        },
        success: function(resp) {
            console.log('Response received:', resp);
            btn.prop('disabled', false).html('<i class="ki-outline ki-magnifier fs-2"></i> {{ trans('sw.show_available_slots') }}');
            $(`#qb_slots_${memberId}`).empty();
        
            // Check if day is available
            if (resp.day_available === false) {
                $(`#qb_slots_${memberId}`).html(`
                    <div class="slots-empty-state">
                        <i class="ki-outline ki-calendar-tick"></i>
                        <div class="empty-title">{{ trans('sw.day_not_available_for_reservation') }}</div>
                        <div class="empty-subtitle">{{ trans('sw.please_select_different_date') }}</div>
                    </div>
                `);
                return;
            }

            if (resp.slots && resp.slots.length > 0) {
                const slotsContainer = $('<div class="time-slots-container"></div>');
                let availableCount = 0;
                let occupiedCount = 0;
                
                resp.slots.forEach(function(slot){
                    const slotBtn = $('<button type="button" class="slot-btn"></button>');
                    const hasLimit = resp.has_limit || false;
                    const limit = resp.reservation_limit || 0;
                    const current = slot.current_bookings || 0;
                    const remaining = slot.remaining_slots;
                    
                    let timeText = `<span><i class="ki-outline ki-time fs-6"></i> ${slot.start_time} - ${slot.end_time}</span>`;
                    
                    if (hasLimit && slot.available) {
                        timeText += `<small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                            ${remaining > 0 ? remaining + ' {{ trans("sw.slots_remaining") }}' : '{{ trans("sw.last_slot") }}'}
                        </small>`;
                    } else if (hasLimit && !slot.available) {
                        timeText += `<small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                            {{ trans("sw.limit_reached") }} (${current}/${limit})
                        </small>`;
                    }
                    
                    if(slot.available){
                        availableCount++;
                        slotBtn.addClass('slot-free qb-select-slot-member')
                               .attr('data-start', slot.start_time)
                               .attr('data-end', slot.end_time)
                               .attr('data-member-id', memberId)
                               .html(timeText);
                    } else {
                        occupiedCount++;
                        slotBtn.addClass('slot-busy')
                               .prop('disabled', true)
                               .html(timeText);
                    }
                    
                    slotsContainer.append(slotBtn);
                });
                
                const summaryHtml = resp.has_limit 
                    ? `
                        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light-primary rounded">
                            <div class="d-flex align-items-center gap-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-circle badge-light-success"></span>
                                    <span class="text-gray-700 fw-semibold">{{ trans('sw.available') }}: ${availableCount}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-circle badge-light-secondary"></span>
                                    <span class="text-gray-700 fw-semibold">{{ trans('sw.occupied') }}: ${occupiedCount}</span>
                                </div>
                            </div>
                            <div class="text-gray-600 fw-semibold">
                                <i class="ki-outline ki-user fs-6 me-1"></i>
                                {{ trans('sw.reservation_limit') }}: ${resp.reservation_limit}
                            </div>
                        </div>
                    `
                    : `
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-circle badge-light-success"></span>
                                    <span class="text-gray-700 fw-semibold">{{ trans('sw.available') }}: ${availableCount}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-circle badge-light-secondary"></span>
                                    <span class="text-gray-700 fw-semibold">{{ trans('sw.occupied') }}: ${occupiedCount}</span>
                                </div>
                            </div>
                        </div>
                    `;
                
                const summary = $(summaryHtml);
                $(`#qb_slots_${memberId}`).append(summary).append(slotsContainer);
                
                // If editing a reservation, auto-select the matching time slot
                const currentReservationId = $(`#qb_reservation_id_${memberId}`).val();
                if (currentReservationId) {
                    // Get reservation times from modal data attributes
                    const reservationStartTime = $(`#quickBookModal${memberId}`).data('reservation-start-time');
                    const reservationEndTime = $(`#quickBookModal${memberId}`).data('reservation-end-time');
                    
                    if (reservationStartTime && reservationEndTime) {
                        // Small delay to ensure DOM is fully rendered
                        setTimeout(function() {
                            const matchingSlot = $(`.qb-select-slot-member[data-member-id="${memberId}"][data-start="${reservationStartTime}"][data-end="${reservationEndTime}"]`);
                            if (matchingSlot.length > 0) {
                                // Remove active class from all slots first
                                $(`.qb-select-slot-member[data-member-id="${memberId}"]`).removeClass('active');
                                // Add active class and click the matching slot
                                matchingSlot.first().addClass('active').click();
                            }
                        }, 100);
                    }
                }
            } else {
                $(`#qb_slots_${memberId}`).html(`
                    <div class="slots-empty-state">
                        <i class="ki-outline ki-calendar-tick"></i>
                        <div class="empty-title">{{ trans('sw.no_slots_available') }}</div>
                        <div class="empty-subtitle">{{ trans('sw.try_different_date') }}</div>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error loading slots:', {xhr, status, error});
            btn.prop('disabled', false).html('<i class="ki-outline ki-magnifier fs-2"></i> {{ trans('sw.show_available_slots') }}');
            let errorMsg = '{{ trans('sw.error_loading_slots') }}';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $(`#qb_slots_${memberId}`).html(`
                <div class="slots-error-state">
                    <i class="ki-outline ki-cross-circle"></i>
                    <div class="error-title">${errorMsg}</div>
                    <div class="error-subtitle">${error || '{{ trans('sw.please_try_again') }}'}</div>
                </div>
            `);
        }
    });
});

// Load slots for non-member modal
$(document).on('click', '.qb-load-slots-btn[data-non-member-id]', function(e){
    e.preventDefault();
    e.stopPropagation();
    
    const nonMemberId = $(this).data('non-member-id');
    console.log('Button clicked! Loading slots for non-member:', nonMemberId);
    
    let activity_id;
    const activitySelect = $(`#qb_activity_${nonMemberId}`);
    
    if (activitySelect.length === 0) {
        console.error('Activity select not found for non-member:', nonMemberId);
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: 'Activity select not found',
            confirmButtonText: 'Ok'
        });
        return false;
    }
    
    if (activitySelect.hasClass('select2-hidden-accessible')) {
        activity_id = activitySelect.select2('val');
    } else {
        activity_id = activitySelect.val();
    }
    
    const date = $(`#qb_date_${nonMemberId}`).val();
    const duration = $(`#qb_duration_${nonMemberId}`).val();
    
    console.log('Form values:', {activity_id, date, duration});

    if(!activity_id || !date) {
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.select_activity_date_first') }}',
            confirmButtonText: 'Ok'
        });
        return false;
    }

    const btn = $(this);
    btn.prop('disabled', true).html('<i class="ki-outline ki-loading fs-2"></i> {{ trans('sw.loading') }}...');
    $(`#qb_slots_${nonMemberId}`).html(`
        <div class="slots-loading-state">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">{{ trans('sw.loading') }}...</span>
            </div>
            <div class="text-muted mt-3 fw-semibold">{{ trans('sw.loading_slots') }}...</div>
        </div>
    `);
    
    console.log('Sending request to:', "{{ route('sw.reservation.slots') }}");

    $.ajax({
        url: "{{ route('sw.reservation.slots') }}",
        type: 'POST',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: {
            activity_id: activity_id, 
            reservation_date: date, 
            duration: duration
        },
        success: function(resp) {
            console.log('Response received:', resp);
            btn.prop('disabled', false).html('<i class="ki-outline ki-magnifier fs-2"></i> {{ trans('sw.show_available_slots') }}');
            $(`#qb_slots_${nonMemberId}`).empty();
        
            // Check if day is available
            if (resp.day_available === false) {
                $(`#qb_slots_${nonMemberId}`).html(`
                    <div class="slots-empty-state">
                        <i class="ki-outline ki-calendar-tick"></i>
                        <div class="empty-title">{{ trans('sw.day_not_available_for_reservation') }}</div>
                        <div class="empty-subtitle">{{ trans('sw.please_select_different_date') }}</div>
                    </div>
                `);
                return;
            }

            if (resp.slots && resp.slots.length > 0) {
                const slotsContainer = $('<div class="time-slots-container"></div>');
                let availableCount = 0;
                let occupiedCount = 0;
                
                resp.slots.forEach(function(slot){
                    const slotBtn = $('<button type="button" class="slot-btn"></button>');
                    const hasLimit = resp.has_limit || false;
                    const limit = resp.reservation_limit || 0;
                    const current = slot.current_bookings || 0;
                    const remaining = slot.remaining_slots;
                    
                    let timeText = `<span><i class="ki-outline ki-time fs-6"></i> ${slot.start_time} - ${slot.end_time}</span>`;
                    
                    if (hasLimit && slot.available) {
                        timeText += `<small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                            ${remaining > 0 ? remaining + ' {{ trans("sw.slots_remaining") }}' : '{{ trans("sw.last_slot") }}'}
                        </small>`;
                    } else if (hasLimit && !slot.available) {
                        timeText += `<small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                            {{ trans("sw.limit_reached") }} (${current}/${limit})
                        </small>`;
                    }
                    
                    if(slot.available){
                        availableCount++;
                        slotBtn.addClass('slot-free qb-select-slot-nonmember')
                               .attr('data-start', slot.start_time)
                               .attr('data-end', slot.end_time)
                               .attr('data-non-member-id', nonMemberId)
                               .html(timeText);
                    } else {
                        occupiedCount++;
                        slotBtn.addClass('slot-busy')
                               .prop('disabled', true)
                               .html(timeText);
                    }
                    
                    slotsContainer.append(slotBtn);
                });
                
                const summaryHtml = resp.has_limit 
                    ? `
                        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light-primary rounded">
                            <div class="d-flex align-items-center gap-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-circle badge-light-success"></span>
                                    <span class="text-gray-700 fw-semibold">{{ trans('sw.available') }}: ${availableCount}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-circle badge-light-secondary"></span>
                                    <span class="text-gray-700 fw-semibold">{{ trans('sw.occupied') }}: ${occupiedCount}</span>
                                </div>
                            </div>
                            <div class="text-gray-600 fw-semibold">
                                <i class="ki-outline ki-user fs-6 me-1"></i>
                                {{ trans('sw.reservation_limit') }}: ${resp.reservation_limit}
                            </div>
                        </div>
                    `
                    : `
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-circle badge-light-success"></span>
                                    <span class="text-gray-700 fw-semibold">{{ trans('sw.available') }}: ${availableCount}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-circle badge-light-secondary"></span>
                                    <span class="text-gray-700 fw-semibold">{{ trans('sw.occupied') }}: ${occupiedCount}</span>
                                </div>
                            </div>
                        </div>
                    `;
                
                const summary = $(summaryHtml);
                $(`#qb_slots_${nonMemberId}`).append(summary).append(slotsContainer);
                
                // If editing a reservation, auto-select the matching time slot
                const currentReservationId = $(`#qb_reservation_id_${nonMemberId}`).val();
                if (currentReservationId) {
                    // Get reservation times from modal data attributes
                    const reservationStartTime = $(`#quickBookModal${nonMemberId}`).data('reservation-start-time');
                    const reservationEndTime = $(`#quickBookModal${nonMemberId}`).data('reservation-end-time');
                    
                    if (reservationStartTime && reservationEndTime) {
                        // Small delay to ensure DOM is fully rendered
                        setTimeout(function() {
                            const matchingSlot = $(`.qb-select-slot-nonmember[data-non-member-id="${nonMemberId}"][data-start="${reservationStartTime}"][data-end="${reservationEndTime}"]`);
                            if (matchingSlot.length > 0) {
                                // Remove active class from all slots first
                                $(`.qb-select-slot-nonmember[data-non-member-id="${nonMemberId}"]`).removeClass('active');
                                // Add active class and click the matching slot
                                matchingSlot.first().addClass('active').click();
                            }
                        }, 100);
                    }
                }
            } else {
                $(`#qb_slots_${nonMemberId}`).html(`
                    <div class="slots-empty-state">
                        <i class="ki-outline ki-calendar-tick"></i>
                        <div class="empty-title">{{ trans('sw.no_slots_available') }}</div>
                        <div class="empty-subtitle">{{ trans('sw.try_different_date') }}</div>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error loading slots:', {xhr, status, error});
            btn.prop('disabled', false).html('<i class="ki-outline ki-magnifier fs-2"></i> {{ trans('sw.show_available_slots') }}');
            let errorMsg = '{{ trans('sw.error_loading_slots') }}';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $(`#qb_slots_${nonMemberId}`).html(`
                <div class="slots-error-state">
                    <i class="ki-outline ki-cross-circle"></i>
                    <div class="error-title">${errorMsg}</div>
                    <div class="error-subtitle">${error || '{{ trans('sw.please_try_again') }}'}</div>
                </div>
            `);
        }
    });
});

// Choose slot for member modal
$(document).on('click', '.qb-select-slot-member', function(){
    const memberId = $(this).data('member-id');
    $(`.qb-select-slot-member[data-member-id="${memberId}"]`).removeClass('active');
    $(this).addClass('active');
});

// Choose slot for non-member modal
$(document).on('click', '.qb-select-slot-nonmember', function(){
    const nonMemberId = $(this).data('non-member-id');
    $(`.qb-select-slot-nonmember[data-non-member-id="${nonMemberId}"]`).removeClass('active');
    $(this).addClass('active');
});

// Book now for member (create or update)
$(document).on('click', '.qb-book-btn[data-member-id]', function(){
    const memberId = $(this).data('member-id');
    const reservationId = $(`#qb_reservation_id_${memberId}`).val();
    const activity_id = $(`#qb_activity_${memberId}`).val();
    const date = $(`#qb_date_${memberId}`).val();
    const selected = $(`.qb-select-slot-member[data-member-id="${memberId}"].active`);
    const member_id = $(`#qb_member_id_${memberId}`).val();
    
    if(!activity_id || !date) {
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.select_activity_date_first') }}',
            confirmButtonText: 'Ok'
        });
        return;
    }
    
    if(selected.length === 0) {
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.select_slot') }}',
            confirmButtonText: 'Ok'
        });
        return;
    }
    
    const start_time = selected.data('start');
    const end_time = selected.data('end');
    const notes = $(`#qb_notes_${memberId}`).val();

    const payload = {
        client_type: 'member',
        member_id: member_id,
        non_member_id: null,
        activity_id: activity_id,
        reservation_date: date,
        start_time: start_time,
        end_time: end_time,
        notes: notes
    };

    const btn = $(this);
    const btnText = btn.find('.qb-book-btn-text');
    const isUpdate = reservationId && reservationId !== '';
    const url = isUpdate 
        ? "{{ route('sw.reservation.ajaxUpdate', ':id') }}".replace(':id', reservationId)
        : "{{ route('sw.reservation.ajaxCreate') }}";
    
    btn.prop('disabled', true);
    btnText.text('{{ trans('sw.booking') }}...');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(async r => {
        if(r.status === 422){
            const j = await r.json();
            btn.prop('disabled', false);
            btnText.text(isUpdate ? '{{ trans('sw.update') }}' : '{{ trans('sw.book_now') }}');
            Swal.fire({
                icon: 'error',
                title: '{{ trans('sw.error') }}',
                text: j.message || '{{ trans('sw.slot_conflict') }}',
                confirmButtonText: 'Ok'
            });
            return;
        }
        return r.json();
    })
    .then(res => {
        if(res && res.success){
            // Close modal immediately after successful reservation
            $(`#quickBookModal${memberId}`).modal('hide');
            
            Swal.fire({
                icon: 'success',
                title: '{{ trans('admin.done') }}',
                text: isUpdate ? '{{ trans('admin.successfully_edited') }}' : '{{ trans('sw.reservation_created') }}',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        }
    })
    .catch(() => {
        btn.prop('disabled', false);
        btnText.text(isUpdate ? '{{ trans('sw.update') }}' : '{{ trans('sw.book_now') }}');
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.booking_failed') }}',
            confirmButtonText: 'Ok'
        });
    });
});

// Book now for non-member (create or update)
$(document).on('click', '.qb-book-btn[data-non-member-id]', function(){
    const nonMemberId = $(this).data('non-member-id');
    const reservationId = $(`#qb_reservation_id_${nonMemberId}`).val();
    const activity_id = $(`#qb_activity_${nonMemberId}`).val();
    const date = $(`#qb_date_${nonMemberId}`).val();
    const selected = $(`.qb-select-slot-nonmember[data-non-member-id="${nonMemberId}"].active`);
    const non_member_id = $(`#qb_nonmember_id_${nonMemberId}`).val();
    
    if(!activity_id || !date) {
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.select_activity_date_first') }}',
            confirmButtonText: 'Ok'
        });
        return;
    }
    
    if(selected.length === 0) {
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.select_slot') }}',
            confirmButtonText: 'Ok'
        });
        return;
    }
    
    const start_time = selected.data('start');
    const end_time = selected.data('end');
    const notes = $(`#qb_notes_${nonMemberId}`).val();

    const payload = {
        client_type: 'non_member',
        member_id: null,
        non_member_id: non_member_id,
        activity_id: activity_id,
        reservation_date: date,
        start_time: start_time,
        end_time: end_time,
        notes: notes
    };

    const btn = $(this);
    const btnText = btn.find('.qb-book-btn-text');
    const isUpdate = reservationId && reservationId !== '';
    const url = isUpdate 
        ? "{{ route('sw.reservation.ajaxUpdate', ':id') }}".replace(':id', reservationId)
        : "{{ route('sw.reservation.ajaxCreate') }}";
    
    btn.prop('disabled', true);
    btnText.text('{{ trans('sw.booking') }}...');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(async r => {
        if(r.status === 422){
            const j = await r.json();
            btn.prop('disabled', false);
            btnText.text(isUpdate ? '{{ trans('sw.update') }}' : '{{ trans('sw.book_now') }}');
            Swal.fire({
                icon: 'error',
                title: '{{ trans('sw.error') }}',
                text: j.message || '{{ trans('sw.slot_conflict') }}',
                confirmButtonText: 'Ok'
            });
            return;
        }
        return r.json();
    })
    .then(res => {
        if(res && res.success){
            // Close modal immediately after successful reservation
            $(`#quickBookModal${nonMemberId}`).modal('hide');
            
            Swal.fire({
                icon: 'success',
                title: '{{ trans('admin.done') }}',
                text: isUpdate ? '{{ trans('admin.successfully_edited') }}' : '{{ trans('sw.reservation_created') }}',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        }
    })
    .catch(() => {
        btn.prop('disabled', false);
        btnText.text(isUpdate ? '{{ trans('sw.update') }}' : '{{ trans('sw.book_now') }}');
        Swal.fire({
            icon: 'error',
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.booking_failed') }}',
            confirmButtonText: 'Ok'
        });
    });
});

// Store initial status when page loads and when modal opens
$(document).ready(function(){
    // Store initial values for all status selects
    $('.reservation-status-select').each(function(){
        const currentVal = $(this).val();
        const oldValueAttr = $(this).attr('data-old-value');
        if (oldValueAttr) {
            $(this).data('old-value', oldValueAttr);
        } else if (!$(this).data('old-value')) {
            $(this).data('old-value', currentVal);
        }
    });
    
    // Re-store values when modal is shown
    $('[id^="upcomingReservationsModal"]').on('shown.bs.modal', function(){
        $(this).find('.reservation-status-select').each(function(){
            const select = $(this);
            const currentVal = select.val();
            const oldValueAttr = select.attr('data-old-value');
            
            // Use attribute value if available, otherwise use current value
            if (oldValueAttr) {
                select.data('old-value', oldValueAttr);
            } else {
                select.data('old-value', currentVal);
            }
            
            console.log('Modal opened - stored old-value:', select.data('old-value'), 'for reservation:', select.data('reservation-id'));
        });
    });
});

// Change reservation status in upcoming reservations modal
$(document).on('change', '.reservation-status-select', function(e){
    console.log('=== STATUS SELECT CHANGE EVENT TRIGGERED ===');
    e.preventDefault();
    e.stopPropagation();
    
    const reservationId = $(this).data('reservation-id');
    const select = $(this);
    const newStatus = select.val();
    let oldValue = select.data('old-value') || select.attr('data-old-value');
    
    console.log('Initial values:', { reservationId, newStatus, oldValue });
    
    // If old-value is not set, get it from the select's initial value
    if (!oldValue) {
        oldValue = select.find('option[selected]').val() || select.val();
        select.data('old-value', oldValue);
        console.log('Old value not found, using:', oldValue);
    }
    
    console.log('Status changed:', { reservationId, newStatus, oldValue, selectElement: select });
    
    // If status didn't change, do nothing
    if (newStatus === oldValue) {
        console.log('Status unchanged, ignoring');
        select.val(oldValue); // Revert to old value
        return;
    }
    
    console.log('Showing confirmation dialog...');
    
    // Show confirmation dialog with Yes/No buttons using SweetAlert2
    Swal.fire({
        title: '{{ trans('admin.are_you_sure') }}',
        text: '{{ trans('sw.change_status_confirmation') }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: '{{ trans('admin.yes') }}',
        cancelButtonText: '{{ trans('sw.no') }}',
        allowOutsideClick: false,
        reverseButtons: true
    }).then((result) => {
        console.log('Confirmation result:', result);
        if (!result.isConfirmed) {
            // User cancelled, revert to old value
            console.log('User cancelled, reverting to:', oldValue);
            select.val(oldValue);
            return;
        }
        
        // Determine which action to use based on new status
        let url = '';
        
        if (newStatus === 'confirmed') {
            url = "{{ route('sw.reservation.confirm', ':id') }}".replace(':id', reservationId);
        } else if (newStatus === 'cancelled') {
            url = "{{ route('sw.reservation.cancel', ':id') }}".replace(':id', reservationId);
        } else if (newStatus === 'attended') {
            url = "{{ route('sw.reservation.attend', ':id') }}".replace(':id', reservationId);
        } else if (newStatus === 'missed') {
            url = "{{ route('sw.reservation.missed', ':id') }}".replace(':id', reservationId);
        } else if (newStatus === 'pending') {
            // Revert to old value
            select.val(oldValue);
            Swal.fire({
                title: '{{ trans('admin.info') }}',
                text: '{{ trans('sw.pending_status_not_supported') }}',
                icon: 'info',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        if (!url) {
            select.val(oldValue);
            Swal.fire({
                title: '{{ trans('sw.error') }}',
                text: '{{ trans('sw.invalid_status') }}',
                icon: 'error',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        // Disable select during request
        select.prop('disabled', true);
        
        console.log('Sending AJAX request to:', url);
        
        $.ajax({
            url: url,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(response){
                console.log('AJAX success response:', response);
                
                if(response && response.success && response.status){
                    // Update old value to new status
                    select.data('old-value', response.status);
                    
                    // Update select value to match new status
                    select.val(response.status);
                    
                    // Update badge color if badge exists
                    const card = select.closest('.card');
                    if (card.length) {
                        const badge = card.find('.badge').first();
                        if (badge.length) {
                            const colors = {
                                'confirmed': 'success',
                                'pending': 'warning',
                                'cancelled': 'danger',
                                'attended': 'primary',
                                'missed': 'secondary'
                            };
                            badge.removeClass('badge-success badge-warning badge-danger badge-primary badge-secondary badge-dark')
                                  .addClass('badge-' + (colors[response.status] || 'dark'));
                        }
                    }
                    
                    // Show success message and close modal
                    Swal.fire({
                        title: '{{ trans('admin.done') }}',
                        text: '{{ trans('sw.status_changed_successfully') }}',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Close the upcoming reservations modal
                        const modalId = select.closest('[id^="upcomingReservationsModal"]').attr('id');
                        if (modalId) {
                            $('#' + modalId).modal('hide');
                        }
                    });
                } else {
                    // Revert to old value
                    select.val(oldValue);
                    Swal.fire({
                        title: '{{ trans('sw.error') }}',
                        text: response.message || '{{ trans('sw.status_change_failed') }}',
                        icon: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr, status, error){
                console.error('AJAX error:', { xhr, status, error });
                // Revert to old value
                select.val(oldValue);
                
                let errorMsg = '{{ trans('sw.status_change_failed') }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: '{{ trans('sw.error') }}',
                    text: errorMsg,
                    icon: 'error',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            complete: function(){
                select.prop('disabled', false);
            }
        });
    });
});

// Edit reservation button - opens quick modal with reservation data
$(document).on('click', '.reservation-edit-btn', function(){
    const reservationId = $(this).data('reservation-id');
    const memberId = $(this).data('member-id');
    const nonMemberId = $(this).data('non-member-id');
    
    if (memberId) {
        // Close upcoming reservations modal for member
        $(`#upcomingReservationsModal${memberId}`).modal('hide');
        
        // Fetch reservation data
        $.ajax({
            url: "{{ route('sw.reservation.ajaxGet', ':id') }}".replace(':id', reservationId),
            type: 'GET',
            success: function(res){
                if(res.success && res.data){
                    const data = res.data;
                    
                    // Set reservation ID
                    $(`#qb_reservation_id_${memberId}`).val(data.id);
                    
                    // Populate form fields
                    $(`#qb_activity_${memberId}`).val(data.activity_id).trigger('change');
                    $(`#qb_date_${memberId}`).val(data.reservation_date);
                    
                    // Calculate duration from start and end time
                    const start = data.start_time.split(':');
                    const end = data.end_time.split(':');
                    const startMinutes = parseInt(start[0]) * 60 + parseInt(start[1]);
                    const endMinutes = parseInt(end[0]) * 60 + parseInt(end[1]);
                    const duration = endMinutes - startMinutes;
                    $(`#qb_duration_${memberId}`).val(duration);
                    
                    $(`#qb_notes_${memberId}`).val(data.notes || '');
                    
                    // Update button text
                    $(`#quickBookModal${memberId} .qb-book-btn-text`).text('{{ trans('sw.update') }}');
                    
                    // Store reservation time in modal data attributes for slot selection after loading
                    $(`#quickBookModal${memberId}`).data('reservation-start-time', data.start_time);
                    $(`#quickBookModal${memberId}`).data('reservation-end-time', data.end_time);
                    
                    // Open quick modal first
                    $(`#quickBookModal${memberId}`).modal('show');
                    
                    // Wait for modal to be fully shown, then automatically load slots
                    $(`#quickBookModal${memberId}`).one('shown.bs.modal', function() {
                        // Trigger slots loading after a short delay to ensure select2 is ready
                        setTimeout(function(){
                            // Click load slots button
                            $(`#quickBookModal${memberId} .qb-load-slots-btn`).click();
                        }, 300);
                    });
                }
            },
            error: function(){
                Swal.fire({
                    icon: 'error',
                    title: '{{ trans('sw.error') }}',
                    text: '{{ trans('sw.failed_to_load_reservation') }}',
                    confirmButtonText: 'Ok'
                });
            }
        });
    } else if (nonMemberId) {
        // Close upcoming reservations modal for non-member
        $(`#upcomingReservationsModal${nonMemberId}`).modal('hide');
        
        // Fetch reservation data
        $.ajax({
            url: "{{ route('sw.reservation.ajaxGet', ':id') }}".replace(':id', reservationId),
            type: 'GET',
            success: function(res){
                if(res.success && res.data){
                    const data = res.data;
                    
                    // Set reservation ID
                    $(`#qb_reservation_id_${nonMemberId}`).val(data.id);
                    
                    // Populate form fields
                    $(`#qb_activity_${nonMemberId}`).val(data.activity_id).trigger('change');
                    $(`#qb_date_${nonMemberId}`).val(data.reservation_date);
                    
                    // Calculate duration from start and end time
                    const start = data.start_time.split(':');
                    const end = data.end_time.split(':');
                    const startMinutes = parseInt(start[0]) * 60 + parseInt(start[1]);
                    const endMinutes = parseInt(end[0]) * 60 + parseInt(end[1]);
                    const duration = endMinutes - startMinutes;
                    $(`#qb_duration_${nonMemberId}`).val(duration);
                    
                    $(`#qb_notes_${nonMemberId}`).val(data.notes || '');
                    
                    // Update button text
                    $(`#quickBookModal${nonMemberId} .qb-book-btn-text`).text('{{ trans('sw.update') }}');
                    
                    // Store reservation time in modal data attributes for slot selection after loading
                    $(`#quickBookModal${nonMemberId}`).data('reservation-start-time', data.start_time);
                    $(`#quickBookModal${nonMemberId}`).data('reservation-end-time', data.end_time);
                    
                    // Open quick modal first
                    $(`#quickBookModal${nonMemberId}`).modal('show');
                    
                    // Wait for modal to be fully shown, then automatically load slots
                    $(`#quickBookModal${nonMemberId}`).one('shown.bs.modal', function() {
                        // Trigger slots loading after a short delay to ensure select2 is ready
                        setTimeout(function(){
                            // Click load slots button
                            $(`#quickBookModal${nonMemberId} .qb-load-slots-btn`).click();
                        }, 300);
                    });
                }
            },
            error: function(){
                Swal.fire({
                    icon: 'error',
                    title: '{{ trans('sw.error') }}',
                    text: '{{ trans('sw.failed_to_load_reservation') }}',
                    confirmButtonText: 'Ok'
                });
            }
        });
    }
});

@if($active_activity_reservation && (Route::current()->getName() == 'sw.showOrderSubscription') && (in_array('createReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user))
    // Reset member modal when closed
    $('#quickBookModal{{ $order->member_id }}').on('hidden.bs.modal', function () {
        $('#qb_reservation_id_{{ $order->member_id }}').val('');
        $('#qb_activity_{{ $order->member_id }}').val(null).trigger('change');
        $('#qb_date_{{ $order->member_id }}').val('');
        $('#qb_duration_{{ $order->member_id }}').val('60');
        $('#qb_notes_{{ $order->member_id }}').val('');
        $('#qb_slots_{{ $order->member_id }}').html(`
            <div class="slots-empty-state">
                <i class="ki-outline ki-calendar-tick"></i>
                <div class="empty-title">{{ trans('sw.select_activity_date_to_show_slots') }}</div>
                <div class="empty-subtitle">{{ trans('sw.choose_activity_and_date_first') }}</div>
            </div>
        `);
        $(`.qb-select-slot-member[data-member-id="{{ $order->member_id }}"]`).removeClass('active');
        $(`#quickBookModal{{ $order->member_id }} .qb-book-btn-text`).text('{{ trans('sw.book_now') }}');
    });
@endif

@if($active_activity_reservation && (Route::current()->getName() == 'sw.showOrderSubscriptionNonMember') && (in_array('createReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user))
    // Reset non-member modal when closed
    $('#quickBookModal{{ $order->id }}').on('hidden.bs.modal', function () {
        $('#qb_reservation_id_{{ $order->id }}').val('');
        $('#qb_activity_{{ $order->id }}').val(null).trigger('change');
        $('#qb_date_{{ $order->id }}').val('');
        $('#qb_duration_{{ $order->id }}').val('60');
        $('#qb_notes_{{ $order->id }}').val('');
        $('#qb_slots_{{ $order->id }}').html(`
            <div class="slots-empty-state">
                <i class="ki-outline ki-calendar-tick"></i>
                <div class="empty-title">{{ trans('sw.select_activity_date_to_show_slots') }}</div>
                <div class="empty-subtitle">{{ trans('sw.choose_activity_and_date_first') }}</div>
            </div>
        `);
        $(`.qb-select-slot-nonmember[data-non-member-id="{{ $order->id }}"]`).removeClass('active');
        $(`#quickBookModal{{ $order->id }} .qb-book-btn-text`).text('{{ trans('sw.book_now') }}');
    });
@endif

// Auto-update duration when activity is selected
$(document).on('change', '.qb-activity-select', function(){
    const memberId = $(this).data('member-id');
    const nonMemberId = $(this).data('non-member-id');
    const selectedOption = $(this).find('option:selected');
    const duration = selectedOption.data('duration');
    
    if (duration) {
        if (memberId) {
            $(`#qb_duration_${memberId}`).val(duration);
        } else if (nonMemberId) {
            $(`#qb_duration_${nonMemberId}`).val(duration);
        }
    }
});
</script>

@endif
@endsection


