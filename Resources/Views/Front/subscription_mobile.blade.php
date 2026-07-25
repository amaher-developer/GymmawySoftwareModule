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
        $originalPrice = $record['price'];
        $discountType  = $record['default_discount_type'] ?? 0;
        $discountValue = $record['default_discount_value'] ?? 0;

        if ($discountType == 1 && $discountValue > 0) {
            $discountAmount = round(($discountValue / 100) * $originalPrice, 2);
            $discountLabel  = trans('front.discount') . ' (' . $discountValue . '%)';
        } elseif ($discountType == 2 && $discountValue > 0) {
            $discountAmount = round($discountValue, 2);
            $discountLabel  = trans('front.discount');
        } else {
            $discountAmount = 0;
            $discountLabel  = '';
        }

        $priceBeforeVat = round($originalPrice - $discountAmount, 2);
        $vatAmount      = ($vatPercentage / 100) * $priceBeforeVat;
        $priceWithVat   = (float) round($priceBeforeVat + $vatAmount, 2);

        // Resolve which payment methods are configured
        $paymentsConfig = @$mainSettings->payments ?? [];
        $tabbyEnabled   = !empty($paymentsConfig['tabby']['public_key']);
        $tamaraEnabled  = !empty($paymentsConfig['tamara']['token']);
        $paytabsEnabled = !empty($paymentsConfig['paytabs']['server_key']);
        $paymobEnabled  = !empty($paymentsConfig['paymob']['api_key']);
    @endphp
    <style>
        * { box-sizing: border-box; }
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #fff;
            margin: 0;
            padding: 15px;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $textAlign }};
            color: #333;
        }
        .subscription-header { margin-bottom: 15px; }
        .subscription-header h4 { font-size: 18px; margin: 0 0 10px; }
        .price-box {
            background: #f5f5f5;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
            color: #f97d04;
            line-height: 1.8;
            margin-bottom: 10px;
        }
        .price-box small { font-size: 12px; color: #555; }
        .section-title { font-size: 15px; margin: 15px 0 8px; }
        .highlight-text {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 12px;
        }
        .payment-option {
            border-radius: 10px;
            border: 1px solid #f97d04;
            padding: 12px;
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }
        .payment-option input[type="radio"] { margin-top: 4px; width: 20px; height: 20px; flex-shrink: 0; }
        .payment-option .payment-details {
            flex: 1;
            min-width: 0;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .payment-option label { font-weight: bold; font-size: 14px; cursor: pointer; display: block; margin-bottom: 5px; }
        .payment-option img { width: 80px; padding: 5px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px; }
        .payment-option .policy-msg { font-size: 11px; color: #666; }
        .payment-option.payment-option-tabby {
            display: block;
            padding: 10px;
        }
        .payment-option-tabby .payment-option-head {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            width: 100%;
        }
        .payment-option-tabby .payment-option-head input[type="radio"] {
            margin-top: 2px;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .payment-option-tabby .payment-option-head label {
            flex: 1;
            min-width: 0;
            margin: 0;
        }
        .payment-option-tabby .payment-details {
            width: 100%;
            margin-top: 8px;
            min-width: 0;
        }
        .tabby-widget-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }
        #tabbyCard {
            padding-top: 10px;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
            min-width: 0;
        }
        #tabbyCard > * {
            max-width: 100% !important;
            min-width: 0 !important;
        }
        #tabbyCard iframe {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            display: block !important;
        }
        #tabbyCard div:first-child { background-color: #f5f5f5 !important; }
        .form-control {
            width: 100%;
            padding: 10px;
            margin-bottom: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .gender-row { display: flex; align-items: center; gap: 15px; padding: 8px 0; }
        .gender-row label { font-size: 14px; margin: 0; }
        .gender-row input[type="radio"] { width: 18px; height: 18px; }
        .btn-pay {
            width: 100%;
            padding: 14px;
            background: #f97d04;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-pay:active { background: #e06c00; }
        ::placeholder { color: #bbb !important; }

        @media (max-width: 420px) {
            .payment-option {
                padding: 10px;
                gap: 8px;
            }
            .payment-option-tabby .payment-option-head {
                gap: 8px;
            }
            .payment-option label {
                font-size: 13px;
                line-height: 1.35;
            }
            .payment-option .policy-msg {
                display: block;
                margin-top: 6px;
                line-height: 1.35;
            }
        }
    </style>
</head>
<body>

    {{-- Toastr error injection --}}
    @if(\Session::has('error'))
        <script>window._toastrError = {!! json_encode(\Session::get('error')) !!};</script>
    @endif
    @if($errors->any())
        <script>window._toastrValidation = {!! json_encode($errors->all()) !!};</script>
    @endif

    {{-- Subscription header --}}
    <div class="subscription-header">
        <h4>{{ $record['name'] }}</h4>
        <div class="price-box">
            @if($discountAmount > 0)
                <small style="text-decoration: line-through; color: #999;">
                    {{ number_format($originalPrice, 2) }} {{ trans('front.pound_unit') }}
                </small><br>
                <small style="color: green;">
                    {{ $discountLabel }}: -{{ number_format($discountAmount, 2) }} {{ trans('front.pound_unit') }}
                </small><br>
            @endif
            {{ trans('front.price') }}: {{ number_format($priceBeforeVat, 2) }} {{ trans('front.pound_unit') }}<br>
            @if($vatPercentage > 0)
                <small>{{ trans('front.vat') }} ({{ $vatPercentage }}%): {{ number_format($vatAmount, 2) }} {{ trans('front.pound_unit') }}</small><br>
                <strong>{{ trans('global.total') }}: {{ $priceWithVat }} {{ trans('front.pound_unit') }}</strong>
            @endif
        </div>
    </div>

    <form method="post" action="{{ route('sw.invoice-mobile.submit') }}">
        {{ csrf_field() }}
        <input type="hidden" name="subscription_id" value="{{ $record['id'] }}">
        <input type="hidden" name="amount"          value="{{ $priceWithVat }}">
        <input type="hidden" name="vat_percentage"  value="{{ $vatPercentage }}">
        <input type="hidden" name="token"           value="{{ request('payment_link_token') ?: request('token') ?: 'null' }}">
        <input type="hidden" name="member_id"       value="{{ optional($currentUser)->id ?: 'null' }}">

        {{-- Guest registration fields --}}
        @if(!$currentUser)
            <h5 class="section-title">{{ trans('front.register_info') }}:</h5>
            <div class="highlight-text">
                <input type="text"  name="name"    class="form-control" placeholder="{{ trans('front.name') }}"  value="{{ old('name') }}"  required>
                <input type="text"  name="phone"   class="form-control" placeholder="{{ trans('front.phone') }}" value="{{ old('phone') }}" required>
                <div class="gender-row">
                    <input type="radio" name="gender" value="1" id="male_m"
                        {{ old('gender') == 1 ? 'checked' : '' }} required>
                    <label for="male_m">{{ trans('front.male') }}</label>
                    <input type="radio" name="gender" value="2" id="female_m"
                        {{ old('gender') == 2 ? 'checked' : '' }}>
                    <label for="female_m">{{ trans('front.female') }}</label>
                </div>
                <label style="font-size: 13px; color: #555; margin-bottom: 2px; display: block;">{{ trans('front.birthdate') }}</label>
                <input type="date" name="dob"     class="form-control" value="{{ old('dob') }}" required>
                <input type="text" name="address" class="form-control" placeholder="{{ trans('front.address') }}" value="{{ old('address') }}" required>
            </div>
        @else
            <input type="hidden" name="name"    value="{{ $currentUser->name }}">
            <input type="hidden" name="phone"   value="{{ $currentUser->phone }}">
            <input type="hidden" name="gender"  value="{{ $currentUser->gender }}">
            <input type="hidden" name="dob"     value="{{ $currentUser->dob ? \Carbon\Carbon::parse($currentUser->dob)->format('Y-m-d') : '' }}">
            <input type="hidden" name="address" value="{{ $currentUser->address }}">
            {{-- Show member identity to confirm who is paying --}}
            <div style="background:#f0f7ff;border:1px solid #b8d4ef;border-radius:8px;padding:12px 14px;margin-bottom:14px;">
                <div style="font-size:13px;color:#555;margin-bottom:4px;">{{ trans('front.member_name') }}</div>
                <div style="font-size:15px;font-weight:600;color:#1a3a5c;">{{ $currentUser->name }}</div>
                @if($currentUser->code)
                <div style="font-size:12px;color:#777;margin-top:4px;">
                    {{ trans('front.member_code') }}: <strong>{{ ltrim($currentUser->getRawOriginal('code') ?? $currentUser->code, '0') ?: '—' }}</strong>
                </div>
                @endif
            </div>
        @endif

        {{-- Joining date --}}
        <h5 class="section-title">{{ trans('front.register_info_joining_date') }}:</h5>
        <div class="highlight-text">
            <label style="font-size: 13px; color: #555; margin-bottom: 2px; display: block;">
                {{ trans('front.register_info_joining_date') }}
            </label>
            <input type="date" name="joining_date" class="form-control"
                   value="{{ old('joining_date', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                   min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                   max="{{ \Carbon\Carbon::now()->addMonths(12)->format('Y-m-d') }}"
                   required>
        </div>

        {{-- Payment method selection --}}
        <h5 class="section-title">{{ trans('front.choose_payment_methods') }}:</h5>

        @if($tabbyEnabled)
        <div class="payment-option payment-option-tabby">
            <div class="payment-option-head">
                <input type="radio" name="payment_method" value="2" id="tabby_m"
                    {{ old('payment_method') == '2' ? 'checked' : '' }}>
                <label for="tabby_m">{{ trans('front.tabby_installment_msg') }}</label>
            </div>
            <div class="payment-details">
                <img src="{{ asset('resources/assets/new_front/images/tabby-logo.webp') }}"
                     onerror="this.style.display='none'" alt="Tabby">
                <span class="policy-msg">{{ trans('front.tabby_policy_msg') }}</span>
                <div class="tabby-widget-wrap">
                    <div id="tabbyCard"></div>
                </div>
            </div>
        </div>
        @endif

        @if($tamaraEnabled)
        <div class="payment-option">
            <input type="radio" name="payment_method" value="4" id="tamara_m"
                {{ old('payment_method') == '4' ? 'checked' : '' }}>
            <div class="payment-details">
                <label for="tamara_m">{{ trans('front.tamara_installment_msg') }}</label>
                <img src="https://cdn.tamara.co/assets/png/tamara-logo-badge-{{ app()->getLocale() == 'ar' ? 'ar' : 'en' }}.png"
                     alt="Tamara">
                <span class="policy-msg">{{ trans('front.tamara_policy_msg') }}</span>
                <div style="padding-top: 10px;">
                    <tamara-widget type="tamara-summary" amount="{{ $priceWithVat }}" inline-type="2"></tamara-widget>
                </div>
            </div>
        </div>
        @endif

        @if($paytabsEnabled)
        <div class="payment-option">
            <input type="radio" name="payment_method" value="5" id="paytabs_m"
                {{ old('payment_method') == '5' ? 'checked' : '' }}>
            <div class="payment-details">
                <label for="paytabs_m">{{ trans('front.paytabs_payment_msg') }}</label>
                <p style="margin: 5px 0 0;">
                    <img style="height: 40px; width: auto; padding: 5px; margin: 4px 2px; border: 1px solid #ccc; border-radius: 5px; object-fit: contain;"
                         src="{{ asset('resources/assets/new_front/images/paytabs-logo.svg') }}"
                         onerror="this.style.display='none'" alt="PayTabs">
                    <img style="height: 40px; width: auto; padding: 5px; margin: 4px 2px; border: 1px solid #ccc; border-radius: 5px; object-fit: contain;"
                         src="{{ asset('resources/assets/new_front/images/visa_logo.svg') }}" alt="Visa">
                    <img style="height: 40px; width: auto; padding: 5px; margin: 4px 2px; border: 1px solid #ccc; border-radius: 5px; object-fit: contain;"
                         src="{{ asset('resources/assets/new_front/images/mastercard-logo.svg') }}" alt="Mastercard">
                    <img style="height: 40px; width: auto; padding: 5px; margin: 4px 2px; border: 1px solid #ccc; border-radius: 5px; object-fit: contain;"
                         src="{{ asset('resources/assets/new_front/images/mada-logo.svg') }}" alt="Mada">
                    <img style="height: 40px; width: auto; padding: 5px; margin: 4px 2px; border: 1px solid #ccc; border-radius: 5px; object-fit: contain;"
                         src="{{ asset('resources/assets/new_front/images/apple-pay-logo.svg') }}" alt="Apple Pay">
                </p>
                <span class="policy-msg">{{ trans('front.paytabs_policy_msg') }}</span>
            </div>
        </div>
        @endif

        @if($paymobEnabled)
        <div class="payment-option">
            <input type="radio" name="payment_method" value="6" id="paymob_m"
                {{ old('payment_method') == '6' ? 'checked' : '' }}>
            <div class="payment-details">
                <label for="paymob_m">{{ trans('front.paymob_payment_msg') }}</label>
                <p style="margin: 5px 0 0;">
                    <img style="height: 40px; width: auto; padding: 5px; margin: 4px 2px; border: 1px solid #ccc; border-radius: 5px; object-fit: contain;"
                         src="{{ asset('resources/assets/new_front/images/visa_logo.svg') }}" alt="Visa">
                    <img style="height: 40px; width: auto; padding: 5px; margin: 4px 2px; border: 1px solid #ccc; border-radius: 5px; object-fit: contain;"
                         src="{{ asset('resources/assets/new_front/images/mastercard-logo.svg') }}" alt="Mastercard">
                    <img style="height: 40px; width: auto; padding: 5px; margin: 4px 2px; border: 1px solid #ccc; border-radius: 5px; object-fit: contain;"
                         src="{{ asset('resources/assets/new_front/images/mada-logo.svg') }}" alt="Mada">
                </p>
                <span class="policy-msg">{{ trans('front.paymob_policy_msg') }}</span>
            </div>
        </div>
        @endif

        <button type="submit" class="btn-pay">{{ trans('front.pay_now') }}</button>
    </form>

    <div style="padding-bottom: 60px;"></div>

    {{-- Tamara widget config --}}
    @if($tamaraEnabled)
    <script>
        window.tamaraWidgetConfig = {
            lang: '{{ app()->getLocale() }}',
            country: '{{ $mainSettings->payments["tamara"]["country"] ?? "SA" }}',
            publicKey: '{{ $mainSettings->payments["tamara"]["public_key"] ?? "" }}'
        };
    </script>
    <script defer src="https://cdn.tamara.co/widget-v2/tamara-widget.js"></script>
    @endif

    {{-- Tabby promo card --}}
    @if($tabbyEnabled)
    <script src="https://checkout.tabby.ai/tabby-card.js"></script>
    <script>
        (function () {
            var resizeTimer = null;

            function tabbySize() {
                return window.matchMedia('(max-width: 560px)').matches ? 'narrow' : 'wide';
            }

            function renderTabbyCard() {
                if (!window.TabbyCard) return;

                var container = document.getElementById('tabbyCard');
                if (!container) return;

                try {
                    if (window._tabbyCard && typeof window._tabbyCard.destroy === 'function') {
                        window._tabbyCard.destroy();
                    }
                } catch (e) {}

                container.innerHTML = '';

                window._tabbyCard = new TabbyCard({
                    selector: '#tabbyCard',
                    currency: '{{ $mainSettings->payments["tabby"]["currency"] ?? "SAR" }}',
                    lang: '{{ app()->getLocale() }}',
                    price: {{ $priceWithVat }},
                    size: tabbySize(),
                    theme: 'black',
                    header: false,
                    publicKey: '{{ $mainSettings->payments["tabby"]["public_key"] ?? "" }}',
                    merchantCode: '{{ $mainSettings->payments["tabby"]["merchant_code"] ?? "" }}'
                });
            }

            window.addEventListener('load', function () {
                setTimeout(renderTabbyCard, 80);
            });

            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(renderTabbyCard, 140);
            });
        })();
    </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: '{{ app()->getLocale() === "ar" ? "toast-top-left" : "toast-top-right" }}',
            timeOut: 6000,
        };
        if (typeof window._toastrError !== 'undefined') {
            toastr.error(window._toastrError);
        }
        if (typeof window._toastrValidation !== 'undefined') {
            window._toastrValidation.forEach(function(msg) { toastr.error(msg); });
        }
        // Also show error from URL ?error= param (after gateway redirect back)
        var qError = new URLSearchParams(window.location.search).get('error');
        if (qError) toastr.error(decodeURIComponent(qError));
    </script>
</body>
</html>
