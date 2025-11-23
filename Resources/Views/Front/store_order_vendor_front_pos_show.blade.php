<!DOCTYPE html>
<html  @if($lang == 'ar') lang="ar" dir="rtl" @else lang="en" @endif>

<head>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('resources/assets/new_front/img/logo/favicon.ico')}}">

    <meta charset="utf-8"/>
    <title>{{$mainSettings->name}}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'PT Sans', sans-serif;
        }

        @page {
            size: 2.8in 11in;
            margin-top: 0cm;
            margin-left: 0cm;
            margin-right: 0cm;
        }

        table {
            width: 100%;
        }

        tr {
            width: 100%;

        }

        h1 {
            text-align: center;
            vertical-align: middle;
        }

        #logo {
            width: 60%;
            text-align: center;
            -webkit-align-content: center;
            align-content: center;
            padding: 5px;
            margin: 2px;
            display: block;
            margin: 0 auto;
        }

        header {
            width: 100%;
            text-align: center;
            -webkit-align-content: center;
            align-content: center;
            vertical-align: middle;
        }

        .items thead {
            text-align: center;
        }

        .center-align {
            text-align: center;
        }

        .bill-details td {
            font-size: 12px;
        }

        .receipt {
            font-size: medium;
        }

        .items .heading {
            font-size: 12.5px;
            text-transform: uppercase;
            border-top:1px solid black;
            margin-bottom: 4px;
            border-bottom: 1px solid black;
            vertical-align: middle;
        }

        .items thead tr th:first-child,
        .items tbody tr td:first-child {
            width: 47%;
            min-width: 47%;
            max-width: 47%;
            word-break: break-all;
            text-align: center;
        }

        .items td {
            font-size: 12px;
            text-align: center;
            vertical-align: bottom;
        }

        .price::before {
            content: "\20B9";
            font-family: Arial;
            text-align: center;
        }

        .sum-up {
            text-align: right !important;
        }
        .total {
            font-size: 13px;
            border-top:1px dashed black !important;
            border-bottom:1px dashed black !important;
        }
        .total.text, .total.price {
            text-align: center;
        }
        .total.price::before {
            content: "\20B9";
        }
        .line {
            border-top:1px solid black !important;
        }
        .heading.rate {
            width: 20%;
        }
        .heading.amount {
            width: 25%;
        }
        .heading.qty {
            width: 5%
        }
        p {
            padding: 1px;
            margin: 0;
        }
        section, footer {
            font-size: 12px;
        }
    </style>
</head>

<body onload="window.print()">
<header>
    <div id="logo" class="media" data-src="{{$mainSettings->logo}}" src="{{$mainSettings->logo}}"><img src="{{$mainSettings->logo}}" style="width: 120px;height: 120px;object-fit: contain;"/></div>
</header>
<p>{{ trans('sw.order_number')}} : {{$order['id']}}</p>
<table class="bill-details">
    <tbody>
    <tr>
        <td>{{ trans('sw.date')}} : <span>{{ \Carbon\Carbon::parse($order['created_at'] ?? $order['updated_at'])->format('Y-m-d') }}</span></td>
        <td>{{ trans('sw.time')}} : <span>{{ \Carbon\Carbon::parse($order['created_at'] ?? $order['updated_at'])->format('h:i a') }}</span></td>
    </tr>
    <tr>
        @if(@$order['vendor_name'])<td>{{ trans('sw.vendor_name')}} #: <span>{{@$order['vendor_name']}}</span></td>@endif
        @if(@$order['vendor_phone'])<td>{{ trans('sw.vendor_phone')}} # : <span>{{@$order['vendor_phone']}}</span></td>@endif
    </tr>
    <tr>
        <th class="center-align" colspan="2"><span class="receipt">{{ trans('sw.invoice')}}</span></th>
    </tr>
    </tbody>
</table>

<table class="items">
    <thead>
    <tr>

        <th class="heading name">{{ trans('sw.item')}}</th>
        <th class="heading qty">{{ trans('sw.qty')}}</th>
        <th class="heading amount">{{ trans('sw.price_per_unit')}}</th>
        <th class="heading amount">{{ trans('sw.total_price')}}</th>
    </tr>
    </thead>

    <tbody>
    <tr>
        <td>{{@$order['product']['name']}}</td>
        <td>{{@$order['quantity']}}</td>
        <td>{{number_format(($order['amount'] / $order['quantity']), 2)}} {{@trans('sw.app_currency')}}</td>
        <td class="">{{number_format(($order['amount']), 2)}} {{@trans('sw.app_currency')}}</td>
    </tr>

    <tr>
        <th colspan="3" class="total text">{{ trans('sw.total_for_price')}} </th>
        <th class="total ">{{number_format($order['amount'], 2)}} {{@trans('sw.app_currency')}}</th>
    </tr>
    </tbody>
</table>
<section>
    @if(@$mainSettings->vat_details['saudi'] && @$qr_img_invoice)
        <div class="col-lg-12 " style="text-align: center">
            <img   width="50" src="{{asset($qr_img_invoice)}}"/>
        </div>
    @endif
    @if(@$order->pay_type)
    <p style="text-align:center">
        {{ trans('sw.paid_type')}} : <span>{{@$order->pay_type->name}}</span>
    </p>
    @endif
    <p style="text-align:center">
        {{ trans('sw.pos_thank_msg')}}
    </p>
</section>
<footer style="text-align:center">
    <p>{{ trans('sw.pos_terms_msg')}}</p>
    {{--    <p>www.gymmawy.com</p>--}}
</footer>
</body>

</html>


