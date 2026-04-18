<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    @php
        $isRtl          = app()->getLocale() === 'ar';
        $textAlign      = $isRtl ? 'right' : 'left';
        $vatPercentage  = (float) (@$mainSettings->vat_details['vat_percentage'] ?? 0);
        $paymentsConfig = @$mainSettings->payments ?? [];
        $tabbyEnabled   = !empty($paymentsConfig['tabby']['public_key']);
        $tamaraEnabled  = !empty($paymentsConfig['tamara']['token']);
        $paytabsEnabled = !empty($paymentsConfig['paytabs']['server_key']);
        $paymobEnabled  = !empty($paymentsConfig['paymob']['api_key']);

        // Per-activity price lookup for JS
        $activityPriceMap = $activities->mapWithKeys(fn($a) => [(string)$a->id => (float)($a->price ?? 0)])->toArray();
    @endphp
    <style>
        * { box-sizing: border-box; }
        html, body { max-width: 100%; overflow-x: hidden; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #fff; margin: 0; padding: 15px; direction: {{ $isRtl ? 'rtl' : 'ltr' }}; text-align: {{ $textAlign }}; color: #333; }
        .title { margin-bottom: 12px; font-size: 18px; }
        .price-box { background: #f5f5f5; border-radius: 8px; padding: 12px; margin-bottom: 12px; line-height: 1.8; color: #f97d04; font-size: 15px; }
        .section-title { font-size: 15px; margin: 15px 0 8px; font-weight: 600; }
        .activity-list { border: 1px solid #eee; border-radius: 8px; overflow: hidden; margin-bottom: 12px; }
        .activity-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-bottom: 1px solid #f0f0f0; }
        .activity-item:last-child { border-bottom: none; }
        .activity-item input[type="checkbox"] { width: 20px; height: 20px; flex-shrink: 0; accent-color: #f97d04; }
        .activity-item label { flex: 1; font-size: 14px; cursor: pointer; margin: 0; }
        .activity-item .act-price { font-size: 13px; color: #f97d04; font-weight: 600; white-space: nowrap; }
        .payment-option { border-radius: 10px; border: 1px solid #f97d04; padding: 12px; margin-bottom: 10px; display: flex; align-items: flex-start; gap: 10px; width: 100%; max-width: 100%; overflow: hidden; }
        .payment-option input[type="radio"] { margin-top: 4px; width: 20px; height: 20px; flex-shrink: 0; }
        .payment-option .payment-details { flex: 1; min-width: 0; overflow-wrap: anywhere; word-break: break-word; }
        .payment-option label { font-weight: bold; font-size: 14px; cursor: pointer; display: block; margin-bottom: 5px; }
        .payment-option img { width: 80px; padding: 5px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px; }
        .payment-option .policy-msg { font-size: 11px; color: #666; }
        #tabbyCard { padding-top: 10px; width: 100%; max-width: 100%; overflow: hidden; min-width: 0; }
        #tabbyCard > * { max-width: 100% !important; min-width: 0 !important; }
        #tabbyCard iframe { width: 100% !important; max-width: 100% !important; min-width: 0 !important; display: block !important; }
        .btn-pay { width: 100%; padding: 14px; background: #f97d04; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .btn-pay:disabled { background: #ccc; cursor: not-allowed; }
        .highlight-text { border-radius: 10px; border: 1px solid #ddd; padding: 10px; margin-bottom: 12px; }
        .form-control { width: 100%; padding: 10px; margin-bottom: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
    </style>
</head>
<body>
    @if(\Session::has('error'))
        <script>window._toastrError = {!! json_encode(\Session::get('error')) !!};</script>
    @endif
    @if(\Session::has('success'))
        <script>window._toastrSuccess = {!! json_encode(\Session::get('success')) !!};</script>
    @endif

    <h4 class="title">{{ trans('front.activities') ?? trans('sw.activities') }}</h4>

    {{-- Total summary box (updated by JS) --}}
    <div class="price-box" id="totalBox">
        <span id="totalLine">{{ trans('global.total') }}: <strong id="totalDisplay">0.00</strong> {{ trans('front.pound_unit') }}</span>
    </div>

    <form method="post" action="{{ route('sw.activity-invoice-mobile.submit') }}" id="activityForm">
        {{ csrf_field() }}
        <input type="hidden" name="activity_id" value="{{ $record->id }}">
        <input type="hidden" name="token" value="{{ request('payment_link_token') ?: request('token') ?: 'null' }}">
        <input type="hidden" name="member_id" value="{{ optional($currentUser)->id ?: 'null' }}">

        @if(!$currentUser)
            <h5 class="section-title">{{ trans('front.register_info') }}:</h5>
            <div class="highlight-text">
                <input type="text" name="name"  class="form-control" placeholder="{{ trans('front.name') }}"  required>
                <input type="text" name="phone" class="form-control" placeholder="{{ trans('front.phone') }}" required>
            </div>
        @endif

        {{-- Activity checkboxes --}}
        <h5 class="section-title">{{ trans('sw.activities') ?? trans('front.activities') ?? 'Activities' }}:</h5>
        <div class="activity-list">
            @foreach($activities as $act)
                @php
                    $actName  = $isRtl ? ($act->name_ar ?? $act->name_en ?? $act->name) : ($act->name_en ?? $act->name_ar ?? $act->name);
                    $actPrice = (float) ($act->price ?? 0);
                    $vatAmt   = $vatPercentage > 0 ? round($actPrice * $vatPercentage / 100, 2) : 0;
                    $total    = round($actPrice + $vatAmt, 2);
                @endphp
                <div class="activity-item">
                    <input type="checkbox" name="activity_ids[]" value="{{ $act->id }}"
                           id="act_{{ $act->id }}"
                           data-price="{{ $actPrice }}"
                           {{ $act->id == $record->id ? 'checked' : '' }}>
                    <label for="act_{{ $act->id }}">{{ $actName }}</label>
                    <span class="act-price">{{ number_format($total, 2) }} {{ trans('front.pound_unit') }}</span>
                </div>
            @endforeach
        </div>

        @if($vatPercentage > 0)
            <div style="font-size:12px;color:#888;margin-bottom:10px;">
                {{ trans('front.vat') }} ({{ $vatPercentage }}%) {{ trans('sw.included') ?? 'included' }}
            </div>
        @endif

        <h5 class="section-title">{{ trans('front.choose_payment_methods') }}:</h5>

        @if($tabbyEnabled)
            <div class="payment-option">
                <input type="radio" name="payment_method" value="2" id="tabby_a" required>
                <div class="payment-details">
                    <label for="tabby_a">{{ trans('front.tabby_installment_msg') }}</label>
                    <img src="{{ asset('resources/assets/new_front/images/tabby-logo.webp') }}" onerror="this.style.display='none'" alt="Tabby">
                    <span class="policy-msg">{{ trans('front.tabby_policy_msg') }}</span>
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
                    <span class="policy-msg">{{ trans('front.tamara_policy_msg') }}</span>
                </div>
            </div>
        @endif

        @if($paytabsEnabled)
            <div class="payment-option">
                <input type="radio" name="payment_method" value="5" id="paytabs_a">
                <div class="payment-details">
                    <label for="paytabs_a">{{ trans('front.paytabs_payment_msg') }}</label>
                    <p style="margin:5px 0 0;">
                        <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;" src="{{ asset('resources/assets/new_front/images/paytabs-logo.svg') }}" onerror="this.style.display='none'" alt="PayTabs">
                        <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;" src="{{ asset('resources/assets/new_front/images/visa_logo.svg') }}" alt="Visa">
                        <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;" src="{{ asset('resources/assets/new_front/images/mastercard-logo.svg') }}" alt="Mastercard">
                        <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;" src="{{ asset('resources/assets/new_front/images/mada-logo.svg') }}" alt="Mada">
                        <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;" src="{{ asset('resources/assets/new_front/images/apple-pay-logo.svg') }}" alt="Apple Pay">
                    </p>
                    <span class="policy-msg">{{ trans('front.paytabs_policy_msg') }}</span>
                </div>
            </div>
        @endif

        @if($paymobEnabled)
            <div class="payment-option">
                <input type="radio" name="payment_method" value="6" id="paymob_a">
                <div class="payment-details">
                    <label for="paymob_a">{{ trans('front.paymob_payment_msg') }}</label>
                    <p style="margin:5px 0 0;">
                        <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;" src="{{ asset('resources/assets/new_front/images/visa_logo.svg') }}" alt="Visa">
                        <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;" src="{{ asset('resources/assets/new_front/images/mastercard-logo.svg') }}" alt="Mastercard">
                        <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;" src="{{ asset('resources/assets/new_front/images/mada-logo.svg') }}" alt="Mada">
                    </p>
                    <span class="policy-msg">{{ trans('front.paymob_policy_msg') }}</span>
                </div>
            </div>
        @endif

        <button type="submit" class="btn-pay" id="payBtn">{{ trans('front.pay_now') }}</button>
    </form>

    @if($tabbyEnabled)
    <script src="https://checkout.tabby.ai/tabby-card.js"></script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    <script>
        var vatPct = {{ $vatPercentage }};
        var currency = '{{ trans('front.pound_unit') }}';

        function recalculate() {
            var base = 0;
            document.querySelectorAll('input[name="activity_ids[]"]:checked').forEach(function(cb) {
                base += parseFloat(cb.dataset.price || 0);
            });
            var vat = vatPct > 0 ? Math.round(base * vatPct / 100 * 100) / 100 : 0;
            var total = Math.round((base + vat) * 100) / 100;
            document.getElementById('totalDisplay').textContent = total.toFixed(2);

            var btn = document.getElementById('payBtn');
            btn.disabled = (total <= 0);

            @if($tabbyEnabled)
            if (window._tabbyCard) {
                try { window._tabbyCard.destroy(); } catch(e){}
            }
            window._tabbyCard = new TabbyCard({
                selector: '#tabbyCard',
                currency: '{{ $mainSettings->payments["tabby"]["currency"] ?? "SAR" }}',
                lang: '{{ app()->getLocale() }}',
                price: total,
                size: window.matchMedia('(max-width: 560px)').matches ? 'narrow' : 'wide', theme: 'black', header: false,
                publicKey: '{{ $mainSettings->payments["tabby"]["public_key"] ?? "" }}',
                merchantCode: '{{ $mainSettings->payments["tabby"]["merchant_code"] ?? "" }}'
            });
            @endif
        }

        document.querySelectorAll('input[name="activity_ids[]"]').forEach(function(cb) {
            cb.addEventListener('change', recalculate);
        });
        recalculate();

        toastr.options = { closeButton: true, progressBar: true, positionClass: '{{ app()->getLocale() === "ar" ? "toast-top-left" : "toast-top-right" }}', timeOut: 6000 };
        if (typeof window._toastrError !== 'undefined') toastr.error(window._toastrError);
        if (typeof window._toastrSuccess !== 'undefined') toastr.success(window._toastrSuccess);
    </script>
</body>
</html>
