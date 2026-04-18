<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title }}</title>
    @php $isRtl = app()->getLocale() === 'ar'; @endphp
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #fff;
            margin: 0;
            padding: 15px;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ? 'right' : 'left' }};
            color: #333;
        }
        .logo-wrap { text-align: center; margin-bottom: 18px; }
        .logo-wrap img { max-width: 140px; max-height: 70px; object-fit: contain; }
        .invoice-number { text-align: center; font-size: 13px; color: #999; margin-bottom: 18px; }
        .success-banner {
            background: #e6f4ea;
            border: 1px solid #a8d5b0;
            border-radius: 8px;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }
        .success-banner .checkmark { font-size: 28px; line-height: 1; }
        .success-banner h4 { margin: 0 0 3px; font-size: 16px; color: #1a7a2e; }
        .success-banner p  { margin: 0; font-size: 13px; color: #2d6a35; }
        .invoice-card { border: 1px solid #eee; border-radius: 8px; overflow: hidden; margin-bottom: 15px; }
        .invoice-card-header {
            background: #f97d04;
            color: #fff;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .invoice-card-header span   { font-size: 13px; opacity: 0.9; }
        .invoice-card-header strong { font-size: 15px; }
        .invoice-body { padding: 14px 15px; }
        .invoice-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        .invoice-row:last-child { border-bottom: none; }
        .invoice-row .label { color: #777; }
        .invoice-row .value { font-weight: 500; color: #333; }
        .invoice-row.total-row .label { font-weight: bold; color: #333; font-size: 15px; }
        .invoice-row.total-row .value { font-weight: bold; color: #f97d04; font-size: 15px; }
        .divider { border: none; border-top: 1px dashed #ddd; margin: 12px 0; }
        .qr-wrap { text-align: center; padding: 10px 0; }
        .qr-wrap img { width: 100px; }
    </style>
</head>
<body>

    {{-- Gym logo --}}
    <div class="logo-wrap">
        @php
            $logoFile = $mainSettings ? $mainSettings->getRawOriginal('logo_ar') : null;
            $logoSrc  = $logoFile
                ? asset('uploads/settings/' . $logoFile)
                : asset('resources/assets/new_front/images/logo.png');
        @endphp
        <img src="{{ $logoSrc }}" alt="Logo"
             onerror="this.src='{{ asset('resources/assets/new_front/images/logo.png') }}'">
    </div>

    <div class="invoice-number">
        {{ trans('front.invoice') }} #{{ $invoice->id }}
        &nbsp;|&nbsp;
        {{ \Carbon\Carbon::parse($invoice->created_at ?? $invoice->updated_at)->format('Y-m-d') }}
    </div>

    <div class="success-banner">
        <div class="checkmark">✅</div>
        <div>
            <h4>{{ trans('front.payment_success_title') }}</h4>
            <p>{{ trans('front.payment_success_body') }}</p>
        </div>
    </div>

    <div class="invoice-card">
        <div class="invoice-card-header">
            <strong>{{ optional($invoice->subscription)->name }}</strong>
            <span>{{ \Carbon\Carbon::parse($invoice->created_at ?? $invoice->updated_at)->format('h:i a') }}</span>
        </div>
        <div class="invoice-body">

            <div class="invoice-row">
                <span class="label">{{ trans('front.buyer_name') }}</span>
                <span class="value">{{ optional($invoice->member)->name }}</span>
            </div>

            @if($invoice->joining_date)
            <div class="invoice-row">
                <span class="label">{{ trans('front.register_info_joining_date') }}</span>
                <span class="value">{{ \Carbon\Carbon::parse($invoice->joining_date)->toDateString() }}</span>
            </div>
            @endif

            @if($invoice->expire_date)
            <div class="invoice-row">
                <span class="label">{{ trans('front.expire_date') }}</span>
                <span class="value">{{ \Carbon\Carbon::parse($invoice->expire_date)->toDateString() }}</span>
            </div>
            @endif

            @if($invoice->discount_value)
            <div class="invoice-row">
                <span class="label">{{ trans('front.discount') }}</span>
                <span class="value" style="color: green;">
                    -{{ $invoice->discount_value }} {{ trans('front.app_currency') }}
                </span>
            </div>
            @endif

            <hr class="divider">

            @if(($invoice->vat ?? 0) > 0)
            <div class="invoice-row">
                <span class="label">
                    {{ trans('front.total_price') }} ({{ trans('front.excluding_vat') }})
                </span>
                <span class="value">
                    {{ number_format(($invoice->amount_paid ?? 0) + ($invoice->amount_remaining ?? 0) - ($invoice->vat ?? 0), 2) }}
                    {{ trans('front.app_currency') }}
                </span>
            </div>
            <div class="invoice-row">
                <span class="label">{{ trans('front.vat') }}@if($invoice->vat_percentage > 0) ({{ $invoice->vat_percentage }}%)@endif</span>
                <span class="value">{{ number_format($invoice->vat ?? 0, 2) }} {{ trans('front.app_currency') }}</span>
            </div>
            @endif

            <div class="invoice-row total-row">
                <span class="label">{{ trans('global.total') }}</span>
                <span class="value">
                    {{ number_format(($invoice->amount_paid ?? 0) + ($invoice->amount_remaining ?? 0), 2) }}
                    {{ trans('front.app_currency') }}
                </span>
            </div>

        </div>
    </div>

    @if($qr_img_invoice)
    <div class="qr-wrap">
        <img src="{{ asset($qr_img_invoice) }}" alt="QR Code">
    </div>
    @endif

    <div style="padding-bottom: 60px;"></div>

</body>
</html>
