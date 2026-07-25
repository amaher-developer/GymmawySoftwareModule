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
            border-radius: 10px; padding: 14px;
            display: flex; align-items: center; gap: 10px; margin-bottom: 20px;
        }
        .success-banner .checkmark { font-size: 32px; line-height: 1; }
        .success-banner h4 { margin: 0 0 3px; font-size: 17px; color: #1a7a2e; }
        .success-banner p  { margin: 0; font-size: 13px; color: #2d6a35; }

        .invoice-card { border: 1px solid #eee; border-radius: 12px; overflow: hidden; margin-bottom: 16px; }
        .invoice-card-header {
            background: #f97d04; color: #fff; padding: 13px 16px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .invoice-card-header span   { font-size: 13px; opacity: .85; }
        .invoice-card-header strong { font-size: 15px; }
        .invoice-body { padding: 14px 16px; }
        .invoice-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 9px 0; border-bottom: 1px solid #f2f2f2; font-size: 14px;
        }
        .invoice-row:last-child { border-bottom: none; }
        .invoice-row .label { color: #777; }
        .invoice-row .value { font-weight: 500; color: #222; }
        .invoice-row.total-row .label { font-weight: 700; color: #222; font-size: 15px; }
        .invoice-row.total-row .value { font-weight: 800; color: #f97d04; font-size: 15px; }
        .new-badge {
            display: inline-block; background: #fff3e0; color: #e65100;
            border-radius: 20px; font-size: 10px; font-weight: 700;
            padding: 2px 8px; margin-{{ $isRtl ? 'right' : 'left' }}: 6px; vertical-align: middle;
        }
    </style>
</head>
<body>

    @if($logoUrl)
    <div class="logo-wrap">
        <img src="{{ $logoUrl }}" alt="Logo">
    </div>
    @endif

    <div class="success-banner">
        <span class="checkmark">🎉</span>
        <div>
            <h4>{{ trans('sw.upgrade_success_title') }}</h4>
            <p>{{ trans('sw.upgrade_success_desc') }}</p>
        </div>
    </div>

    <div class="invoice-card">
        <div class="invoice-card-header">
            <span>{{ trans('sw.upgrade_subscription_title') }}</span>
            <strong>#{{ $memberSub->id }}</strong>
        </div>
        <div class="invoice-body">
            <div class="invoice-row">
                <span class="label">{{ trans('front.member_name') }}</span>
                <span class="value">{{ optional($memberSub->member)->name }}</span>
            </div>
            <div class="invoice-row">
                <span class="label">{{ trans('front.subscription') }}</span>
                <span class="value">
                    {{ optional($memberSub->subscription)->name }}
                    <span class="new-badge">{{ trans('sw.active') }}</span>
                </span>
            </div>
            <div class="invoice-row">
                <span class="label">{{ trans('front.start_date') }}</span>
                <span class="value">{{ \Carbon\Carbon::parse($memberSub->joining_date)->format('Y-m-d') }}</span>
            </div>
            <div class="invoice-row">
                <span class="label">{{ trans('sw.upgrade_expire_date') }}</span>
                <span class="value">{{ \Carbon\Carbon::parse($memberSub->expire_date)->format('Y-m-d') }}</span>
            </div>
            @if($memberSub->vat > 0)
            <div class="invoice-row">
                <span class="label">{{ trans('front.vat') }}</span>
                <span class="value">{{ number_format($memberSub->vat, 2) }} {{ trans('front.pound_unit') }}</span>
            </div>
            @endif
            <div class="invoice-row total-row">
                <span class="label">{{ trans('sw.upgrade_difference_price') }}</span>
                <span class="value">{{ number_format($memberSub->amount_paid, 2) }} {{ trans('front.pound_unit') }}</span>
            </div>
        </div>
    </div>

    <div style="padding-bottom:60px;"></div>

</body>
</html>
