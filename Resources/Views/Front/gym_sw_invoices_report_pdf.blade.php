<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
        }
        .page-title {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .page-title h1 { margin: 0; font-size: 20px; color: #333; }
        .date-range { color: #666; font-size: 10px; margin-top: 4px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-table td { padding: 8px 12px; border: 1px solid #ddd; }
        .summary-table .label { font-weight: bold; color: #555; width: 60%; }
        .summary-table .value { font-weight: bold; font-size: 13px; color: #333; }
        .section-title {
            background-color: #f0f0f0;
            padding: 7px 10px;
            font-weight: bold;
            border-left: 4px solid #667eea;
            margin-bottom: 8px;
            font-size: 12px;
        }
        .section { margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .badge-success  { color: #28a745; font-weight: bold; }
        .badge-info     { color: #17a2b8; font-weight: bold; }
        .badge-warning  { color: #ffc107; font-weight: bold; }
        .badge-secondary{ color: #6c757d; font-weight: bold; }
        .badge-danger   { color: #dc3545; font-weight: bold; }
        .total-row td   { background-color: #667eea; color: #fff; font-weight: bold; font-size: 14px; text-align: center; padding: 10px; }
        .footer { margin-top: 25px; text-align: center; font-size: 9px; color: #aaa; border-top: 1px solid #ddd; padding-top: 8px; }
    </style>
</head>
<body>

    @include('software::Front.partials._report_header')

    <div class="page-title">
        <h1>{{ $title }}</h1>
        @if($data['date_from'] || $data['date_to'])
        <p class="date-range">
            {{ trans('sw.from') }}: {{ $data['date_from'] ?? '—' }}
            &nbsp;&nbsp;{{ trans('sw.to') }}: {{ $data['date_to'] ?? '—' }}
        </p>
        @endif
    </div>

    {{-- Summary Totals --}}
    <div class="section">
        <table width="100%" cellspacing="6" style="border: none; margin-bottom: 20px;">
            <tr>
                <td width="25%" style="border: none; padding: 0; background-color: #667eea; color: #fff; text-align: center;">
                    <div style="padding: 14px 8px;">
                        <div style="font-size: 22px; font-weight: bold;">{{ $data['insights']['total_count'] }}</div>
                        <div style="font-size: 10px; color: #ddd; margin-top: 3px;">{{ trans('sw.total_invoices') }}</div>
                    </div>
                </td>
                <td width="25%" style="border: none; padding: 0; background-color: #11998e; color: #fff; text-align: center;">
                    <div style="padding: 14px 8px;">
                        <div style="font-size: 22px; font-weight: bold;">{{ number_format($data['insights']['total_amount'], 2) }}</div>
                        <div style="font-size: 10px; color: #ddd; margin-top: 3px;">{{ trans('sw.total') }}</div>
                    </div>
                </td>
                <td width="25%" style="border: none; padding: 0; background-color: #28a745; color: #fff; text-align: center;">
                    <div style="padding: 14px 8px;">
                        <div style="font-size: 22px; font-weight: bold;">{{ number_format($data['insights']['total_paid'], 2) }}</div>
                        <div style="font-size: 10px; color: #ddd; margin-top: 3px;">{{ trans('sw.amount_paid') }}</div>
                    </div>
                </td>
                <td width="25%" style="border: none; padding: 0; background-color: #dc3545; color: #fff; text-align: center;">
                    <div style="padding: 14px 8px;">
                        <div style="font-size: 22px; font-weight: bold;">{{ number_format($data['insights']['total_remaining'], 2) }}</div>
                        <div style="font-size: 10px; color: #ddd; margin-top: 3px;">{{ trans('sw.amount_remaining') }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- By Type --}}
    <div class="section">
        <div class="section-title">{{ trans('sw.invoices_by_type') }}</div>
        <table>
            <thead>
                <tr>
                    <th>{{ trans('sw.type') }}</th>
                    <th>{{ trans('admin.total_count') }}</th>
                    <th>{{ trans('sw.total') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge-success">{{ trans('sw.sales') }}</span></td>
                    <td>{{ $data['insights']['by_type']['sales']['count'] }}</td>
                    <td>{{ number_format($data['insights']['by_type']['sales']['amount'], 2) }}</td>
                </tr>
                <tr>
                    <td><span class="badge-info">{{ trans('sw.purchase') }}</span></td>
                    <td>{{ $data['insights']['by_type']['purchase']['count'] }}</td>
                    <td>{{ number_format($data['insights']['by_type']['purchase']['amount'], 2) }}</td>
                </tr>
                <tr>
                    <td><span class="badge-warning">{{ trans('sw.credit_note') }}</span></td>
                    <td>{{ $data['insights']['by_type']['credit_note']['count'] }}</td>
                    <td>{{ number_format($data['insights']['by_type']['credit_note']['amount'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- By Status --}}
    <div class="section">
        <div class="section-title">{{ trans('sw.invoices_by_status') }}</div>
        <table>
            <thead>
                <tr>
                    <th>{{ trans('sw.status') }}</th>
                    <th>{{ trans('admin.total_count') }}</th>
                    <th>{{ trans('sw.total') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge-secondary">{{ trans('sw.draft') }}</span></td>
                    <td>{{ $data['insights']['by_status']['draft']['count'] }}</td>
                    <td>{{ number_format($data['insights']['by_status']['draft']['amount'], 2) }}</td>
                </tr>
                <tr>
                    <td><span class="badge-warning">{{ trans('sw.partial') }}</span></td>
                    <td>{{ $data['insights']['by_status']['partial']['count'] }}</td>
                    <td>{{ number_format($data['insights']['by_status']['partial']['amount'], 2) }}</td>
                </tr>
                <tr>
                    <td><span class="badge-success">{{ trans('sw.paid') }}</span></td>
                    <td>{{ $data['insights']['by_status']['paid']['count'] }}</td>
                    <td>{{ number_format($data['insights']['by_status']['paid']['amount'], 2) }}</td>
                </tr>
                <tr>
                    <td><span class="badge-danger">{{ trans('sw.cancelled') }}</span></td>
                    <td>{{ $data['insights']['by_status']['cancelled']['count'] }}</td>
                    <td>{{ number_format($data['insights']['by_status']['cancelled']['amount'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Invoice List --}}
    @if($data['invoices']->count() > 0)
    <div class="section">
        <div class="section-title">{{ trans('sw.invoices') }}</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ trans('sw.invoice_number') }}</th>
                    <th>{{ trans('sw.type') }}</th>
                    <th>{{ trans('sw.status') }}</th>
                    <th>{{ trans('sw.total') }}</th>
                    <th>{{ trans('sw.amount_paid') }}</th>
                    <th>{{ trans('sw.amount_remaining') }}</th>
                    <th>{{ trans('sw.issued_at') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['invoices'] as $invoice)
                <tr>
                    <td>{{ $invoice->id }}</td>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>
                        @if($invoice->type === 'sales')
                            <span class="badge-success">{{ trans('sw.sales') }}</span>
                        @elseif($invoice->type === 'purchase')
                            <span class="badge-info">{{ trans('sw.purchase') }}</span>
                        @else
                            <span class="badge-warning">{{ trans('sw.credit_note') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($invoice->status === 'paid')
                            <span class="badge-success">{{ trans('sw.paid') }}</span>
                        @elseif($invoice->status === 'partial')
                            <span class="badge-warning">{{ trans('sw.partial') }}</span>
                        @elseif($invoice->status === 'cancelled')
                            <span class="badge-danger">{{ trans('sw.cancelled') }}</span>
                        @else
                            <span class="badge-secondary">{{ trans('sw.draft') }}</span>
                        @endif
                    </td>
                    <td>{{ number_format($invoice->total, 2) }}</td>
                    <td>{{ number_format($invoice->amount_paid, 2) }}</td>
                    <td>{{ number_format($invoice->amount_remaining, 2) }}</td>
                    <td>{{ $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        {{ trans('sw.generated_on') }}: {{ now()->format('Y-m-d H:i:s') }}
    </div>

</body>
</html>
