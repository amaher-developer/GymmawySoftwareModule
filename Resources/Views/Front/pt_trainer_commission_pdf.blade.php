<!DOCTYPE html>
<html dir="{{ $lang == 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #222; margin: 0; padding: 0; direction: {{ $lang == 'ar' ? 'rtl' : 'ltr' }}; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #1a3a5c; color: #fff; padding: 7px 10px; font-size: 10px; text-align: {{ $lang == 'ar' ? 'right' : 'left' }}; white-space: nowrap; }
        td { padding: 6px 10px; border-bottom: 1px solid #e4e6ef; font-size: 9px; }
        tr:nth-child(even) td { background-color: #f8f9fa; }
        .title-row { background: #f0f4f8; padding: 8px 10px; margin-bottom: 10px; border-right: 4px solid #1a3a5c; }
        .title-row h2 { margin: 0; font-size: 13px; color: #1a3a5c; }
        .totals-row td { background-color: #e8f0fe; font-weight: bold; font-size: 10px; border-top: 2px solid #1a3a5c; }
        .text-end { text-align: {{ $lang == 'ar' ? 'left' : 'right' }}; }
    </style>
</head>
<body>

@php $mainSettings = $settings; @endphp
@include('software::Front.partials._report_header')

<div class="title-row">
    <h2>{{ $title }}</h2>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>{{ trans('sw.session_date') }}</th>
            <th>{{ trans('sw.subscriber') }}</th>
            <th>{{ trans('sw.member_code') }}</th>
            <th>{{ trans('sw.pt_class') }}</th>
            <th class="text-end">{{ trans('sw.commission_amount') }}</th>
            <th class="text-end">{{ trans('sw.commission_rate') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($commissions as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['session_date'] }}</td>
                <td>{{ $row['member_name'] }}</td>
                <td>{{ $row['member_code'] }}</td>
                <td>{{ $row['class_name'] }}</td>
                <td class="text-end">{{ $row['commission_amount'] }}</td>
                <td class="text-end">{{ $row['commission_rate'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#888;">{{ trans('sw.no_record_found') }}</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="totals-row">
            <td colspan="5" style="text-align: {{ $lang == 'ar' ? 'left' : 'right' }};">
                {{ trans('sw.sessions_label', ['count' => $totals['count']]) }}
            </td>
            <td class="text-end">{{ number_format($totals['amount'], 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

</body>
</html>
