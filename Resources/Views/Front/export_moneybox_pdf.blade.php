<!DOCTYPE html>
<html lang="{{ $lang ?? 'en' }}" dir="{{ $lang == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            direction: {{ $lang == 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ $lang == 'ar' ? 'right' : 'left' }};
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: {{ $lang == 'ar' ? 'right' : 'left' }};
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            font-size: 11px;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        @if($lang == 'ar')
        th, td {
            direction: rtl;
            text-align: right;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        @else
        th, td {
            direction: ltr;
            text-align: left;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        @endif

        .footer {
            text-align: {{ $lang == 'ar' ? 'left' : 'right' }};
            margin-top: 20px;
            font-weight: bold;
        }

        .no-data {
            text-align: center;
            font-size: 16px;
            margin: 40px 0;
        }

        .summary-section {
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .summary-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 6px 10px;
            background-color: #f5f5f5;
            border-bottom: 2px solid #ddd;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .summary-table td {
            padding: 6px 10px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .summary-table .label-cell {
            font-weight: bold;
            background-color: #f9f9f9;
            width: 60%;
        }

        .summary-table .value-cell {
            text-align: center;
            width: 40%;
        }

        .text-success { color: #0fa751; }
        .text-danger { color: #ec2d38; }
        .text-primary { color: #0162e8; }
        .text-info { color: #17a2b8; }

        .summary-row-group {
            width: 100%;
        }

        .summary-row-group td {
            vertical-align: top;
            padding: 0 5px;
        }
    </style>
</head>
<body>

@include('software::Front.partials._report_header')

<div class="title"><b>{{$title}}</b></div>

@if(count($records) > 0)
    <table>
        <thead>
            <tr>
                @foreach($keys as $key)
                    <th>{{ trans('sw.'.$key)}}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    @foreach($keys as $key)
                        <td>{{$record[$key] }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Financial Summary --}}
    <div class="summary-section">
        <div class="summary-title">{{ trans('sw.revenues')}} / {{ trans('sw.expenses')}} / {{ trans('sw.earnings')}}</div>
        <table class="summary-table">
            <tr>
                <td class="label-cell">{{ trans('sw.revenues')}}</td>
                <td class="value-cell text-success"><b>{{ number_format($revenues, 2) }}</b></td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('sw.expenses')}}</td>
                <td class="value-cell text-danger"><b>{{ number_format($expenses, 2) }}</b></td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('sw.earnings')}}</td>
                <td class="value-cell text-primary"><b>{{ number_format($earnings, 2) }}</b></td>
            </tr>
        </table>
    </div>

    {{-- Payment Types Summary --}}
    @if(isset($payment_types) && count($payment_types) > 0)
    <div class="summary-section">
        <div class="summary-title">{{ trans('sw.payment_types_summary')}}</div>
        <table class="summary-table">
            <tr>
                <th style="width:40%">{{ trans('sw.payment_type')}}</th>
                <th style="width:20%">{{ trans('sw.revenues2')}}</th>
                <th style="width:20%">{{ trans('sw.expenses2')}}</th>
                <th style="width:20%">{{ trans('sw.earnings2')}}</th>
            </tr>
            @foreach($payment_types as $pt)
            <tr>
                <td class="label-cell">{{ $pt->name }}</td>
                <td class="value-cell text-success">{{ number_format(@$payment_revenues[$pt->payment_id] ?? 0, 2) }}</td>
                <td class="value-cell text-danger">{{ number_format(@$payment_expenses[$pt->payment_id] ?? 0, 2) }}</td>
                <td class="value-cell text-primary">{{ number_format((@$payment_revenues[$pt->payment_id] ?? 0) - (@$payment_expenses[$pt->payment_id] ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    {{-- Cash Revenues Breakdown --}}
    <div class="summary-section">
        <div class="summary-title">{{ trans('sw.cash_revenues')}}</div>
        <table class="summary-table">
            <tr>
                <td class="label-cell">{{ trans('sw.subscription_earnings')}}</td>
                <td class="value-cell text-success">{{ number_format($total_subscriptions, 2) }}</td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('sw.pt_subscription_earnings')}}</td>
                <td class="value-cell text-success">{{ number_format($total_pt_subscriptions, 2) }}</td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('sw.activity_earnings')}}</td>
                <td class="value-cell text-success">{{ number_format($total_activities, 2) }}</td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('sw.store_earnings')}}</td>
                <td class="value-cell text-success">{{ number_format($total_stores, 2) }}</td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('sw.add_moneybox_revenues')}}</td>
                <td class="value-cell text-success">{{ number_format($total_add_to_money_box, 2) }}</td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('sw.withdraw_moneybox_revenues')}}</td>
                <td class="value-cell text-danger">{{ number_format($total_withdraw_from_money_box, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- Balance Operations --}}
    <div class="summary-section">
        <div class="summary-title">{{ trans('sw.balance_operations')}}</div>
        <table class="summary-table">
            <tr>
                <td class="label-cell">{{ trans('sw.total_wallet_topups')}}</td>
                <td class="value-cell text-info">{{ number_format($total_wallet_topups ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('sw.total_debt_payments')}}</td>
                <td class="value-cell text-primary">{{ number_format($total_debt_payments ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer"><b>{{$mainSettings->name ?? ''}}</b></div>
@else
    <div class="no-data">{{ trans('sw.no_record_found')}}</div>
@endif

</body>
</html>
