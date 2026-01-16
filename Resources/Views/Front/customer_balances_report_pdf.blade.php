<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $lang == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: {{ $lang == 'ar' ? 'right' : 'left' }};
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .amount-positive {
            color: #0fa751;
            font-weight: bold;
        }
        .amount-negative {
            color: #ec2d38;
            font-weight: bold;
        }
        .amount-warning {
            color: #f57c00;
            font-weight: bold;
        }
        .status-credit {
            background-color: #e8f5e9;
            color: #0fa751;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }
        .status-debt {
            background-color: #ffebee;
            color: #ec2d38;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }
        .status-unpaid {
            background-color: #fff3e0;
            color: #f57c00;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
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
    </div>

    <table style="border: none; margin-bottom: 15px;">
        <tr>
            <td style="border: none; width: 25%; padding: 8px; text-align: center; background-color: #e8f5e9;">
                <div style="color: #0fa751;">
                    <div style="font-size: 16px; font-weight: bold;">{{ number_format($data['totalStoreCredit'], 2) }}</div>
                    <div style="font-size: 9px;">{{ trans('sw.total_store_credit') }}</div>
                </div>
            </td>
            <td style="border: none; width: 25%; padding: 8px; text-align: center; background-color: #ffebee;">
                <div style="color: #ec2d38;">
                    <div style="font-size: 16px; font-weight: bold;">{{ number_format($data['totalStoreDebt'], 2) }}</div>
                    <div style="font-size: 9px;">{{ trans('sw.total_store_debt') }}</div>
                </div>
            </td>
            <td style="border: none; width: 25%; padding: 8px; text-align: center; background-color: #fff3e0;">
                <div style="color: #f57c00;">
                    <div style="font-size: 16px; font-weight: bold;">{{ number_format($data['totalRemainingAmount'], 2) }}</div>
                    <div style="font-size: 9px;">{{ trans('sw.total_remaining_amount') }}</div>
                </div>
            </td>
            <td style="border: none; width: 25%; padding: 8px; text-align: center; background-color: #f5f5f5;">
                <div style="color: #333;">
                    <div style="font-size: 16px; font-weight: bold;">{{ count($data['members']) }}</div>
                    <div style="font-size: 9px;">{{ trans('sw.total_customers_with_balance') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table style="border: none; margin-bottom: 15px;">
        <tr>
            <td style="border: none; width: 33%; padding: 6px; text-align: center; background-color: #e3f2fd;">
                <div style="color: #1976d2;">
                    <div style="font-size: 14px; font-weight: bold;">{{ number_format($data['totalSubscriptionRemaining'] ?? 0, 2) }}</div>
                    <div style="font-size: 8px;">{{ trans('sw.subscription_remaining') }}</div>
                </div>
            </td>
            <td style="border: none; width: 33%; padding: 6px; text-align: center; background-color: #e1f5fe;">
                <div style="color: #0288d1;">
                    <div style="font-size: 14px; font-weight: bold;">{{ number_format($data['totalPTRemaining'] ?? 0, 2) }}</div>
                    <div style="font-size: 8px;">{{ trans('sw.pt_remaining') }}</div>
                </div>
            </td>
            <td style="border: none; width: 33%; padding: 6px; text-align: center; background-color: #eceff1;">
                <div style="color: #546e7a;">
                    <div style="font-size: 14px; font-weight: bold;">{{ number_format($data['totalTrainingRemaining'] ?? 0, 2) }}</div>
                    <div style="font-size: 8px;">{{ trans('sw.training_remaining') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="font-size: 9px;">{{ trans('sw.code') }}</th>
                <th style="font-size: 9px;">{{ trans('sw.customer_name') }}</th>
                <th style="font-size: 9px;">{{ trans('sw.phone') }}</th>
                <th style="font-size: 9px;">{{ trans('sw.store_balance') }}</th>
                <th style="font-size: 9px;">{{ trans('sw.subscription_remaining_short') }}</th>
                <th style="font-size: 9px;">{{ trans('sw.pt_remaining_short') }}</th>
                <th style="font-size: 9px;">{{ trans('sw.training_remaining_short') }}</th>
                <th style="font-size: 9px;">{{ trans('sw.total_remaining_short') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['members'] as $member)
                <tr>
                    <td style="font-size: 9px;">{{ $member->code }}</td>
                    <td style="font-size: 9px;">{{ $member->name }}</td>
                    <td style="font-size: 9px;">{{ $member->phone }}</td>
                    <td style="font-size: 9px;">
                        @if($member->store_balance != 0)
                            <span class="{{ $member->store_balance > 0 ? 'amount-positive' : 'amount-negative' }}">
                                {{ number_format($member->store_balance, 2) }}
                            </span>
                            @if($member->store_balance > 0)
                                <span class="status-credit">{{ trans('sw.credit') }}</span>
                            @else
                                <span class="status-debt">{{ trans('sw.debt') }}</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td style="font-size: 9px;">
                        @if($member->subscription_remaining > 0)
                            <span style="color: #1976d2; font-weight: bold;">{{ number_format($member->subscription_remaining, 2) }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td style="font-size: 9px;">
                        @if($member->pt_remaining > 0)
                            <span style="color: #0288d1; font-weight: bold;">{{ number_format($member->pt_remaining, 2) }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td style="font-size: 9px;">
                        @if($member->training_remaining > 0)
                            <span style="color: #546e7a; font-weight: bold;">{{ number_format($member->training_remaining, 2) }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td style="font-size: 9px;">
                        @if($member->remaining_amount > 0)
                            <span class="amount-warning">{{ number_format($member->remaining_amount, 2) }}</span>
                            <span class="status-unpaid">{{ trans('sw.unpaid') }}</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ trans('sw.generated_on') }}: {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>
</html>
