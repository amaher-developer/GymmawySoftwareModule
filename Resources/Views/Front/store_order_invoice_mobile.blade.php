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
        $vatPercentage = (float) (@$mainSettings->vat_details['vat_percentage'] ?? 0);
        $rc = is_array($invoice->response_code) ? $invoice->response_code : json_decode($invoice->response_code, true);
        $storeItems = $rc['store_product_items'] ?? [];
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
            display: flex; justify-content: space-between; align-items: flex-start;
            padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px;
        }
        .invoice-row:last-child { border-bottom: none; }
        .invoice-row .label { color: #777; }
        .invoice-row .value { font-weight: 500; color: #333; text-align: end; }
        .invoice-row.total-row .label { font-weight: bold; color: #333; font-size: 15px; }
        .invoice-row.total-row .value { font-weight: bold; color: #f97d04; font-size: 15px; }
        .product-list { padding: 0; margin: 0; list-style: none; width: 100%; }
        .product-list li {
            display: flex; justify-content: space-between; align-items: center;
            padding: 5px 0; border-bottom: 1px dotted #eee; font-size: 13px;
        }
        .product-list li:last-child { border-bottom: none; }
        .product-list .prod-qty { color: #888; font-size: 12px; }
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
        </div>
    </div>

    <div class="invoice-card">
        <div class="invoice-card-header">
            <span>{{ trans('sw.store') ?? trans('front.store') ?? 'Store Order' }}</span>
            <strong>#{{ $invoice->id }}</strong>
        </div>
        <div class="invoice-body">
            <div class="invoice-row">
                <span class="label">{{ trans('front.member_name') }}</span>
                <span class="value">{{ $invoice->name }}</span>
            </div>
            @if($invoice->phone)
            <div class="invoice-row">
                <span class="label">{{ trans('front.phone') }}</span>
                <span class="value">{{ $invoice->phone }}</span>
            </div>
            @endif

            @if(count($storeItems) > 0)
            <div class="invoice-row" style="flex-direction: column; align-items: flex-start; gap: 6px;">
                <span class="label">{{ trans('sw.products') ?? trans('front.products') ?? 'Products' }}</span>
                <ul class="product-list">
                    @foreach($storeItems as $item)
                        @php
                            $pid  = $item['id'] ?? null;
                            $qty  = (int)($item['qty'] ?? 1);
                            $prod = $pid ? ($products[$pid] ?? null) : null;
                            $prodName  = $prod ? ($isRtl ? ($prod->name_ar ?? $prod->name_en ?? $prod->name) : ($prod->name_en ?? $prod->name_ar ?? $prod->name)) : '#'.$pid;
                            $prodPrice = $prod ? (float)($prod->price ?? 0) : 0;
                            $prodVat   = $vatPercentage > 0 ? round($prodPrice * $vatPercentage / 100, 2) : 0;
                            $lineTotal = round(($prodPrice + $prodVat) * $qty, 2);
                        @endphp
                        <li>
                            <span>{{ $prodName }} <span class="prod-qty">× {{ $qty }}</span></span>
                            @if($lineTotal > 0)
                                <span>{{ number_format($lineTotal, 2) }} {{ trans('front.pound_unit') }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @php
                $vatAmount = (float) ($invoice->vat ?? 0);
            @endphp

            @if($vatAmount > 0)
            <div class="invoice-row">
                <span class="label">{{ trans('front.vat') }}@if(($invoice->vat_percentage ?? 0) > 0) ({{ $invoice->vat_percentage }}%)@endif</span>
                <span class="value">{{ number_format($vatAmount, 2) }} {{ trans('front.pound_unit') }}</span>
            </div>
            @endif

            <div class="invoice-row total-row">
                <span class="label">{{ trans('global.total') }}</span>
                <span class="value">{{ number_format($invoice->amount ?? 0, 2) }} {{ trans('front.pound_unit') }}</span>
            </div>
        </div>
    </div>

    <div style="padding-bottom:60px;"></div>

</body>
</html>
