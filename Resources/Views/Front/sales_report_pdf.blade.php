<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $lang == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            direction: {{ $lang == 'ar' ? 'rtl' : 'ltr' }};
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .date-range {
            color: #666;
            font-size: 11px;
            margin-top: 5px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #f5f5f5;
            padding: 8px;
            font-weight: bold;
            border-left: 4px solid #667eea;
            margin-bottom: 10px;
        }
        .total-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .total-box h2 {
            margin: 0;
            font-size: 28px;
        }
        .total-box p {
            margin: 5px 0 0 0;
            opacity: 0.8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: {{ $lang == 'ar' ? 'right' : 'left' }};
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .amount {
            font-weight: bold;
            color: #28a745;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p class="date-range">{{ trans('sw.from') }}: {{ $data['from'] }} - {{ trans('sw.to') }}: {{ $data['to'] }}</p>
    </div>

    <div class="total-box">
        <h2>{{ number_format($data['totalSales'], 2) }}</h2>
        <p>{{ trans('sw.total_sales') }}</p>
    </div>

    <div class="section">
        <div class="section-title">{{ trans('sw.sales_by_payment_method') }}</div>
        <table>
            <thead>
                <tr>
                    <th>{{ trans('sw.payment_method') }}</th>
                    <th>{{ trans('sw.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['salesByPaymentType'] as $paymentId => $paymentData)
                    <tr>
                        <td>{{ $paymentData['name'] }}</td>
                        <td class="amount">{{ number_format($paymentData['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td>{{ trans('sw.store_balance_sales') }}</td>
                    <td class="amount">{{ number_format($data['storeBalanceSales'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">{{ trans('sw.sales_by_category') }}</div>
        <table>
            <thead>
                <tr>
                    <th>{{ trans('sw.category') }}</th>
                    <th>{{ trans('sw.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ trans('sw.subscription_sales') }}</td>
                    <td class="amount">{{ number_format($data['subscriptionSales'], 2) }}</td>
                </tr>
                <tr>
                    <td>{{ trans('sw.pt_sales') }}</td>
                    <td class="amount">{{ number_format($data['ptSales'], 2) }}</td>
                </tr>
                <tr>
                    <td>{{ trans('sw.activity_sales') }}</td>
                    <td class="amount">{{ number_format($data['activitySales'], 2) }}</td>
                </tr>
                <tr>
                    <td>{{ trans('sw.store_sales') }}</td>
                    <td class="amount">{{ number_format($data['storeSales'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        {{ trans('sw.generated_on') }}: {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>
</html>
