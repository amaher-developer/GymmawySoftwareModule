<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Amiri&display=swap" rel="stylesheet">

    <style>
        body{
            font-family: DejaVu Sans, sans-serif;
            direction: rtl;
            text-align: right;
        }
        @if($lang == 'ar')
        .table > tbody > tr > td {
            direction: rtl;
            text-align: right;
        }
        .table > thead > tr > th {
            direction: rtl;
            text-align: right;
        }
        @endif
    </style>
</head>
<body>

<div class="container">
    <div class="row">
    <div class="@if($lang == 'ar') text-left @else text-right @endif"><p dir="ltr"><b>{{date('d/m/Y')}}</b></p></div>
    <div class="@if($lang == 'ar') text-right @else text-left @endif"><p style="font-size: 18px;"><b>{{$title}}</b></p></div>
    </div>
    @if(count($records) > 0)
        <table class="table card-table table-striped table-vcenter text-nowrap mb-0  " >
            <thead>
            <tr style="float: right">
                @foreach($keys as $key)
                    <th>{{trans('sw.'.$key)}}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($records as $record)
                <tr>
                    @foreach($keys as $key)
                        <td> {{ $record->$key }}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="@if($lang == 'ar') text-left @else text-right @endif"><b>{{$mainSettings->name}}</b></div>

    @else
        <h4 class="text-center">{{trans('sw.no_record_found')}}</h4>
    @endif
</div>
</body>
</html>

