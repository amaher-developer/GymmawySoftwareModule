<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? trans('front.payment_error_title') }}</title>
    @php $isRtl = app()->getLocale() === 'ar'; @endphp
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #fff;
            margin: 0;
            padding: 30px 20px;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            text-align: center;
            color: #333;
        }
        .error-icon { font-size: 60px; margin-bottom: 16px; }
        h2 { color: #d9534f; font-size: 20px; margin-bottom: 10px; }
        p  { color: #666; font-size: 14px; line-height: 1.6; }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 28px;
            background: #f97d04;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="error-icon">❌</div>
    <h2>{{ trans('front.payment_error_title') }}</h2>
    <p>{{ trans('front.payment_error_body') }}</p>
    <a class="back-btn" href="javascript:history.back()">{{ trans('global.back') }}</a>
</body>
</html>
