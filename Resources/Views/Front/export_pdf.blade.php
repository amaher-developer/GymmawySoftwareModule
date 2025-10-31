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

<div class="header">
    <div class="date"><b>{{date('d/m/Y')}}</b></div>
    <div class="title"><b>{{$title}}</b></div>
</div>

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

</body>
</html>
