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

        $hasOptions = isset($optionGroups) && $optionGroups instanceof \Illuminate\Database\Eloquent\Collection && $optionGroups->isNotEmpty();
        $hasActivities = isset($activities) && $activities instanceof \Illuminate\Support\Collection && $activities->isNotEmpty();

        // Resolve which payment methods are configured
        $paymentsConfig = @$mainSettings->payments ?? [];
        $tabbyEnabled   = !empty($paymentsConfig['tabby']['public_key']);
        $tamaraEnabled  = !empty($paymentsConfig['tamara']['token']);
        $paytabsEnabled = !empty($paymentsConfig['paytabs']['server_key']);
        $paymobEnabled  = !empty($paymentsConfig['paymob']['api_key']);
        $paymobIntentionEnabled = !empty($paymentsConfig['paymob_intention']['secret_key']) && !empty($paymentsConfig['paymob_intention']['public_key']);
        $anyGatewayEnabled = $tabbyEnabled || $tamaraEnabled || $paytabsEnabled || $paymobEnabled || $paymobIntentionEnabled;
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

        /* Option groups */
        .option-group { margin-bottom: 14px; }
        .option-group-title { font-size: 14px; font-weight: 600; margin-bottom: 6px; color: #333; }
        .option-group-title .required-star { color: #e53; }
        .option-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; margin-bottom: 6px;
            border: 1px solid #ddd; border-radius: 8px;
            cursor: pointer; transition: border-color 0.15s;
        }
        .option-item:hover { border-color: #f97d04; }
        .option-item.selected { border-color: #f97d04; background: #fff8f2; }
        .option-item input[type="radio"],
        .option-item input[type="checkbox"] { width: 18px; height: 18px; flex-shrink: 0; accent-color: #f97d04; }
        .option-item-label { flex: 1; font-size: 14px; }
        .option-item-price { font-size: 13px; color: #f97d04; font-weight: 600; white-space: nowrap; }
        .activity-item-sub { display: block; font-size: 12px; color: #888; }
        .activity-input:disabled { accent-color: #ccc; }

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
            {{ trans('front.price') }}: <span id="js-base-display">{{ number_format($priceBeforeVat, 2) }}</span> {{ trans('front.pound_unit') }}
            @if($hasOptions)
            <span id="js-options-line" style="display:none;"><br>
                <small id="js-options-display" style="color:#555;"></small>
            </span>
            @endif
            <br>
            @if($vatPercentage > 0)
                <small>{{ trans('front.vat') }} ({{ $vatPercentage }}%): <span id="js-vat-display">{{ number_format($vatAmount, 2) }}</span> {{ trans('front.pound_unit') }}</small><br>
                <strong>{{ trans('global.total') }}: <span id="js-total-display">{{ $priceWithVat }}</span> {{ trans('front.pound_unit') }}</strong>
            @else
                <strong>{{ trans('global.total') }}: <span id="js-total-display">{{ $priceWithVat }}</span> {{ trans('front.pound_unit') }}</strong>
            @endif
        </div>
    </div>

    <form method="post" action="{{ route('sw.invoice-mobile.submit') }}">
        {{ csrf_field() }}
        <input type="hidden" name="subscription_id" value="{{ $record['id'] }}">
        <input type="hidden" id="js-amount-input" name="amount" value="{{ $priceWithVat }}">
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

        {{-- Subscription option groups --}}
        @if($hasOptions)
        <div id="option-groups-wrapper">
            <h5 class="section-title">{{ trans('sw.subscription_options', [], app()->getLocale()) }}:</h5>
            @foreach($optionGroups as $group)
            <div class="option-group" data-group-id="{{ $group->id }}"
                 data-selection-type="{{ $group->selection_type }}"
                 data-required="{{ $group->is_required ? '1' : '0' }}">
                <div class="option-group-title">
                    {{ $group->name }}
                    @if($group->is_required)
                        <span class="required-star">*</span>
                    @endif
                </div>
                @foreach($group->options as $option)
                @php
                    $inputType  = $group->selection_type === 'multiple' ? 'checkbox' : 'radio';
                    $groupName  = 'opt_group_' . $group->id;
                @endphp
                <label class="option-item" for="opt_{{ $option->id }}">
                    <input type="{{ $inputType }}"
                           id="opt_{{ $option->id }}"
                           data-group="{{ $groupName }}"
                           value="{{ $option->id }}"
                           data-price="{{ (float) $option->price_modifier }}"
                           data-option-id="{{ $option->id }}"
                           class="option-input">
                    <span class="option-item-label">{{ $option->name }}</span>
                    @if($option->price_modifier != 0)
                    <span class="option-item-price">
                        {{ $option->price_modifier > 0 ? '+' : '' }}{{ number_format((float)$option->price_modifier, 2) }} {{ trans('front.pound_unit') }}
                    </span>
                    @endif
                </label>
                @endforeach
            </div>
            @endforeach
        </div>

        {{-- Hidden inputs to collect all selected option_ids --}}
        <div id="js-option-ids-container"></div>
        @endif

        {{-- Allowed activities (does not affect price) --}}
        @if($hasActivities)
        <div id="activities-wrapper">
            <h5 class="section-title">{{ trans('sw.select_activities_for_member', [], app()->getLocale()) }}:</h5>
            @foreach($activities as $pivot)
            @php $activity = $pivot->activity; @endphp
            <label class="option-item activity-item" for="act_{{ $activity->id }}">
                <input type="checkbox"
                       id="act_{{ $activity->id }}"
                       name="activity_ids[]"
                       value="{{ $activity->id }}"
                       class="activity-input"
                       {{ (!$activityLimit || $loop->index < $activityLimit) ? 'checked' : '' }}>
                <span class="option-item-label">
                    {{ $activity->name }}
                    @if($activity->trainer)
                        <span class="activity-item-sub">{{ $activity->trainer->name }}</span>
                    @endif
                    <span class="activity-item-sub">{{ trans('sw.training_times', [], app()->getLocale()) }}: {{ (int) $pivot->training_times }}</span>
                </span>
            </label>
            @endforeach
        </div>
        @endif

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

        @if($paymobIntentionEnabled)
        <div class="payment-option">
            <input type="radio" name="payment_method" value="7" id="paymob_intention_m"
                {{ old('payment_method') == '7' ? 'checked' : '' }}>
            <div class="payment-details">
                <label for="paymob_intention_m">{{ trans('front.paymob_payment_msg') }}</label>
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

        @if(!$anyGatewayEnabled)
        <div style="background:#fff3f3;border:1px solid #f5c6cb;border-radius:8px;padding:14px;margin-bottom:14px;text-align:center;color:#721c24;font-size:14px;">
            <strong>{{ app()->getLocale() === 'ar' ? 'لا توجد طريقة دفع متاحة حالياً' : 'No payment method available' }}</strong><br>
            <small style="color:#999;">{{ app()->getLocale() === 'ar' ? 'يرجى التواصل مع الإدارة' : 'Please contact administration' }}</small>
        </div>
        <button type="button" class="btn-pay" disabled style="opacity:0.5;cursor:not-allowed;">{{ trans('front.pay_now') }}</button>
        @else
        <button type="submit" class="btn-pay">{{ trans('front.pay_now') }}</button>
        @endif
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

    @if($hasOptions)
    <script>
    var SW_BASE_BEFORE_VAT = {{ $priceBeforeVat }};
    var SW_VAT_PCT         = {{ $vatPercentage }};
    var SW_CURRENCY        = '{{ trans("front.pound_unit") }}';

    function swGetSelectedOptionIds() {
        var ids = [];
        document.querySelectorAll('.option-input:checked').forEach(function(el) {
            ids.push(parseInt(el.value));
        });
        return ids;
    }

    function swGetOptionsTotal() {
        var total = 0;
        document.querySelectorAll('.option-input:checked').forEach(function(el) {
            total += parseFloat(el.getAttribute('data-price')) || 0;
        });
        return total;
    }

    function swUpdateOptionPrice() {
        var optionsTotal   = swGetOptionsTotal();
        var newBeforeVat   = SW_BASE_BEFORE_VAT + optionsTotal;
        var vatAmount      = (SW_VAT_PCT / 100) * newBeforeVat;
        var totalWithVat   = newBeforeVat + vatAmount;

        // Update displayed values
        var baseEl = document.getElementById('js-base-display');
        if (baseEl) baseEl.textContent = newBeforeVat.toFixed(2);

        var vatEl = document.getElementById('js-vat-display');
        if (vatEl) vatEl.textContent = vatAmount.toFixed(2);

        var totalEl = document.getElementById('js-total-display');
        if (totalEl) totalEl.textContent = totalWithVat.toFixed(2);

        // Show/hide options modifier line
        var optLine = document.getElementById('js-options-line');
        var optDisp = document.getElementById('js-options-display');
        if (optLine && optDisp) {
            if (optionsTotal !== 0) {
                optDisp.textContent = (optionsTotal > 0 ? '+' : '') + optionsTotal.toFixed(2) + ' ' + SW_CURRENCY + ' ({{ app()->getLocale() === "ar" ? "خيارات إضافية" : "options" }})';
                optLine.style.display = 'inline';
            } else {
                optLine.style.display = 'none';
            }
        }

        // Update the hidden amount submitted to the payment gateway
        var amountInput = document.getElementById('js-amount-input');
        if (amountInput) amountInput.value = totalWithVat.toFixed(2);

        // Update option_ids hidden container
        var container = document.getElementById('js-option-ids-container');
        if (container) {
            container.innerHTML = '';
            swGetSelectedOptionIds().forEach(function(id) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'option_ids[]';
                inp.value = id;
                container.appendChild(inp);
            });
        }
    }

    // Style selected items, enforce radio mutual-exclusion, then update price
    document.querySelectorAll('.option-input').forEach(function(el) {
        el.addEventListener('change', function() {
            var group = this.closest('.option-group');
            if (!group) return;
            if (this.type === 'radio') {
                // Deselect all siblings first, then re-check the clicked one
                group.querySelectorAll('.option-input[type="radio"]').forEach(function(sibling) {
                    sibling.checked = false;
                    sibling.closest('.option-item').classList.remove('selected');
                });
                this.checked = true;
            }
            if (this.checked) {
                this.closest('.option-item').classList.add('selected');
            } else {
                this.closest('.option-item').classList.remove('selected');
            }
            // Always recalculate AFTER deselection is complete
            swUpdateOptionPrice();
        });
    });

    // Validate required groups on submit
    document.querySelector('form').addEventListener('submit', function(e) {
        var valid = true;
        document.querySelectorAll('.option-group[data-required="1"]').forEach(function(group) {
            var checked = group.querySelectorAll('.option-input:checked').length;
            if (checked === 0) {
                valid = false;
                group.querySelector('.option-group-title').style.color = '#e53';
            } else {
                group.querySelector('.option-group-title').style.color = '';
            }
        });
        if (!valid) {
            e.preventDefault();
            alert('{{ app()->getLocale() === "ar" ? "يرجى اختيار الخيارات المطلوبة" : "Please select the required options" }}');
        }
    });
    </script>
    @endif

    @if($hasActivities && $activityLimit)
    <script>
    (function() {
        var ACTIVITY_LIMIT = {{ (int) $activityLimit }};
        function enforceActivityLimit() {
            var checked = document.querySelectorAll('.activity-input:checked').length;
            document.querySelectorAll('.activity-input:not(:checked)').forEach(function(el) {
                el.disabled = checked >= ACTIVITY_LIMIT;
            });
        }
        document.querySelectorAll('.activity-input').forEach(function(el) {
            el.addEventListener('change', enforceActivityLimit);
        });
        enforceActivityLimit();
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
