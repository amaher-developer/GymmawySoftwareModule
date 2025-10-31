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
        <link href="{{asset('resources/assets/admin/pages/css/invoice-rtl.css')}}" rel="stylesheet" type="text/css"/>
    @else
        <link href="{{asset('resources/assets/admin/pages/css/invoice.css')}}" rel="stylesheet" type="text/css"/>
    @endif

@endsection
@section('form_title') {{ @$title }} @endsection
@section('page_body')

    {{--    {!! \DNS2D::getBarcodeHTML('AQVTYWxsYQIKMTIzNDU2Nzg5MQMUMjAyMS0wNy0xMlQxNDoyNTowOVoEBjEwMC4wMAUFMTUuMDA=', 'QRCODE') !!}--}}

    <!-- BEGIN PAGE CONTENT-->
    <div class="invoice">
        <div class="row invoice-logo">

            <div class="col-xs-6 invoice-logo-space">
                @if($mainSettings->logo)
                    <img src="https://gymmawy.com/resources/assets/front/img/logo/default_ar.png" class="img-responsive" alt="" style="height: 120px;object-fit: contain;"/>
                @endif
            </div>
            <div class="col-xs-6">
                <p>
                    # {{$order['id']}} / {{\Carbon\Carbon::parse($order['created_at'] ?? $order['updated_at'])->format('d-m-Y')}}
                </p>
            </div>
        </div>
        <hr/>
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
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>
                            #
                        </th>
                        <th class="hidden-480">
                            {{$title_details}}
                        </th>
                        <th>
                            {{ trans('sw.total_price')}}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                {{$order['id']}}
                            </td>
                            <td class="hidden-480">
                                {{ trans('sw.sw_payment_subscription_msg', ['date_from' => @$order['date_from'], 'date_to' => @$order['date_to']])}}
                            </td>
                            <td>
                                {{number_format(@$order['response']['amount_cents'] / 100)}} {{@$order['response']['currency']}}
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
                <ul class="list-unstyled amounts">
                    <li>
                        <strong>{{ trans('sw.invoice_total')}}:</strong> {{number_format(((@$order['response']['amount_cents'] / 100) - @$order['vat']))}} {{@$order['response']['currency']}}
                    </li>
                    @if(@$order['vat'])
                        <li>
                            <strong>{{ trans('sw.vat_total')}} :</strong> {{@number_format($order['vat'], 2)}} {{@$order['response']['currency']}}
                        </li>
                        <li>
                            <strong>{{ trans('sw.invoice_total_required')}}:</strong> {{number_format(@$order['response']['amount_cents'] / 100)}} {{@$order['response']['currency']}}
                        </li>
                    @endif
                </ul>
                <br/>
                <a class="btn btn-lg blue hidden-print margin-bottom-5" onclick="javascript:window.print();">
                    {{ trans('sw.print')}} <i class="fa fa-print"></i>
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
