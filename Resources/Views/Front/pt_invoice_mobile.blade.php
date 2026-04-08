<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title }}</title>
    @php
        $isRtl  = app()->getLocale() === 'ar';
        $logo   = $mainSettings ? $mainSettings->getRawOriginal('logo_' . app()->getLocale()) : null;
        $logoUrl = $logo ? asset('uploads/settings/' . $logo) : null;
    @endphp
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #fff; margin: 0; padding: 15px;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ? 'right' : 'left' }};
            color: #333;
        }
        .logo-wrap { text-align: center; margin-bottom: 18px; }
        .logo-wrap img { max-width: 140px; max-height: 70px; object-fit: contain; }
        .success-banner {
            background: #e6f4ea; border: 1px solid #a8d5b0;
            border-radius: 8px; padding: 14px;
            display: flex; align-items: center; gap: 10px; margin-bottom: 18px;
        }
        .success-banner .checkmark { font-size: 28px; line-height: 1; }
        .success-banner h4 { margin: 0 0 3px; font-size: 16px; color: #1a7a2e; }
        .success-banner p  { margin: 0; font-size: 13px; color: #2d6a35; }
        .invoice-card { border: 1px solid #eee; border-radius: 8px; overflow: hidden; margin-bottom: 15px; }
        .invoice-card-header {
            background: #f97d04; color: #fff;
            padding: 12px 15px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .invoice-card-header span   { font-size: 13px; opacity: 0.9; }
        .invoice-card-header strong { font-size: 15px; }
        .invoice-body { padding: 14px 15px; }
        .invoice-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px;
        }
        .invoice-row:last-child { border-bottom: none; }
        .invoice-row .label { color: #777; }
        .invoice-row .value { font-weight: 500; color: #333; }
        .invoice-row.total-row .label { font-weight: bold; color: #333; font-size: 15px; }
        .invoice-row.total-row .value { font-weight: bold; color: #f97d04; font-size: 15px; }
    </style>
</head>
<body>

    @if($logoUrl)
    <div class="logo-wrap">
        <img src="{{ $logoUrl }}" alt="Logo">
    </div>
    @endif

    <div class="success-banner">
        <span class="checkmark">✅</span>
        <div>
            <h4>{{ trans('front.payment_success_title') }}</h4>
            <p>{{ trans('front.payment_success_desc') }}</p>
        </div>
    </div>

    <div class="invoice-card">
        <div class="invoice-card-header">
            <span>{{ trans('sw.pt_subscription_mobile_title') }}</span>
            <strong>#{{ $ptMember->id }}</strong>
        </div>
        <div class="invoice-body">
            <div class="invoice-row">
                <span class="label">{{ trans('front.member_name') }}</span>
                <span class="value">{{ optional($ptMember->member)->name }}</span>
            </div>
            <div class="invoice-row">
                <span class="label">{{ trans('sw.pt_choose_class') }}</span>
                <span class="value">{{ optional($ptMember->class)->name }}</span>
            </div>
            @if($ptMember->classTrainer && $ptMember->classTrainer->trainer)
            <div class="invoice-row">
                <span class="label">{{ trans('sw.pt_choose_trainer') }}</span>
                <span class="value">{{ $ptMember->classTrainer->trainer->name }}</span>
            </div>
            @endif
            <div class="invoice-row">
                <span class="label">{{ trans('sw.pt_sessions') }}</span>
                <span class="value">{{ $ptMember->total_sessions }}</span>
            </div>
            <div class="invoice-row">
                <span class="label">{{ trans('front.start_date') }}</span>
                <span class="value">{{ $ptMember->start_date ? \Carbon\Carbon::parse($ptMember->start_date)->format('Y-m-d') : '-' }}</span>
            </div>
            <div class="invoice-row">
                <span class="label">{{ trans('front.expire_date') }}</span>
                <span class="value">{{ $ptMember->expire_date ? \Carbon\Carbon::parse($ptMember->expire_date)->format('Y-m-d') : '-' }}</span>
            </div>
            <div class="invoice-row total-row">
                <span class="label">{{ trans('global.total') }}</span>
                <span class="value">{{ number_format($ptMember->paid_amount, 2) }} {{ trans('front.pound_unit') }}</span>
            </div>
        </div>
    </div>

    <div style="padding-bottom:60px;"></div>

</body>
</html>
