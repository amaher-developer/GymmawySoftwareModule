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
    @if($lang=='ar')
        <link href="{{asset('resources/assets/new_front/pages/css/invoice-rtl.css')}}" rel="stylesheet" type="text/css"/>
    @else
        <link href="{{asset('resources/assets/new_front/pages/css/invoice.css')}}" rel="stylesheet" type="text/css"/>
    @endif

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Modern Invoice Container */
        .invoice {
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 40px;
            animation: fadeInUp 0.6s ease-out;
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Invoice Header */
        .invoice-logo {
            padding-bottom: 20px;
            border-bottom: 3px solid #ff9800;
            margin-bottom: 30px;
        }

        .invoice-logo img {
            max-height: 80px;
            object-fit: contain;
        }

        .invoice-logo p {
            font-size: 18px;
            font-weight: 700;
            color: #ff9800;
            margin: 0;
            text-align: right;
        }

        /* Section Headings */
        .invoice h3 {
            color: #333;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f5f5f5;
        }

        /* Info Lists */
        .invoice ul.list-unstyled {
            padding: 0;
            margin: 0;
        }

        .invoice ul.list-unstyled li {
            padding: 8px 0;
            color: #666;
            font-size: 14px;
        }

        .invoice ul.list-unstyled li strong {
            color: #333;
            font-weight: 600;
            min-width: 120px;
            display: inline-block;
        }

        .invoice ul.list-unstyled li i {
            color: #ff9800;
            margin: 0 5px;
        }

        /* Modern Table */
        .invoice .table {
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .invoice .table thead {
            background: #f5f5f5;
        }

        .invoice .table thead th {
            padding: 15px;
            font-weight: 700;
            color: #333;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
            border: none;
        }

        .invoice .table tbody td {
            padding: 20px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f5f5f5;
            color: #666;
        }

        .invoice .table tbody tr:last-child td {
            border-bottom: none;
        }

        .invoice .table tbody tr:hover {
            background: #fff9f0;
        }

        /* Invoice Block - Totals */
        .invoice-block {
            background: #f9f9f9;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
        }

        .invoice-block ul.amounts {
            padding: 0;
            margin: 0 0 20px 0;
        }

        .invoice-block ul.amounts li {
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 15px;
            color: #666;
        }

        .invoice-block ul.amounts li:last-child {
            border-bottom: 2px solid #ff9800;
            padding-top: 15px;
            margin-top: 10px;
            font-size: 18px;
            color: #333;
        }

        .invoice-block ul.amounts li strong {
            color: #333;
            font-weight: 600;
        }

        /* Print Button */
        .invoice-block .btn {
            background: #ff9800;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
        }

        .invoice-block .btn:hover {
            background: #f57c00;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);
        }

        .invoice-block .btn i {
            margin-left: 8px;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            background: #4caf50;
            color: white;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 2px 6px rgba(76, 175, 80, 0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .invoice {
                padding: 20px;
            }

            .invoice-logo p {
                text-align: center;
                margin-top: 15px;
            }

            .invoice .table {
                font-size: 12px;
            }

            .invoice-block {
                margin-top: 20px;
            }
        }

        /* Print Styles */
        @media print {
            .invoice {
                box-shadow: none;
                padding: 20px;
            }

            .invoice-block .btn {
                display: none;
            }

            .breadcrumb,
            .portlet-title,
            .page-sidebar,
            .page-header {
                display: none !important;
            }
        }
    </style>

@endsection
@section('form_title') {{ @$title }} @endsection
@section('page_body')

    {{--    {!! \DNS2D::getBarcodeHTML('AQVTYWxsYQIKMTIzNDU2Nzg5MQMUMjAyMS0wNy0xMlQxNDoyNTowOVoEBjEwMC4wMAUFMTUuMDA=', 'QRCODE') !!}--}}

    <!-- BEGIN PAGE CONTENT-->
    <div class="invoice">
        <div class="row invoice-logo">
            <div class="col-xs-6 invoice-logo-space">
                @if($mainSettings->logo)
                    <img src="https://gymmawy.com/resources/assets/new_front/img/logo/default_ar.png" class="img-responsive" alt=""/>
                @endif
            </div>
            <div class="col-xs-6">
                <p>
                    # {{$order['id']}} / {{\Carbon\Carbon::parse($order['created_at'] ?? $order['updated_at'])->format('d-m-Y')}}
                </p>
                @if(@$order['response']['success'] == 'true')
                    <div style="text-align: right; margin-top: 10px;">
                        <span class="status-badge">
                            <i class="fa fa-check-circle"></i> {{ trans('sw.successful')}}
                        </span>
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            @if(@$mainSettings->vat_details['seller_name'] || @$mainSettings->vat_details['vat_number'])
            <div class="col-xs-6 invoice-payment">
                <h3>{{@$title_details}}:</h3>
                <ul class="list-unstyled">
                    @if(@$mainSettings->vat_details['seller_name'])
                        <li>
                            <strong>{{ trans('sw.subscription_name')}}:</strong> {{@$mainSettings->vat_details['seller_name']}}
                        </li>
                    @endif


                </ul>
            </div>
            @endif
            <div class="col-xs-6">
                <h3><br/></h3>
                <ul class="list-unstyled">
                    <li>
                        <strong>{{ trans('sw.date')}}:</strong> <i
                                class="fa fa-calendar text-muted"></i> {{ \Carbon\Carbon::parse($order['created_at'] ?? $order['updated_at'])->format('Y-m-d') }} <i
                                class="fa fa-clock-o text-muted"></i> {{ \Carbon\Carbon::parse($order['created_at'] ?? $order['updated_at'])->format('h:i a') }}
                    </li>
{{--                    @if(@$mainSettings->vat_details['vat_percentage'])--}}
{{--                        <li>--}}
{{--                            <strong>{{trans('sw.vat_total')}} :</strong> {{@number_format($order['vat'])}}--}}
{{--                        </li>--}}
{{--                    @endif--}}
                    <li>
                        <strong>{{ trans('sw.total_price')}}
                            :</strong> {{number_format(@$order['response']['amount_cents'] / 100)}} {{@$order['response']['currency']}}
                    </li>
                </ul>
            </div>

        </div>
        <div class="row">
            <div class="col-xs-12">
                <h3><i class="fa fa-list"></i> {{ trans('sw.invoice_details') ?? 'Invoice Details'}}</h3>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th style="width: 80px;">
                            <i class="fa fa-hashtag"></i> #
                        </th>
                        <th class="hidden-480">
                            <i class="fa fa-info-circle"></i> {{$title_details}}
                        </th>
                        <th style="width: 150px;">
                            <i class="fa fa-money"></i> {{ trans('sw.total_price')}}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>{{$order['id']}}</strong>
                            </td>
                            <td class="hidden-480">
                                {{ trans('sw.sw_payment_subscription_msg', ['date_from' => @$order['date_from'], 'date_to' => @$order['date_to']])}}
                            </td>
                            <td>
                                <strong>{{number_format(@$order['response']['amount_cents'] / 100)}} {{@$order['response']['currency']}}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-3">
            </div>
            <div class="col-xs-9 invoice-block">
                <h3 style="border: none; padding: 0; margin: 0 0 20px 0;"><i class="fa fa-calculator"></i> {{ trans('sw.invoice_summary') ?? 'Summary'}}</h3>
                <ul class="list-unstyled amounts">
                    <li>
                        <strong><i class="fa fa-file-text-o"></i> {{ trans('sw.invoice_total')}}:</strong>
                        <span style="float: right;">{{number_format(((@$order['response']['amount_cents'] / 100) - @$order['vat']))}} {{@$order['response']['currency']}}</span>
                    </li>
                    @if(@$order['vat'])
                        <li>
                            <strong><i class="fa fa-percent"></i> {{ trans('sw.vat_total')}}:</strong>
                            <span style="float: right;">{{@number_format($order['vat'], 2)}} {{@$order['response']['currency']}}</span>
                        </li>
                        <li>
                            <strong><i class="fa fa-money"></i> {{ trans('sw.invoice_total_required')}}:</strong>
                            <span style="float: right; font-size: 20px; color: #ff9800;">{{number_format(@$order['response']['amount_cents'] / 100)}} {{@$order['response']['currency']}}</span>
                        </li>
                    @endif
                </ul>
                <br/>
                <a class="btn btn-lg hidden-print" onclick="javascript:window.print();">
                    <i class="fa fa-print"></i> {{ trans('sw.print')}}
                </a>
            </div>
        </div>

{{--        @if(@$mainSettings->terms)--}}
{{--            <div class="row">--}}
{{--                <div class="col-xs-12">--}}
{{--                    <div class="portlet grey-cascade box" style="border-top: 1px solid #b1bdbd;">--}}
{{--                        <div class="portlet-title" style="border-bottom: 1px solid #eee;">--}}
{{--                            <div class="caption">--}}
{{--                                <i class="fa fa-file-text-o"></i> {{ trans('sw.terms')}}--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="portlet-body">--}}
{{--                            <div class="table-responsive">--}}
{{--                                {!! $mainSettings->terms !!}--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                </div>--}}
{{--            </div>--}}
{{--        @endif--}}

{{--        <div class="row">--}}
{{--            <div class="col-xs-3">--}}
{{--                <b>{{ trans('sw.name')}}:</b>&nbsp;&nbsp;<span style="vertical-align: text-bottom;color: lightgray;">----------------</span>--}}
{{--                <br/><br/>--}}
{{--                <b>{{ trans('sw.signature')}}:</b>&nbsp;&nbsp;<span style="vertical-align: text-bottom;color: lightgray;">----------------</span>--}}
{{--            </div>--}}
{{--            <div class="col-xs-9 invoice-block">--}}
{{--                <a class="btn btn-lg blue hidden-print margin-bottom-5" onclick="javascript:window.print();">--}}
{{--                    {{ trans('sw.print')}} <i class="fa fa-print"></i>--}}
{{--                </a>--}}

{{--                <a class="btn btn-lg blue hidden-print margin-bottom-5" onclick="javascript:window.open('{{route('sw.showOrderPOS', @$order->id)}}', 'POS','height=600,width=700');">--}}
{{--                    <i class="fa fa-file-text-o"></i>--}}
{{--                </a>--}}
{{--            </div>--}}
{{--        </div>--}}

    </div>
    <!-- END PAGE CONTENT-->


@endsection
@section('sub_scripts')

@endsection


