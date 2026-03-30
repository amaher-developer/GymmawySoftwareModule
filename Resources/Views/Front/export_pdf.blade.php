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
        
        .header {
            text-align: {{ $lang == 'ar' ? 'right' : 'left' }};
            margin-bottom: 20px;
        }
        
        .date {
            text-align: {{ $lang == 'ar' ? 'left' : 'right' }};
            font-size: 10px;
            margin-bottom: 10px;
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
    
    <div class="footer"><b>{{$mainSettings->name ?? ''}}</b></div>
@else
    <div class="no-data">{{ trans('sw.no_record_found')}}</div>
@endif

@if(!empty($stats))
    <div style="margin-top: 24px;">
        <div style="font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #333; padding-bottom: 4px;">
            {{ trans('sw.online_transaction_report') }} - {{ trans('sw.total_amount') }}
        </div>
        <table style="width: 50%; margin-bottom: 16px;">
            <tr>
                <td style="background:#f5f5f5; font-weight:bold;">{{ trans('admin.total_count') }}</td>
                <td>{{ $stats['total_count'] }}</td>
                <td style="background:#f5f5f5; font-weight:bold;">{{ trans('sw.total_amount') }}</td>
                <td>{{ number_format($stats['total_amount'], 2) }}</td>
            </tr>
        </table>

        <div style="font-weight: bold; margin-bottom: 6px;">{{ trans('sw.status') }}</div>
        <table style="width: 60%; margin-bottom: 16px;">
            <thead>
                <tr>
                    <th>{{ trans('sw.status') }}</th>
                    <th>{{ trans('admin.total_count') }}</th>
                    <th>{{ trans('sw.total_amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $statusLabels = [1 => trans('sw.successful'), 0 => trans('sw.pending'), 2 => trans('sw.declined'), 3 => trans('sw.cancelled')]; @endphp
                @foreach($stats['by_status'] as $code => $data)
                    <tr>
                        <td>{{ $statusLabels[$code] ?? $code }}</td>
                        <td>{{ $data['count'] }}</td>
                        <td>{{ number_format($data['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="font-weight: bold; margin-bottom: 6px;">{{ trans('sw.payment_gateway') }}</div>
        <table style="width: 60%;">
            <thead>
                <tr>
                    <th>{{ trans('sw.payment_gateway') }}</th>
                    <th>{{ trans('admin.total_count') }}</th>
                    <th>{{ trans('sw.total_amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['by_gateway'] as $data)
                    @if($data['count'] > 0)
                        <tr>
                            <td>{{ $data['label'] }}</td>
                            <td>{{ $data['count'] }}</td>
                            <td>{{ number_format($data['amount'], 2) }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@endif

</body>
</html>


