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
        <li class="breadcrumb-item">
            <a href="{{ route('sw.listMoneyBox').'?search='.$order->id }}" class="text-muted text-hover-primary">{{ trans('sw.report') }}</a>
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
@endsection
@section('form_title') {{ @$title }} @endsection
@section('page_body')
    {{--    {!! \DNS2D::getBarcodeHTML('AQVTYWxsYQIKMTIzNDU2Nzg5MQMUMjAyMS0wNy0xMlQxNDoyNTowOVoEBjEwMC4wMAUFMTUuMDA=', 'QRCODE') !!}--}}

    <!-- begin::Invoice 3-->
    <div class="card invoice-container">
        <!-- begin::Body-->
        <div class="card-body py-20">
            <!-- begin::Wrapper-->
            <div class="mw-lg-950px mx-auto w-100">
                <!-- begin::Header-->
                <div class="d-flex justify-content-between flex-column flex-sm-row mb-19">
                    <h4 class="fw-bolder text-gray-800 fs-2qx pe-5 pb-7">{{ trans('sw.invoice') }}</h4>
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
                        <div class="fw-bold fs-2">{{ $title_details }}</div>
                        <!--begin::Message-->
                        <!--begin::Separator-->
                        <div class="separator"></div>
                        <!--begin::Separator-->
                        <!--begin::Order details-->
                        <div class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold">
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted"><i class="ki-outline ki-hash fs-6 me-1"></i>{{ app()->getLocale() == 'ar' ? 'رقم الفاتورة' : trans('sw.invoice_numbers') }}</span>
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
                                <span class="text-muted"><i class="ki-outline ki-dollar fs-6 me-1"></i>{{ trans('sw.payment_type') }}</span>
                                <span class="fs-5">{{ get_payment_type_name(@$order->payment_type, $payment_types)}}</span>
                            </div>
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted"><i class="ki-outline ki-bill fs-6 me-1"></i>{{ trans('sw.invoice_total_required') }}</span>
                                <span class="fs-5">{{$order->operation == \Modules\Software\Classes\TypeConstants::Add ? '' : '-'}} {{number_format($order->amount, 2)}} {{@trans('sw.app_currency')}}</span>
                            </div>
                        </div>
                        <!--end::Order details-->
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
                            <!-- Empty column to match 4 columns layout -->
                            <div class="flex-root d-flex flex-column" style="max-width: calc(25% - 20px);"></div>
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
                                            <th class="min-w-175px pb-2">{{ $title_details }}</th>
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
                                                        <span class="symbol-label {{ $order->operation == \Modules\Software\Classes\TypeConstants::Add ? 'bg-light-success' : 'bg-light-danger' }}">
                                                            <i class="ki-outline {{ $order->operation == \Modules\Software\Classes\TypeConstants::Add ? 'ki-plus-circle text-success' : 'ki-minus-circle text-danger' }} fs-2x"></i>
                                                        </span>
                                                    </div>
                                                    <!--end::Thumbnail-->
                                                    <!--begin::Title-->
                                                    <div class="ms-5">
                                                        <div class="fw-bold">{{ $order->operation == \Modules\Software\Classes\TypeConstants::Add ? trans('sw.add') : trans('sw.subtract') }}</div>
                                                        <div class="fs-7 text-muted">{{$order->notes}}</div>
                                                    </div>
                                                    <!--end::Title-->
                                                </div>
                                            </td>
                                            <td class="text-end">{{$order->operation == \Modules\Software\Classes\TypeConstants::Add ? '' : '-'}} {{number_format($order->amount, 2)}} {{@trans('sw.app_currency')}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-end">{{ trans('sw.invoice_total') }}</td>
                                            <td class="text-end">{{$order->operation == \Modules\Software\Classes\TypeConstants::Add ? '' : '-'}} {{number_format(($order->amount - @$order->vat),2)}} {{@trans('sw.app_currency')}}</td>
                                        </tr>
                                        @if(@$order->store_order && @$order->store_order->loyaltyRedemption)
                                        <tr>
                                            <td colspan="2" class="text-end">
                                                <i class="ki-outline ki-gift text-primary me-1"></i>{{ trans('sw.loyalty_discount') }}
                                                <span class="text-muted fs-7">({{ abs($order->store_order->loyaltyRedemption->points) }} {{ trans('sw.points')}})</span>
                                            </td>
                                            <td class="text-end text-primary fw-bold">
                                                -{{number_format(abs($order->store_order->loyaltyRedemption->points) * (@$order->store_order->loyaltyRedemption->rule->point_to_money_rate ?? 0), 2)}} {{@trans('sw.app_currency')}}
                                            </td>
                                        </tr>
                                        @endif
                                        @if(@$order->vat)
                                        <tr>
                                            <td colspan="2" class="text-end">{{ trans('sw.vat_total') }} ({{@$mainSettings->vat_details['vat_percentage'].'%'}})</td>
                                            <td class="text-end">{{$order->operation == \Modules\Software\Classes\TypeConstants::Add ? '' : '-'}} {{@number_format($order->vat, 2)}} {{@trans('sw.app_currency')}}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td colspan="2" class="fs-3 text-gray-900 fw-bold text-end">{{ trans('sw.invoice_total_required') }}</td>
                                            <td class="text-gray-900 fs-3 fw-bolder text-end">{{$order->operation == \Modules\Software\Classes\TypeConstants::Add ? '' : '-'}} {{number_format($order->amount, 2)}} {{@trans('sw.app_currency')}}</td>
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
                                    <div style="color: lightgray;">----------------</div>
                                    <div class="fw-bold mt-4 mb-2">{{ trans('sw.signature') }}:</div>
                                    <div style="color: lightgray;">----------------</div>
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
                        <!-- begin::Print-->
                        <button type="button" class="btn btn-success my-1 {{ app()->getLocale() == 'ar' ? 'ms-12' : 'me-12' }}" onclick="window.print();">
                            <i class="fa fa-print {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i> {{ trans('sw.print') }}
                        </button>
                        <!-- end::Print-->
                        <!-- begin::POS View-->
                        <button type="button" class="btn btn-light-success btn-icon my-1" onclick="javascript:window.open('{{route('sw.showOrderPOS', @$order->id)}}', 'POS','height=600,width=700');" title="POS {{ trans('sw.view') }}">
                            <i class="ki-outline ki-file fs-2"></i>
                        </button>
                        <!-- end::POS View-->
                    </div>
                    <!-- end::Actions-->
                    @if(@$mainSettings->vat_details['saudi'] && @$qr_img_invoice)
                    <!-- begin::QR Code-->
                    <div class="my-1 d-flex flex-column align-items-center">
                        <img class="well" src="{{asset($qr_img_invoice)}}" style="height: 60px;"/>
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


@endsection
@section('sub_scripts')

@endsection
