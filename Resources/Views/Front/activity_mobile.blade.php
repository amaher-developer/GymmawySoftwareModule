<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    @php
        $isRtl      = app()->getLocale() === 'ar';
        $textAlign  = $isRtl ? 'right' : 'left';

        $vatPercentage = @$mainSettings->vat_details['vat_percentage'] ?? 0;
        $basePrice     = (float) ($record->price ?? 0);
        $vatAmount     = ($vatPercentage / 100) * $basePrice;
        $priceWithVat  = (float) round($basePrice + $vatAmount, 2);

        $paymentsConfig = @$mainSettings->payments ?? [];
        $tabbyEnabled   = !empty($paymentsConfig['tabby']['public_key']);
        $tamaraEnabled  = !empty($paymentsConfig['tamara']['token']);
        $paytabsEnabled = !empty($paymentsConfig['paytabs']['server_key']);
        $paymobEnabled  = !empty($paymentsConfig['paymob']['api_key']);
    @endphp
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #fff; margin: 0; padding: 15px; direction: {{ $isRtl ? 'rtl' : 'ltr' }}; text-align: {{ $textAlign }}; color: #333; }
        .title { margin-bottom: 12px; font-size: 18px; }
        .price-box { background: #f5f5f5; border-radius: 8px; padding: 12px; margin-bottom: 12px; line-height: 1.8; color: #f97d04; }
        .section-title { font-size: 15px; margin: 15px 0 8px; }
        .payment-option { border-radius: 10px; border: 1px solid #f97d04; padding: 12px; margin-bottom: 10px; display: flex; align-items: flex-start; gap: 10px; }
        .payment-option input[type="radio"] { margin-top: 4px; width: 20px; height: 20px; flex-shrink: 0; }
        .payment-option .payment-details { flex: 1; }
        .payment-option label { font-weight: bold; font-size: 14px; cursor: pointer; display: block; margin-bottom: 5px; }
        .payment-option img { width: 80px; padding: 5px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px; }
        .payment-option .policy-msg { font-size: 11px; color: #666; }
        #tabbyCard { padding-top: 10px; width: 100%; }
        .btn-pay { width: 100%; padding: 14px; background: #f97d04; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .highlight-text { border-radius: 10px; border: 1px solid #ddd; padding: 10px; margin-bottom: 12px; }
        .form-control { width: 100%; padding: 10px; margin-bottom: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .gender-row { display: flex; align-items: center; gap: 15px; padding: 8px 0; }
        .gender-row input[type="radio"] { width: 18px; height: 18px; }
    </style>
</head>
<body>
    @if(
        \Session::has('error'))
        <script>window._toastrError = {!! json_encode(\Session::get('error')) !!};</script>
    @endif
    @if(\Session::has('success'))
        <script>window._toastrSuccess = {!! json_encode(\Session::get('success')) !!};</script>
    @endif

    <h4 class="title">{{ $record->name }}</h4>
    <div class="price-box">
        {{ trans('front.price') }}: {{ number_format($basePrice, 2) }} {{ trans('front.pound_unit') }}<br>
        @if($vatPercentage > 0)
            <small>{{ trans('front.vat') }} ({{ $vatPercentage }}%): {{ number_format($vatAmount, 2) }} {{ trans('front.pound_unit') }}</small><br>
        @endif
        <strong>{{ trans('global.total') }}: {{ number_format($priceWithVat, 2) }} {{ trans('front.pound_unit') }}</strong>
    </div>

    <form method="post" action="{{ route('sw.activity-invoice-mobile.submit') }}">
        {{ csrf_field() }}
        <input type="hidden" name="activity_id" value="{{ $record->id }}">
        <input type="hidden" name="token" value="{{ request('token') }}">

        @if(!$currentUser)
            <h5 class="section-title">{{ trans('front.register_info') }}:</h5>
            <div class="highlight-text">
                <input type="text"  name="name"    class="form-control" placeholder="{{ trans('front.name') }}" required>
                <input type="text"  name="phone"   class="form-control" placeholder="{{ trans('front.phone') }}" required>
                <div class="gender-row">
                    <input type="radio" name="gender" value="1" id="male_a" required>
                    <label for="male_a">{{ trans('front.male') }}</label>
                    <input type="radio" name="gender" value="2" id="female_a">
                    <label for="female_a">{{ trans('front.female') }}</label>
                </div>
                <input type="date" name="dob" class="form-control" required>
                <input type="text" name="address" class="form-control" placeholder="{{ trans('front.address') }}" required>
            </div>
        @endif

        <h5 class="section-title">{{ trans('front.choose_payment_methods') }}:</h5>

        @if($tabbyEnabled)
            <div class="payment-option">
                <input type="radio" name="payment_method" value="2" id="tabby_a" required>
                <div class="payment-details">
                    <label for="tabby_a">{{ trans('front.tabby_installment_msg') }}</label>
                    <img src="{{ asset('resources/assets/new_front/images/tabby-logo.webp') }}" onerror="this.style.display='none'" alt="Tabby">
                    <div id="tabbyCard"></div>
                </div>
            </div>
        @endif

        @if($tamaraEnabled)
            <div class="payment-option">
                <input type="radio" name="payment_method" value="4" id="tamara_a">
                <div class="payment-details">
                    <label for="tamara_a">{{ trans('front.tamara_installment_msg') }}</label>
                    <img src="https://cdn.tamara.co/assets/png/tamara-logo-badge-{{ app()->getLocale() == 'ar' ? 'ar' : 'en' }}.png" alt="Tamara">
                </div>
            </div>
        @endif

        @if($paytabsEnabled)
            <div class="payment-option">
                <input type="radio" name="payment_method" value="5" id="paytabs_a">
                <div class="payment-details">
                    <label for="paytabs_a">{{ trans('front.paytabs_payment_msg') }}</label>
                </div>
            </div>
        @endif

        @if($paymobEnabled)
            <div class="payment-option">
                <input type="radio" name="payment_method" value="6" id="paymob_a">
                <div class="payment-details">
                    <label for="paymob_a">{{ trans('front.paymob_payment_msg') }}</label>
                </div>
            </div>
        @endif

        <button type="submit" class="btn-pay">{{ trans('front.pay_now') }}</button>
    </form>

    @if($tabbyEnabled)
    <script src="https://checkout.tabby.ai/tabby-card.js"></script>
    <script>
        new TabbyCard({
            selector: '#tabbyCard',
            currency: '{{ $mainSettings->payments["tabby"]["currency"] ?? "SAR" }}',
            lang: '{{ app()->getLocale() }}',
            price: {{ $priceWithVat }},
            size: 'wide',
            theme: 'black',
            header: false,
            publicKey: '{{ $mainSettings->payments["tabby"]["public_key"] ?? "" }}',
            merchantCode: '{{ $mainSettings->payments["tabby"]["merchant_code"] ?? "" }}'
        });
    </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    <script>
        toastr.options = { closeButton: true, progressBar: true, positionClass: '{{ app()->getLocale() === "ar" ? "toast-top-left" : "toast-top-right" }}', timeOut: 6000 };
        if (typeof window._toastrError !== 'undefined') toastr.error(window._toastrError);
        if (typeof window._toastrSuccess !== 'undefined') toastr.success(window._toastrSuccess);
    </script>
</body>
</html>
