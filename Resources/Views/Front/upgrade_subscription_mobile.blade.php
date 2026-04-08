<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    @php
        $isRtl     = app()->getLocale() === 'ar';
        $textAlign = $isRtl ? 'right' : 'left';
        $dir       = $isRtl ? 'rtl' : 'ltr';
        $sideStart = $isRtl ? 'right' : 'left';

        $paymentsConfig = @$mainSettings->payments ?? [];
        $tabbyEnabled   = !empty($paymentsConfig['tabby']['public_key']);
        $tamaraEnabled  = !empty($paymentsConfig['tamara']['token']);
        $paytabsEnabled = !empty($paymentsConfig['paytabs']['server_key']);
        $paymobEnabled  = !empty($paymentsConfig['paymob']['api_key']);
        $currency       = trans('front.pound_unit');
    @endphp
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5; margin: 0; padding: 15px;
            direction: {{ $dir }}; text-align: {{ $textAlign }}; color: #222;
        }
        h2.page-title { font-size: 20px; margin: 0 0 14px; color: #222; }

        /* ── Current subscription card ── */
        .current-card {
            background: #fff; border-radius: 14px;
            padding: 14px 16px; margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,.07);
        }
        .current-card .label { font-size: 11px; color: #999; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
        .current-card .sub-name { font-size: 16px; font-weight: 700; color: #222; }
        .current-card .meta-row { display: flex; gap: 18px; margin-top: 8px; font-size: 13px; color: #666; }
        .current-card .meta-row span { display: flex; align-items: center; gap: 4px; }
        .badge-active {
            display: inline-block; background: #e6f4ea; color: #1a7a2e;
            border-radius: 20px; padding: 2px 10px; font-size: 11px; font-weight: 600;
            margin-{{ $sideStart }}: 8px; vertical-align: middle;
        }

        /* ── Section title ── */
        .section-title {
            font-size: 15px; font-weight: 700; color: #333;
            margin: 20px 0 10px; display: flex; align-items: center; gap: 8px;
        }
        .section-title::after {
            content: ''; flex: 1; height: 1px; background: #e5e5e5;
        }

        /* ── Upgrade plan cards ── */
        .plan-card {
            background: #fff; border: 2px solid #e0e0e0;
            border-radius: 14px; padding: 14px 16px;
            margin-bottom: 12px; cursor: pointer; position: relative;
            transition: border-color .18s, box-shadow .18s;
        }
        .plan-card.selected {
            border-color: #f97d04;
            box-shadow: 0 0 0 3px rgba(249,125,4,.12);
        }
        .plan-card input[type="radio"] { display: none; }

        .plan-card-top {
            display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;
        }
        .plan-radio-dot {
            width: 22px; height: 22px; border: 2px solid #ccc;
            border-radius: 50%; flex-shrink: 0; margin-top: 2px;
            display: flex; align-items: center; justify-content: center;
        }
        .plan-card.selected .plan-radio-dot { border-color: #f97d04; }
        .plan-radio-dot-inner {
            width: 11px; height: 11px; border-radius: 50%; background: #f97d04; display: none;
        }
        .plan-card.selected .plan-radio-dot-inner { display: block; }

        .plan-info { flex: 1; }
        .plan-name { font-size: 15px; font-weight: 700; color: #222; }
        .plan-period { font-size: 12px; color: #888; margin-top: 2px; }

        .plan-price-col { text-align: {{ $isRtl ? 'left' : 'right' }}; flex-shrink: 0; }
        .plan-price { font-size: 18px; font-weight: 800; color: #222; line-height: 1.2; }
        .plan-price-label { font-size: 10px; color: #aaa; }

        /* Diff breakdown (shown after selection) */
        .diff-box {
            margin-top: 12px; background: #fdf5ec;
            border: 1px solid #f5d9b6; border-radius: 10px;
            padding: 12px 14px; font-size: 13px; line-height: 1.9;
        }
        .diff-box .diff-row { display: flex; justify-content: space-between; color: #555; }
        .diff-box .diff-row.total { font-weight: 700; font-size: 15px; color: #f97d04; }

        /* ── Payment options ── */
        .payment-option {
            border-radius: 10px; border: 1px solid #f97d04;
            padding: 12px; margin-bottom: 10px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .payment-option input[type="radio"] { margin-top: 4px; width: 20px; height: 20px; flex-shrink: 0; }
        .payment-option .payment-details { flex: 1; }
        .payment-option label { font-weight: bold; font-size: 14px; cursor: pointer; display: block; margin-bottom: 5px; }
        .payment-option img { height: 36px; width: auto; padding: 4px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px; object-fit: contain; }
        .payment-option .policy-msg { font-size: 11px; color: #666; }

        /* ── Pay button ── */
        .btn-pay {
            width: 100%; padding: 15px; background: #f97d04; color: #fff;
            border: none; border-radius: 10px; font-size: 16px;
            font-weight: bold; cursor: pointer; margin-top: 14px;
        }
        .btn-pay:disabled { background: #ccc; cursor: not-allowed; }
        .btn-pay:active:not(:disabled) { background: #e06c00; }

        .no-plans { text-align: center; color: #888; font-size: 14px; padding: 30px 0; }
    </style>
</head>
<body>

    @if(\Session::has('error'))
        <script>window._toastrError = {!! json_encode(\Session::get('error')) !!};</script>
    @endif

    <h2 class="page-title">{{ $title }}</h2>

    {{-- ── Current subscription ── --}}
    <div class="current-card">
        <div class="label">{{ trans('sw.upgrade_current_subscription') }}</div>
        <div class="sub-name">
            {{ optional($activeSub->subscription)->name }}
            <span class="badge-active">{{ trans('sw.active') }}</span>
        </div>
        <div class="meta-row">
            <span>📅 {{ \Carbon\Carbon::parse($activeSub->joining_date)->format('Y-m-d') }}</span>
            <span>⏳ {{ \Carbon\Carbon::parse($activeSub->expire_date)->format('Y-m-d') }}</span>
        </div>
        <div class="meta-row" style="margin-top:4px;">
            <span>💰 {{ trans('sw.upgrade_current_price') }}: <strong>{{ number_format($currentPrice, 2) }} {{ $currency }}</strong></span>
        </div>
    </div>

    @if($upgrades->isEmpty())
        <p class="no-plans">{{ trans('sw.upgrade_no_plans') }}</p>
    @else

    <form method="post" action="{{ route('sw.upgrade-invoice-mobile.submit') }}" id="upgradeForm">
        {{ csrf_field() }}
        <input type="hidden" name="token"                value="{{ request('token') }}">
        <input type="hidden" name="old_subscription_id" value="{{ $activeSub->subscription_id }}">
        <input type="hidden" name="active_member_sub_id" value="{{ $activeSub->id }}">
        <input type="hidden" name="subscription_id"      id="hiddenSubId"    value="">
        <input type="hidden" name="amount"               id="hiddenAmount"   value="">
        <input type="hidden" name="vat_percentage"       value="{{ $vatPercentage }}">

        {{-- ── Plan cards ── --}}
        <div class="section-title">{{ trans('sw.upgrade_choose_plan') }}</div>

        @php
            $vatPct = (float) $vatPercentage;
        @endphp

        @foreach($upgrades as $plan)
        @php
            $planPrice     = (float) $plan->price;
            // Effective price after discount
            $discType  = $plan->default_discount_type  ?? 0;
            $discVal   = (float) ($plan->default_discount_value ?? 0);
            if ($discVal > 0) {
                $discounted = $discType == 1
                    ? $planPrice * (1 - $discVal / 100)
                    : max(0, $planPrice - $discVal);
            } else {
                $discounted = $planPrice;
            }
            $discountedWithVat = round($discounted + ($discounted * $vatPct / 100), 2);
            $currentWithVat    = round($currentPrice + ($currentPrice * $vatPct / 100), 2);
            $diffWithVat       = max(0, round($discountedWithVat - $currentWithVat, 2));
            $diffBase          = $vatPct > 0 ? round($diffWithVat / (1 + $vatPct / 100), 2) : $diffWithVat;
            $diffVat           = round($diffWithVat - $diffBase, 2);
        @endphp
        <div class="plan-card" id="planCard{{ $plan->id }}"
             onclick="selectPlan({{ $plan->id }}, {{ $discountedWithVat }}, {{ $diffWithVat }}, {{ $diffBase }}, {{ $diffVat }}, '{{ addslashes($plan->name) }}', {{ $planPrice }})">
            <input type="radio" name="_plan_radio" value="{{ $plan->id }}">
            <div class="plan-card-top">
                <div class="plan-radio-dot"><div class="plan-radio-dot-inner"></div></div>
                <div class="plan-info">
                    <div class="plan-name">{{ $plan->name }}</div>
                    <div class="plan-period">{{ $plan->period }} {{ trans('sw.day_2') }}</div>
                </div>
                <div class="plan-price-col">
                    <div class="plan-price">{{ number_format($discountedWithVat, 2) }}</div>
                    <div class="plan-price-label">{{ $currency }}</div>
                </div>
            </div>

            {{-- Diff breakdown (hidden until selected) --}}
            <div class="diff-box" id="diff{{ $plan->id }}" style="display:none;">
                <div class="diff-row">
                    <span>{{ trans('sw.upgrade_new_price') }}</span>
                    <span>{{ number_format($discountedWithVat, 2) }} {{ $currency }}</span>
                </div>
                <div class="diff-row">
                    <span>{{ trans('sw.upgrade_current_price') }}</span>
                    <span>- {{ number_format($currentWithVat, 2) }} {{ $currency }}</span>
                </div>
                @if($vatPct > 0)
                <div class="diff-row">
                    <span>{{ trans('front.vat') }} ({{ $vatPct }}%)</span>
                    <span>{{ number_format($diffVat, 2) }} {{ $currency }}</span>
                </div>
                @endif
                <div class="diff-row total">
                    <span>{{ trans('sw.upgrade_difference_price') }}</span>
                    <span>{{ number_format($diffWithVat, 2) }} {{ $currency }}</span>
                </div>
            </div>
        </div>
        @endforeach

        {{-- ── Payment method ── --}}
        <div class="section-title" id="paymentSection" style="display:none;">{{ trans('front.choose_payment_methods') }}</div>

        @if($tabbyEnabled)
        <div class="payment-option" id="payOpt_tabby" style="display:none;">
            <input type="radio" name="payment_method" value="2" id="tabby_u">
            <div class="payment-details">
                <label for="tabby_u">{{ trans('front.tabby_installment_msg') }}</label>
                <img src="{{ asset('resources/assets/new_front/images/tabby-logo.webp') }}" onerror="this.style.display='none'" alt="Tabby">
                <span class="policy-msg">{{ trans('front.tabby_policy_msg') }}</span>
            </div>
        </div>
        @endif

        @if($tamaraEnabled)
        <div class="payment-option" id="payOpt_tamara" style="display:none;">
            <input type="radio" name="payment_method" value="4" id="tamara_u">
            <div class="payment-details">
                <label for="tamara_u">{{ trans('front.tamara_installment_msg') }}</label>
                <img src="https://cdn.tamara.co/assets/png/tamara-logo-badge-{{ app()->getLocale() == 'ar' ? 'ar' : 'en' }}.png" alt="Tamara">
                <span class="policy-msg">{{ trans('front.tamara_policy_msg') }}</span>
            </div>
        </div>
        @endif

        @if($paytabsEnabled)
        <div class="payment-option" id="payOpt_paytabs" style="display:none;">
            <input type="radio" name="payment_method" value="5" id="paytabs_u">
            <div class="payment-details">
                <label for="paytabs_u">{{ trans('front.paytabs_payment_msg') }}</label>
                <p style="margin:5px 0 0;">
                    <img src="{{ asset('resources/assets/new_front/images/paytabs-logo.svg') }}" alt="PayTabs">
                    <img src="{{ asset('resources/assets/new_front/images/visa_logo.svg') }}" alt="Visa">
                    <img src="{{ asset('resources/assets/new_front/images/mastercard-logo.svg') }}" alt="Mastercard">
                    <img src="{{ asset('resources/assets/new_front/images/mada-logo.svg') }}" alt="Mada">
                    <img src="{{ asset('resources/assets/new_front/images/apple-pay-logo.svg') }}" alt="Apple Pay">
                </p>
                <span class="policy-msg">{{ trans('front.paytabs_policy_msg') }}</span>
            </div>
        </div>
        @endif

        @if($paymobEnabled)
        <div class="payment-option" id="payOpt_paymob" style="display:none;">
            <input type="radio" name="payment_method" value="6" id="paymob_u">
            <div class="payment-details">
                <label for="paymob_u">{{ trans('front.paymob_payment_msg') }}</label>
                <p style="margin:5px 0 0;">
                    <img src="{{ asset('resources/assets/new_front/images/visa_logo.svg') }}" alt="Visa">
                    <img src="{{ asset('resources/assets/new_front/images/mastercard-logo.svg') }}" alt="Mastercard">
                    <img src="{{ asset('resources/assets/new_front/images/mada-logo.svg') }}" alt="Mada">
                </p>
                <span class="policy-msg">{{ trans('front.paymob_policy_msg') }}</span>
            </div>
        </div>
        @endif

        <button type="submit" class="btn-pay" id="payBtn" disabled>{{ trans('front.pay_now') }}</button>
    </form>

    @endif

    <div style="padding-bottom:60px;"></div>

    {{-- Tamara widget --}}
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

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    <script>
        var selectedPlanId  = null;
        var paymentOpts     = ['tabby','tamara','paytabs','paymob'];

        function selectPlan(planId, fullPrice, diffTotal, diffBase, diffVat, planName, rawPrice) {
            // Deselect all cards
            document.querySelectorAll('.plan-card').forEach(function(el) {
                el.classList.remove('selected');
                var diffBox = el.querySelector('.diff-box');
                if (diffBox) diffBox.style.display = 'none';
            });

            var card = document.getElementById('planCard' + planId);
            card.classList.add('selected');
            document.getElementById('diff' + planId).style.display = 'block';

            selectedPlanId = planId;
            document.getElementById('hiddenSubId').value  = planId;
            document.getElementById('hiddenAmount').value = diffTotal;

            // Show payment section + options
            document.getElementById('paymentSection').style.display = 'flex';
            paymentOpts.forEach(function(g) {
                var el = document.getElementById('payOpt_' + g);
                if (el) el.style.display = 'flex';
            });

            checkPayBtn();
        }

        document.querySelectorAll('input[name="payment_method"]').forEach(function(el) {
            el.addEventListener('change', checkPayBtn);
        });

        function checkPayBtn() {
            var hasPayment = !!document.querySelector('input[name="payment_method"]:checked');
            document.getElementById('payBtn').disabled = !(selectedPlanId && hasPayment);
        }

        // Toastr
        toastr.options = {
            closeButton: true, progressBar: true,
            positionClass: '{{ app()->getLocale() === "ar" ? "toast-top-left" : "toast-top-right" }}',
            timeOut: 6000,
        };
        if (typeof window._toastrError !== 'undefined') toastr.error(window._toastrError);
        var qError = new URLSearchParams(window.location.search).get('error');
        if (qError) toastr.error(decodeURIComponent(qError));
    </script>
</body>
</html>
